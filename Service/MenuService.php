<?php

namespace Unlooped\MenuBundle\Service;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Unlooped\MenuBundle\Annotation\HideInMenu;
use Unlooped\MenuBundle\Annotation\ShowInMenu;
use Unlooped\MenuBundle\Exception\ShowAndHideAnnotationSetException;
use Unlooped\MenuBundle\Helper\MenuHelper;

class MenuService
{

    private $menuBuilderServices;
    private $router;
    /** @var UserInterface */
    private $user;
    private $routeCollection;

    /**
     * @param iterable|AbstractMenuBuilderService[] $handlers
     */
    public function __construct(
        iterable $handlers,
        RouterInterface $router = null,
        TokenStorageInterface $tokenStorage = null
    )
    {
        $this->menuBuilderServices = $handlers;
        $this->router = $router;
        $this->routeCollection = $this->router->getRouteCollection();

        if ($tokenStorage
            && $tokenStorage->getToken()
            && $tokenStorage->getToken()->getUser())
        {
            $this->user = $tokenStorage->getToken()->getUser();
        }

        foreach ($this->menuBuilderServices as $menuBuilderService) {
            $menuBuilderService->setMenuService($this);
        }
    }

    public function getMenuHelperForName(string $name): ?MenuHelper
    {
        $methodName = 'create' . ucfirst($name) . 'Menu';
        foreach ($this->menuBuilderServices as $menuBuilderService) {
            if (method_exists($menuBuilderService, $methodName)) {
                return $menuBuilderService->$methodName();
            }
        }

        return null;
    }

    /**
     * @param string $routeName
     *
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws RouteNotFoundException
     * @throws ShowAndHideAnnotationSetException
     */
    public function canCurrentUserAccessRoute(string $routeName): bool
    {
        if (!$this->router || !$this->user) {
            return false;
        }

        $route = $this->routeCollection->get($routeName);
        if (!$route) {
            throw new RouteNotFoundException($routeName . ' not Found');
        }

        $method = $route->getDefault('_controller');
        $rmethod = new ReflectionMethod($method);

        $reader = new AnnotationReader();
        /** @var ShowInMenu $showForRolws */
        $showInMenu = $reader->getMethodAnnotation($rmethod, ShowInMenu::class);
        /** @var HideInMenu $hideInMenu */
        $hideInMenu = $reader->getMethodAnnotation($rmethod, HideInMenu::class);

        if ($showInMenu && $hideInMenu) {
            throw new ShowAndHideAnnotationSetException($method);
        }

        if (!$showInMenu && !$hideInMenu) {
            return true;
        }

        if ($showInMenu) {
            $frc = count($showInMenu->forRoles);
            if ($frc === 0) {
                return true;
            }

            if (count(array_diff($this->user->getRoles(), $showInMenu->forRoles)) < $frc) {
                return true;
            }

            return false;
        }

        if ($hideInMenu) {
            $frc = count($hideInMenu->forRoles);
            if ($frc === 0) {
                return false;
            }

            if (count(array_diff($this->user->getRoles(), $hideInMenu->forRoles)) < $frc) {
                return false;
            }

            return true;
        }
    }



}
