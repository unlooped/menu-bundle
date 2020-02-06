<?php

namespace Unlooped\MenuBundle\Model;

use Unlooped\MenuBundle\Exception\NameForMenuAlreadyExistsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Menu
{

    private $name;
    private $isActive = false;
    private $isChildActive = false;
    private $activeChild;
    private $children = [];
    private $options;
    private $parent;

    public function __construct(string $name, Menu $parent = null, array $options = [])
    {
        $this->name = $name;
        $this->parent = $parent;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public static function create(
        string $name,
        Menu $parent = null,
        array $options = []
    ): Menu
    {
        return new self($name, $parent, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => null,
            'attr'            => [],
            'route'           => '',
            'routeOptions'    => [],
            'routeAsAbsolute' => true,
            'url'             => '',
            'other'           => [],
            'visible'         => true,
        ]);

        $resolver->setAllowedTypes('label', ['null', 'string']);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('route', 'string');
        $resolver->setAllowedTypes('routeOptions', 'array');
        $resolver->setAllowedTypes('routeAsAbsolute', 'bool');
        $resolver->setAllowedTypes('url', 'string');
        $resolver->setAllowedTypes('other', 'array');
        $resolver->setAllowedTypes('visible', 'bool');
    }
    /**
     * @throws NameForMenuAlreadyExistsException
     */
    public function addMenu(Menu $menu): void
    {
        if (array_key_exists($menu->getName(), $this->children)) {
            throw new NameForMenuAlreadyExistsException($menu->getName());
        }

        $this->children[$menu->getName()] = $menu;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getMenuByName(string $name): ?Menu
    {
        if (array_key_exists($name, $this->children)) {
            return $this->children[$name];
        }

        return null;
    }

    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    public function getRoute(): ?string
    {
        return $this->options['route'];
    }

    public function getRouteParameters(): array
    {
        return $this->options['routeOptions'];
    }

    public function getRouteAsAbsolute(): bool
    {
        return $this->options['routeAsAbsolute'];
    }

    public function getUrl(): ?string
    {
        return $this->options['url'];
    }

    public function getLabel(): string
    {
        return $this->options['label'] ?? $this->name;
    }

    public function getOtherOptions()
    {
        return $this->options['other'];
    }

    public function getAttr()
    {
        return $this->options['attr'];
    }

    public function markAsActive(): void
    {
        $this->isActive = true;
        if ($this->parent) {
            $this->parent->markAsChildIsActive($this);
        }
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function markAsChildIsActive(Menu $menu): void
    {
        $this->isChildActive = true;
        $this->activeChild = $menu;
        if ($this->parent) {
            $this->parent->markAsChildIsActive($this);
        }
    }

    public function isChildActive(): bool
    {
        return $this->isChildActive;
    }

    /**
     * @return array|Menu[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array|Menu[]
     */
    public function visibleChildren(): array
    {
        return array_filter($this->children, function(Menu $menu) {
            return $menu->isVisible();
        });
    }

    public function getActiveChild(): ?Menu
    {
        return $this->activeChild;
    }

    public function isVisible(): bool
    {
        return $this->options['visible'];
    }

}
