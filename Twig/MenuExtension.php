<?php

namespace Unlooped\MenuBundle\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Unlooped\MenuBundle\Model\Menu;
use Unlooped\MenuBundle\Service\MenuService;

class MenuExtension extends AbstractExtension
{

    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('umb_render_menu', [$this, 'getRenderedMenu'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('umb_render_sub_menu', [$this, 'getRenderedSubMenu'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('umb_menu_href', [$this, 'getHrefForMenu'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('umb_menu_link', [$this, 'getLinkForMenu'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('umb_render_breadcrumbs', [$this, 'getBreadcrumbsForMenu'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param Environment $environment
     * @param string $name
     * @param string|null $template #template
     * @param array $options
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getRenderedMenu(
        Environment $environment,
        string $name,
        string $template = null,
        array $options = []
    ): ?string
    {
        $menuHelper = $this->menuService->getMenuHelperForName($name);
        if (!$menuHelper) {
            return null;
        }

        return $environment->render($template ?? '@UnloopedMenu/bootstrap_4_topnav_menu.html.twig', [
            'options'     => $options,
            'menu'        => $menuHelper->getMenu(),
        ]);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getRenderedSubMenu(
        Environment $environment,
        string $name,
        string $path,
        string $template = null,
        array $options = []
    ): ?string
    {
        $menuHelper = $this->menuService->getMenuHelperForName($name);

        if (!$menuHelper) {
            return null;
        }

        $pathChunks = explode('.', $path);
        $lastMenu = $menuHelper->getMenuByName($pathChunks[0]);
        $length = count($pathChunks);
        for ($i = 1; $i < $length; $i++) {
            if (!$lastMenu) {
                break;
            }

            $lastMenu = $lastMenu->getMenuByName($pathChunks[$i]);
        }

        return $environment->render($template ?? '@UnloopedMenu/bootstrap_4_nav_menu.html.twig', [
            'options'     => $options,
            'menu'        => $lastMenu,
        ]);
    }

    public function getLinkForMenu(
        Environment $environment,
        Menu $menu
    ): string
    {
        if ($menu->getRoute()) {
            if ($menu->getRouteAsAbsolute()) {
                $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
            } else {
                $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
            }

            /** @var RoutingExtension $router */
            $router = $environment->getExtension(RoutingExtension::class);

            return $router->getPath($menu->getRoute(), $menu->getRouteParameters(), $referenceType);
        }

        if ($menu->getUrl()) {
            return $menu->getUrl();
        }

        return '';
    }

    public function getHrefForMenu(
        Environment $environment,
        Menu $menu
    ): string
    {
        $link = $this->getLinkForMenu($environment, $menu);
        if ($link) {
            return 'href="' . $link . '"';
        }

        return '';
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getBreadcrumbsForMenu(
        Environment $environment,
        string $name
    ): ?string
    {
        $menuHelper = $this->menuService->getMenuHelperForName($name);
        if (!$menuHelper) {
            return null;
        }

        return $environment->render('@UnloopedMenu/bootstrap_4_breadcrumbs.html.twig', [
            'breadcrumbs' => $menuHelper->getBreadcrumbs(),
        ]);
    }
}
