<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Exception;

/**
 * Class InvalidParseObjectException
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Exception
 */
class InvalidParseObjectException extends \InvalidArgumentException
{
    public function __construct($invalidObject)
    {
        $invalidClass = (is_string($invalidObject)) ? $invalidObject : get_class($invalidObject);
        $msg          = sprintf(
            '%s must implement the Devtrw\\ParseBundle\\Object\\ParseObjectInterface interface to use it as a Parse object.',
            $invalidClass
        );
        parent::__construct($msg);
    }
}
