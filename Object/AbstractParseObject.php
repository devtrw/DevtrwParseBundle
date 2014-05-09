<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Object;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class AbstractParseObject
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Object
 */
abstract class AbstractParseObject implements ParseObjectInterface
{
    /**
     * This is used by the helper methods for storing the underlying data
     * returned and sent to the Parse API
     *
     * @var array
     */
    protected $objectData = [];

    /**
     * As a fallback for undefined getters/setters provide get<$property>()
     * and set<$property>($value) methods that are aliases for
     * $this->get($property) and $this->set($property, $value).
     * You should try to have getters and setters defined for
     * property access but the fallback is here for when you dont.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    public function __call($name, $arguments)
    {
        $nameParts = explode('_', Inflector::tableize($name));
        $method    = array_shift($nameParts);
        $property  = Inflector::camelize(implode('_', $nameParts));
        array_unshift($arguments, $property);

        return call_user_func_array([$this, $method], $arguments);
    }

    public function addResponseData(array $data = [])
    {
        foreach ($data as $key => $value) {
            // Call setters for each returned value
            $this->accessData($this, $key, $value, true);
        }
    }

    /**
     * {@inheritdoc}
     * @author Steven Nance <steven@devtrw.com>
     */
    public static function getApiVersion()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     * @author Steven Nance <steven@devtrw.com>
     */
    public static function getClassPath()
    {
        $classParts = explode('\\', get_called_class());

        return lcfirst(end($classParts));
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation($objectId = null)
    {
        return $this->get('location');
    }

    /**
     * This should return an array containing the data used in the body of requests
     *
     * @return array
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getRequestData()
    {
        $normalizedData = [];
        foreach ($this->getDataFields() as $key) {
            $normalizedData[Inflector::camelize($key)] = $this->get($key);
        }

        return $this->stripNullValues($normalizedData);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocation($location = null)
    {
        $this->set('location', $location);

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->get('createdAt');
    }

    /**
     * This should return an array of fields that contain data to be sent and retrieved
     * from the Parse server.
     * The property names should be written using lowercase_underscored_syntax which should correspond to
     * the getter and setter for said property.
     * For example some_property_name would correspond with getSomePropertyName and setSomePropertyName($newName).
     * The the Symfony PropertyAccess component is used for getting and setting the values. Null values will
     * be removed from the request object which allows for partial updates.
     *
     * @TODO   Allow unsetting/nulling a value
     * @return Array
     * @author Steven Nance <steven@devtrw.com>
     */
    abstract function getDataFields();

    public function getImmutableFields()
    {
        return ['objectId', 'createdAt', 'updatedAt', 'location'];
    }

    /**
     * @return string|null
     */
    public function getObjectId()
    {
        return $this->get('objectId');
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->get('updatedAt');
    }

    /**
     * @param \DateTime|string $time
     *
     * @return $this
     * @author Steven Nance <steven@devtrw.com>
     */
    public function setCreatedAt($time)
    {
        if (! $time instanceof \DateTime) {
            $time = new \DateTime($time);
        }
        $this->set('createdAt', $time);

        return $this;
    }

    /**
     * The only time you would manually set this is if you already have the ID
     * of an object that you want to retrieve
     *
     * @param string $objectId
     *
     * @return $this
     * @author Steven Nance <steven@devtrw.com>
     */
    public function setObjectId($objectId)
    {
        if (null !== $this->getObjectId()) {
            throw new \LogicException('You cannot change the ID of an object once it has been set');
        }
        $this->set('objectId', $objectId);

        return $this;
    }

    /**
     * @param \DateTime|string $time
     *
     * @return $this
     * @author Steven Nance <steven@devtrw.com>
     */
    public function setUpdatedAt($time)
    {
        if (! $time instanceof \DateTime) {
            $time = new \DateTime($time);
        }
        $this->set('updatedAt', $time);

        return $this;
    }

    protected function accessData($from, $key, $value = null, $set = false)
    {
        $accessor  = PropertyAccess::createPropertyAccessor();
        $accessKey = $this->formatAccessKey($key, $from);

        if (false !== $set) {
            $accessor->setValue($from, $accessKey, $value);
        }

        return $accessor->getValue($from, $accessKey);
    }

    protected function get($key)
    {
        return $this->accessData($this->objectData, $key);
    }

    protected function set($key, $newValue)
    {
        $this->objectData[$key] = $newValue;

        return $this;
    }

    /**
     * Get a property access key formatted for the provided type
     *
     * @param string       $key
     * @param array|object $from
     *
     * @return string
     * @author Steven Nance <steven@devtrw.com>
     */
    private function formatAccessKey($key, $from)
    {
        // Accessing getters requires properties_be_separated_by_underscores
        if (is_object($from)) {
            return Inflector::tableize($key);
        }

        // Array indices are all stored using camelCaseCaps mirroring the format of the API
        if (is_array($from)) {
            return sprintf('[%s]', Inflector::camelize($key));
        }

        throw new \InvalidArgumentException(sprintf('Unexpected access target of type "%s"', gettype($from)));
    }

    private function stripNullValues($normalizedData)
    {
        return array_filter(
            $normalizedData,
            function ($val) {
                return null !== $val;
            }
        );
    }
}
