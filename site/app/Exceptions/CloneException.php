<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when a entity can't be cloned.
 */
class CloneException extends Exception
{
    public function __construct(string $message = null)
    {
        if (empty($message)) {
            $message = trans('errors.clone_in');
        }
        parent::__construct($message);
    }
}
