<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Tests\Http;

use Devtrw\ParseBundle\Http\Client;
use Devtrw\ParseBundle\Object\User;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;

/**
 * Class ClientTest
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Tests\Http
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $defaultConfig;

    public function setUp()
    {
        $this->defaultConfig = [
            'app_id'   => 'expected_app_id',
            'rest_key' => 'expected_rest_key',
            'base_url' => 'https://mock.parse.com'
        ];
    }

    public function testCreateClientObject()
    {
        $client           = $this->createClient();
        $now              = new \DateTime();
        $objectId         = 'Ed1nuqPvcm';
        $expectedLocation = 'https://mock.parse.com/1/classes/users/' . $objectId;

        $response = new Response(
            201,
            ['Location' => $expectedLocation, 'Content-Type' => 'application/json'],
            Stream::factory(json_encode(['createdAt' => $now->format(DATE_ISO8601), 'objectId' => $objectId]))
        );

        $user = new User();
        $user->setUsername('some-username')
             ->setPassword('some-new-password-hash');
        $expectedRequest = json_encode(['username' => 'some-username', 'password' => 'some-new-password-hash']);

        $history = $this->mockClientRequest($client, $response);

        // make request
        $client->save($user);

        $this->assertCount(1, $history);
        $lastRequest = $history->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals('/1/classes/users', $lastRequest->getPath());

        $this->assertJsonStringEqualsJsonString($expectedRequest, (string) $lastRequest->getBody());

        $this->assertEquals($objectId, $user->getObjectId(), 'The object ID returned in the request is set');
        $this->assertEquals($expectedLocation, $user->getLocation(), 'The location reference is stored when returned');
        $this->assertEquals($now, $user->getCreatedAt(), 'The createdAt property is set and is a \DateTime instance');
    }

    public function testExceptionOnInvalidObjectType()
    {
        $client       = $this->createClient();
        $invalidClass = get_class($this);

        $this->setExpectedException('Devtrw\\ParseBundle\\Exception\\InvalidParseObjectException');
        $client->createRequest('GET', $invalidClass);
    }

    public function testExceptionOnMissingAppId()
    {
        $this->setExpectedException(
            'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
        );
        $this->createClient(['app_id' => null]);
    }

    public function testExceptionOnMissingBaseUrl()
    {
        $this->setExpectedException(
            'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
        );
        $this->createClient(['base_url' => null], false);
    }

    public function testExceptionOnMissingRestKey()
    {
        $this->setExpectedException(
            'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
        );
        $this->createClient(['rest_key' => null], false);
    }

    public function testGetClient()
    {
        $config = ['base_url' => 'http://expected.base.url'];
        $client =
            $this->createClient($config)
                 ->getClient();
        $this->assertInstanceOf('GuzzleHttp\\ClientInterface', $client);
        $this->assertEquals($config['base_url'], $client->getBaseUrl());
    }

    public function testGetClientObject()
    {
        $objectId         = 'sdflkjD';
        $client           = $this->createClient();
        $oneWeekAgo       = new \DateTime('-1 week');
        $oneDayAgo        = new \DateTime('-1 day');
        $expectedLocation = 'https://mock.parse.com/1/classes/users/' . $objectId;
        $userData         =
            [
                'objectId'      => $objectId,
                'username'      => 'someuser',
                'emailVerified' => true,
                'email'         => 'someuser@example.com',
                'createdAt'     => $oneWeekAgo->format(DATE_ISO8601),
                'updatedAt'     => $oneDayAgo->format(DATE_ISO8601)
            ];
        $response         = new Response(
            200,
            ['Content-Type' => 'application/json'],
            Stream::factory(json_encode($userData))
        );
        $history          = $this->mockClientRequest($client, $response);

        /** @var User $user */
        $user = $client->get(User::class, $userData['objectId']);

        $request = $history->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());

        $requestLocation = $request->getUrl();
        $this->assertEquals(
            $expectedLocation,
            $requestLocation,
            sprintf('Request location, "%s", is equal to expectedLocation, "%s".', $requestLocation, $expectedLocation)
        );

        $this->assertEquals(
            $userData['objectId'],
            $user->getObjectId(),
            'The object ID returned in the request is set'
        );
        $this->assertEquals(
            $expectedLocation,
            $user->getLocation(),
            'The location reference is stored when returned'
        );
        $this->assertEquals(
            $oneWeekAgo,
            $user->getCreatedAt(),
            'The createdAt property is set and is a \DateTime instance'
        );
        $this->assertEquals(
            $oneDayAgo,
            $user->getUpdatedAt(),
            'The updatedAt property is set and is a \DateTime instance'
        );
        $this->assertEquals($expectedLocation, $user->getLocation(), 'The location property is correctly set');
    }

    public function testRequestClientHeaders()
    {
        $client  = $this->createClient();
        $request = $client->createRequest('GET', '\Devtrw\ParseBundle\Object\User');

        $this->assertInstanceOf('GuzzleHttp\\Message\\Request', $request);
        $this->assertEquals($this->defaultConfig['app_id'], $request->getHeader('X-Parse-Application-Id'));
        $this->assertEquals($this->defaultConfig['rest_key'], $request->getHeader('X-Parse-REST-API-Key'));
    }

    public function testRequestClientObjectEndpoint()
    {
        $config  = ['base_url' => 'https://expected.com'];
        $client  = $this->createClient($config);
        $request = $client->createRequest('GET', '\Devtrw\ParseBundle\Object\User');

        $this->assertEquals('https://expected.com/1/classes/users', $request->getUrl());
    }

    private function createClient(array $config = [])
    {
        return new Client(array_merge($this->defaultConfig, $config));
    }

    private function mockClientRequest(Client $client, ResponseInterface $response)
    {
        $guzzleClient = $client->getClient();
        $emitter      = $guzzleClient->getEmitter();
        $history      = new History();
        $emitter->attach($history);
        $emitter->attach(new Mock([$response], false));

        return $history;
    }
}
