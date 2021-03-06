<?php

namespace Unlooped\MenuBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Unlooped\MenuBundle\Exception\NameForMenuAlreadyExistsException;
use Unlooped\MenuBundle\Model\Menu;
use Unlooped\MenuBundle\Service\MenuService;

class MenuHelper
{

    /** @var Menu */
    private $rootMenu;
    private $currentMenu;
    private $request;
    private $menuService;

    public static function create(
        Request $request = null,
        MenuService $menuService = null
    ): self
    {
        return new self($request, $menuService);
    }

    public function __construct(
        Request $request = null,
        MenuService $menuService = null
    )
    {
        $this->rootMenu = new Menu('__root');
        $this->currentMenu = $this->rootMenu;
        $this->request = $request;
        $this->menuService = $menuService;
    }

    /**
     * @param string $name
     * @param array $options
     *
     * @throws NameForMenuAlreadyExistsException
     */
    public function addMenu(string $name, array $options = []): self
    {
        $options = $this->updateVisible($options);

        $menu = Menu::create($name, $this->currentMenu, $options);
        $this->updateActive($menu);
        $this->currentMenu->addMenu($menu);

        return $this;
    }

    /**
     * @param string $name
     * @param array $options
     *
     * @throws NameForMenuAlreadyExistsException
     */
    public function addSubMenu(string $name, array $options = []): self
    {
        $options = $this->updateVisible($options);

        $subMenu = Menu::create($name, $this->currentMenu, $options);
        $this->updateActive($subMenu);

        $this->currentMenu->addMenu($subMenu);
        $this->currentMenu = $subMenu;

        return $this;
    }

    private function updateActive(Menu $menu): void
    {
        if (!$this->request) {
            return;
        }

        if ($menu->getRoute() && $this->request->attributes->get('_route') === $menu->getRoute()) {
            $menu->markAsActive();
        } elseif ($menu->getUrl() && $this->request->getUri() === $menu->getUrl()) {
            $menu->markAsActive();
        }
    }

    public function end(): self
    {
        $this->currentMenu = $this->currentMenu->getParent();

        return $this;
    }

    public function getMenuByName(string $name): ?Menu
    {
        return $this->rootMenu->getMenuByName($name);
    }

    public function getMenu(): Menu
    {
        return $this->rootMenu;
    }

    /**
     * @return array|Menu[]
     */
    public function getBreadcrumbs(): array
    {
        $res = [];
        $menu = $this->rootMenu;
        while ($nac = $menu->getActiveChild()) {
            $menu = $nac;
            $res[] = $nac;
        }

        return $res;
    }

    protected function updateVisible(array $options): array
    {
        if ($this->menuService && isset($options['visible_for_roles']) && !isset($options['visible'])) {
            $options['visible'] = $this->menuService->isAccessible($options['visible_for_roles']);
        }

        return $options;
    }

}
