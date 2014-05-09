<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Tests\Object;

/**
 * Class AbstractParseObjectTest
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\ParseBundle\Tests\Object
 */
class AbstractParseObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockParseObject
     */
    private $parseObject;

    public function setUp()
    {
        $this->parseObject = new MockParseObject();
    }

    public function testAddResponseData()
    {
        $lastWeek = new \DateTime('-1 week');
        $now      = new \DateTime();
        $objectId = 'slkjf9Kh';
        $location = 'https://some-endpoint.com/1/classes/MockParseObject/' . $objectId;

        $responseData = [
            'createdAt' => $lastWeek,
            'objectId'  => $objectId,
            'updatedAt' => $now,
            'location'  => $location
        ];
        $this->parseObject->addResponseData($responseData);
        $this->assertEquals($responseData['objectId'], $this->parseObject->getObjectId());
        $this->assertEquals($responseData['location'], $this->parseObject->getLocation());
        $this->assertInstanceOf('\DateTime', $this->parseObject->getCreatedAt());
        $this->assertEquals($responseData['createdAt'], $this->parseObject->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->parseObject->getUpdatedAt());
        $this->assertEquals($responseData['updatedAt'], $this->parseObject->getUpdatedAt());
    }

    public function testGetRequestData()
    {
        $expected = [
            'one'         => 'ones-data',
            'oneTwo'      => 'one-two\'s-data',
            'oneTwoThree' => 'One Two Three Data',
            'fourFiveSix' => 'fOurFive    six',
            'intVal'      => 789,
            'falseVal'    => false,
            'trueVal'     => true,
            'arrayVal'    => ['i' => 'contain', 'array' => 'values'],
            'objVal'      => (object) ['lazy' => 'object', 'creation' => 'here']
        ];
        $this->parseObject
            ->setOne($expected['one'])
            ->setOneTwo($expected['oneTwo'])
            ->setOneTwoThree($expected['oneTwoThree'])
            ->setFourFiveSix($expected['fourFiveSix'])
            ->setIntVal($expected['intVal'])
            ->setFalseVal($expected['falseVal'])
            ->setTrueVal($expected['trueVal'])
            ->setArrayVal($expected['arrayVal'])
            ->setObjVal($expected['objVal'])
            ->setNullVal(null) // We are expecting this to not be included in the request
        ;
        // NOTE: These fields are defined in the getDataFields response of the MockParseObject
        $actual = $this->parseObject->getRequestData();

        $this->assertEquals(
            $expected,
            $actual,
            sprintf('Expected: %s \\n Actual: %s', print_r($expected, true), print_r($actual, true))
        );
    }

    public function testGetRequestDataExcludesNullKeys()
    {
        $expected = ['one' => 'data-in-one'];
        $this->parseObject->setOne($expected['one'])
                          ->setOneTwo(null);

        $this->assertEquals($expected, $this->parseObject->getRequestData());
    }

    public function testSetObjectId()
    {
        $this->parseObject->setObjectId('expected-id');
        $this->assertEquals('expected-id', $this->parseObject->getObjectId());
    }
}
