# BETA, DON'T USE IN PRODUCTION


Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require unlooped/menu-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require unlooped/menu-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Unlooped\MenuBundle\UnloopedMenuBundle(),
        ];

        // ...
    }

    // ...
}
```

Usage
============

## Create a Service which extends `AbstractMenuBuilderService`

Currently only works with `create#NAME#Menu()`.

```php
<?php

namespace App\Service;

use Doctrine\Common\Annotations\AnnotationException;
use ReflectionException;
use Unlooped\MenuBundle\Exception\NameForMenuAlreadyExistsException;
use Unlooped\MenuBundle\Exception\ShowAndHideAnnotationSetException;
use Unlooped\MenuBundle\Helper\MenuHelper;
use Unlooped\MenuBundle\Service\AbstractMenuBuilderService;

class MenuBuilderService extends AbstractMenuBuilderService 
{

    /**
     * @throws NameForMenuAlreadyExistsException
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws ShowAndHideAnnotationSetException
     */
    public function createMainMenu(): MenuHelper
    {
        return $this->createMenuHelper()
            ->addMenu('Link A', ['route' => 'route_name_a'])
            ->addMenu('Link B', ['route' => 'route_name_b'])
            ->addMenu('Link C', ['route' => 'route_name_c'])
            ;
    }

}
```

### Available Options and Defaults (all optional)

```php
[
    'label'           => null, // null or string
    'attr'            => [], // array
    'route'           => '', // string
    'routeOptions'    => [], // see route options
    'routeAsAbsolute' => true, // bool
    'url'             => '', // string
    'other'           => [], // array
    'visible'         => true, // bool
]
```

### Menus with Submenus:

```php
public function createMainMenu(): MenuHelper
{
    return $this->createMenuHelper()
        ->addSubMenu('Category 1', [options])
            ->addMenu('Link 1 A', ['route' => 'route_name_c1_a'])
            ->addMenu('Link 1 B', ['route' => 'route_name_c1_b'])
            ->addMenu('Link 1 C', ['route' => 'route_name_c1_c'])
        ->end()
        ->addSubMenu('Category 2', [options])
            ->addMenu('Link 2 A', ['route' => 'route_name_c2_a'])
            ->addMenu('Link 2 B', ['route' => 'route_name_c2_b'])
            ->addMenu('Link 2 C', ['route' => 'route_name_c2_c'])
            ->addSubMenu('Category 2 A', ['route' => 'route_name_cat2a'])
                ->addMenu('Link 2A A', ['route' => 'route_name_c2_a'])
                ->addMenu('Link 2A B', ['route' => 'route_name_c2_b'])
                ->addMenu('Link 2A C', ['route' => 'route_name_c2_c'])
            ->end()
        ->end()
        ;
}
```

## Render Menu:

```twig
{{ render_menu('main') }}
```

### with options:

```twig
{{ render_menu('main', '@UnloopedMenu/bootstrap_4_sidebar_menu.html.twig', {'attr': {'class': 'collapse', 'id': 'docs'}}) }}
```

## Render Breadcrumbs

```twig
{{ render_breadcrumbs('main') }}
```

## Hide or Show

Use either the `HideInMenu` or `ShowInMenu` annotation on the route method in your controller:

Hides the link for everybody:
```php
/**
 * @Route("/home", name="home")
 *
 * @HideInMenu()
 */
public function dashboard(): Response
{
    return $this->render('dashboard/index.html.twig', [
        'controller_name' => 'DashboardController',
    ]);
}
```

Hides Menu only for users with role `ROLE_GUEST` OR `ROLE_USER` 
```php
/**
 * @Route("/home", name="home")
 *
 * @HideInMenu(forRoles={"ROLE_GUEST", "ROLE_USER"})
 */
public function dashboard(): Response
{
    return $this->render('dashboard/index.html.twig', [
        'controller_name' => 'DashboardController',
    ]);
}
```

Show Menu only for users with role `ROLE_ADMIN` OR `ROLE_USER` 
```php
/**
 * @Route("/home", name="home")
 *
 * @ShowInMenu(forRoles={"ROLE_ADMIN", "ROLE_USER"})
 */
public function dashboard(): Response
{
    return $this->render('dashboard/index.html.twig', [
        'controller_name' => 'DashboardController',
    ]);
}
```
