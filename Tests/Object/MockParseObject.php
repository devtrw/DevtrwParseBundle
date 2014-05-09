<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Tests\Object;

use Devtrw\ParseBundle\Object\AbstractParseObject;

/**
 * Class MockParseObject
 * @method $this setFourFiveSix(string $propertyValue)
 * @method $this setIntVal(int $propertyValue)
 * @method $this setFalseVal(boolean $propertyValue)
 * @method $this setTrueVal(boolean $propertyValue)
 * @method $this setArrayVal(array $propertyValue)
 * @method $this setObjVal(object $propertyValue)
 * @method $this setNUllVal(null $propertyValue)
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\ParseBundle\Tests\Object
 */
class MockParseObject extends AbstractParseObject
{
    function getDataFields()
    {
        return [
            'one',
            'oneTwo',
            'oneTwoThree',
            'fourFiveSix',
            'intVal',
            'falseVal',
            'trueVal',
            'arrayVal',
            'objVal',
            'nullVal'
        ];
    }

    public function getOne()
    {
    }

    public function getOneTwo()
    {
    }

    public function getOneTwoThree()
    {
    }

    public function setOne($property)
    {
        $this->set('one', $property);

        return $this;
    }

    public function setOneTwo($property)
    {
        $this->set('oneTwo', $property);

        return $this;
    }

    public function setOneTwoThree($property)
    {
        $this->set('oneTwoThree', $property);

        return $this;
    }
}
