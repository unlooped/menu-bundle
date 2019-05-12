<?php

namespace Unlooped\MenuBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class HideInMenu
{

    /**
     * @var array
     */
    public $forRoles = [];

}
