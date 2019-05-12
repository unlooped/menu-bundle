<?php

namespace Unlooped\MenuBundle\Exception;

use Exception;
use Throwable;

class ShowAndHideAnnotationSetException extends Exception
{
    public function __construct($method, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Show and Hide Annotation can\'t be set at the same time at: ' . $method, $code, $previous);
    }

}
