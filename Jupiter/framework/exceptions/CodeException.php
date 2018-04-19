<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/5/18
 * Time: 5:04 PM
 */

namespace framework\exceptions;

use Throwable;

class CodeException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}