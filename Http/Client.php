<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Http;

use Devtrw\ParseBundle\Exception\InvalidParseObjectException;
use Devtrw\ParseBundle\Object\ParseObjectInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class Client
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Http
 */
class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * @param string                      $method
     * @param string|ParseObjectInterface $objectClass
     * @param array                       $options
     *
     * @return RequestInterface
     * @author Steven Nance <steven@devtrw.com>
     */
    public function createRequest($method, $objectClass, array $body = [])
    {
        $endpoint = $this->getObjectEndpoint($objectClass);
        $options  = [];
        if (! empty($body)) {
            $options['body'] = json_encode($body);
        }
        $request =
            $this->getClient()
                 ->createRequest($method, $endpoint, $options);
        $this->setRequestHeaders($request);

        return $request;
    }

    /**
     * @param string $objectClass A class implementing ParseObjectInterface to be fetched from the API
     * @param string $objectId
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    public function get($objectClass, $objectId)
    {
        // make request
        $request = $this->createRequest('GET', $objectClass);
        $request->setUrl($request->getUrl() . '/' . $objectId);
        $response = $this->sendRequest($request);

        // create object with response data
        $parseObject = new $objectClass();
        $this->updateParseObject($parseObject, $response);

        return $parseObject;
    }

    /**
     * Returns the underlying GuzzleClient.
     * You should not need to access this in most cases.
     *
     * @return GuzzleClient
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new GuzzleClient(
                [
                    'base_url' => $this->options['base_url']
                ]
            );
        }

        return $this->client;
    }

    public function logIn($username, $password)
    {
    }

    /**
     * Sends a (POST) or update (PUT) request with the passed in object based
     * on whether or not an ID is present.
     *
     * @param ParseObjectInterface $parseObject
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    public function save(ParseObjectInterface $parseObject)
    {
        $request  = $this->createRequest('POST', $parseObject, $parseObject->getRequestData());
        $response = $this->sendRequest($request);
        $this->updateParseObject($parseObject, $response);
    }

    /**
     * Build an endpoint for the passed in object class or instance
     *
     * @FIXME  This is really ugly...
     *
     * @param $object
     *
     * @return string
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function getObjectEndpoint($object)
    {
        if (false === in_array('Devtrw\\ParseBundle\\Object\\ParseObjectInterface', class_implements($object))) {
            throw new InvalidParseObjectException($object);
        }

        return '/' . implode('/', [$object::getApiVersion(), 'classes', $object::getClassPath()]);
    }

    /**
     * @param ParseObjectInterface $parseObject
     * @param array                $getHeaders
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function processResponseHeaders(ParseObjectInterface $parseObject, array $getHeaders = null)
    {
        if (empty($getHeaders)) {
            return;
        }
        $dataHeaders = ['Location'];

        // filter the headers to the allowed values before passing to the parseObject
        $headerData = array_intersect_key($getHeaders, array_flip($dataHeaders));

        // Convert arrays of headers to key => value[0]. For our current use case there should
        // not be multiples of the same header and if they are for some reason the last value
        // will be used.
        $normalized = array_map(
            function ($value) {
                return (is_array($value)) ? end($value) : $value;
            },
            $headerData
        );

        $parseObject->addResponseData($normalized);
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function sendRequest(RequestInterface $request)
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->getClient()
                    ->send($request);
    }

    /**
     * @param RequestInterface $request
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function setRequestHeaders(RequestInterface $request)
    {
        $request->setHeader('X-Parse-Application-Id', $this->options['app_id']);
        $request->setHeader('X-Parse-REST-API-Key', $this->options['rest_key']);
        $request->setHeader('Content-Type', 'application/json');
    }

    /**
     * Handles updating the stored parse object with the data in an API response
     *
     * @param ParseObjectInterface $parseObject
     * @param ResponseInterface    $response
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function updateParseObject(ParseObjectInterface $parseObject, ResponseInterface $response)
    {
        $this->processResponseHeaders($parseObject, $response->getHeaders());
        $this->processResponseBody($parseObject, $response->json());
        if (null === $parseObject->getLocation()) {
            $parseObject->setLocation($response->getEffectiveUrl());
        }
    }

    /**
     * Sets the required/default values loaded from the bundle configuration
     *
     * @param OptionsResolverInterface $resolver
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    private function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['master_key' => false]);
        $resolver->setRequired(['app_id', 'rest_key', 'base_url']);
        $resolver->setAllowedTypes(
            [
                'app_id'   => 'string',
                'rest_key' => 'string',
                'base_url' => 'string'
            ]
        );
    }

    /**
     * @param ParseObjectInterface $parseObject
     * @param array                $responseBody
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    private function processResponseBody(ParseObjectInterface $parseObject, array $responseBody = null)
    {
        if (empty($responseBody)) {
            return;
        }
        $parseObject->addResponseData($responseBody);
    }
}
