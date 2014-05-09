<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Object;

/**
 * Class ParseObjectInterface
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Object
 */
interface ParseObjectInterface
{
    /**
     * This should return the api version of the object url endpoint.
     * For example a User class object that returns "1" will end with the endpoint:
     *     <base_url>/1/classes/<class_path>
     *
     * @return string
     * @author Steven Nance <steven@devtrw.com>
     */
    public static function getApiVersion();

    /**
     * This should return the part of the api endpoint after the base url.
     * For example a User class object that returns "/user" will with the endpoint:
     *     <base_url>/<api_version>/classes/user
     *
     * @return string
     * @author Steven Nance <steven@devtrw.com>
     */
    public static function getClassPath();

    /**
     * This method should update the object with the values contained in the passed in array.
     * The content of the $responseData array will be the json_decoded body and white-listed
     * headers returned as a result of a POST, PUT, or GET request to the API.
     *
     * @param array $responseData
     *
     * @return mixed
     * @author Steven Nance <steven@devtrw.com>
     */
    public function addResponseData(array $responseData = []);

    /**
     * This should return the url that corresponds with the object
     *
     * @return string
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getLocation();

    /**
     * This should return an array containing the data used in the body of requests
     *
     * @return array
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getRequestData();

    /**
     * This should set the url that corresponds with the object
     *
     * @param string|null $url
     *
     * @return mixed
     * @author Steven Nance <steven@devtrw.com>
     */
    public function setLocation($url = null);
}
