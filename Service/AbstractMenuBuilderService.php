<?php

namespace Unlooped\MenuBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Unlooped\MenuBundle\Helper\MenuHelper;

abstract class AbstractMenuBuilderService
{

    protected $requestStack;
    private $menuService;

    public function __construct(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }

    public function createMenuHelper(): MenuHelper
    {
        $request = $this->requestStack->getCurrentRequest();

        return MenuHelper::create($request, $this->menuService);
    }

    public function setMenuService(MenuService $menuService): void
    {
        $this->menuService = $menuService;
    }
}
