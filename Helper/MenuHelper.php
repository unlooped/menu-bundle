<?php

namespace Unlooped\MenuBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Unlooped\MenuBundle\Exception\MenuNotManagedByHelperException;
use Unlooped\MenuBundle\Exception\NameForMenuAlreadyExistsException;
use Unlooped\MenuBundle\Model\Menu;
use Unlooped\MenuBundle\Service\MenuService;

class MenuHelper
{

    protected Menu $rootMenu;
    protected Menu $currentMenu;
    protected array $allMenus = [];

    protected ?Request $request;
    protected ?MenuService $menuService;

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
     * @throws NameForMenuAlreadyExistsException
     */
    public function buildMenu(string $name, Menu $parentMenu, array $options = []): Menu
    {
        $options = $this->updateVisible($options);

        $menu = Menu::create($name, $parentMenu, $options);
        $this->updateActive($menu);
        $parentMenu->addMenu($menu);

        $this->allMenus[] = $menu;

        return $menu;
    }

    /**
     * @throws NameForMenuAlreadyExistsException
     */
    public function addMenu(string $name, array $options = []): self
    {
        $this->buildMenu($name, $this->currentMenu, $options);

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
        $subMenu = $this->buildMenu($name, $this->currentMenu, $options);

        $this->currentMenu = $subMenu;

        return $this;
    }

    /**
     * @throws NameForMenuAlreadyExistsException
     */
    public function nestMenu(Menu $menu, Menu $into): void
    {
        $into->addMenu($menu);
    }

    protected function updateActive(Menu $menu): void
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

    public function getRootMenu(): Menu
    {
        return $this->rootMenu;
    }

    /**
     * @throws MenuNotManagedByHelperException
     */
    public function loadMenu(Menu $menu): self
    {
        if (!in_array($menu, $this->allMenus, true)) {
            throw new MenuNotManagedByHelperException();
        }

        $this->currentMenu = $menu;

        return $this;
    }

    public function loadParentMenu(): self
    {
        $this->currentMenu = $this->currentMenu->getParent();

        return $this;
    }

    public function loadMenuByName(string $name): self
    {
        $this->currentMenu = $this->currentMenu->getMenuByName($name);

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

    public function getCurrentMenu(): Menu
    {
        return $this->currentMenu;
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
