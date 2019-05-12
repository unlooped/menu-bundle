<?php

namespace Unlooped\MenuBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ShowInMenu
{

    /**
     * @var array
     */
    public $forRoles = [];

}
