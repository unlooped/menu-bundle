<?php

namespace Unlooped\MenuBundle\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Unlooped\MenuBundle\Helper\MenuHelper;

class MenuService
{

    private $menuBuilderServices;
    private $authorizationChecker;

    /**
     * @param iterable|AbstractMenuBuilderService[] $handlers
     */
    public function __construct(
        iterable $handlers,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->menuBuilderServices = $handlers;
        $this->authorizationChecker = $authorizationChecker;

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

    public function isAccessible($attributes): bool
    {
        if (is_iterable($attributes)) {
            foreach ($attributes as $attribute) {
                if ($this->authorizationChecker->isGranted($attribute)) {
                    return true;
                }
            }

            return false;
        }

        return $this->authorizationChecker->isGranted($attributes);
    }



}
