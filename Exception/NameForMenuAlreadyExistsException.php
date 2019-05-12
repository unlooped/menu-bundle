<?php

namespace Unlooped\MenuBundle\Exception;

use Exception;
use Throwable;

class NameForMenuAlreadyExistsException extends Exception
{
    public function __construct($name, $code = 0, Throwable $previous = null)
    {
        parent::__construct($name . ' already Exists in Menu', $code, $previous);
    }

}
