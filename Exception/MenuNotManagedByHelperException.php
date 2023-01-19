<?php

namespace Unlooped\MenuBundle\Exception;

use Exception;
use Throwable;

class MenuNotManagedByHelperException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Menu not Managed by this Helper', $code, $previous);
    }

}
