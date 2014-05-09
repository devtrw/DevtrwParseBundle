<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Tests\DependencyInjection;

use Devtrw\ParseBundle\DependencyInjection\DevtrwParseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DevtrwParseExtensionTest
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Tests\DependencyInjection
 */
class DevtrwParseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;
    /**
     * @var array
     */
    private $defaultConfig;
    /**
     * @var DevtrwParseExtension
     */
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', array('DevtrwParseBundle' => true));
        $this->extension     = new DevtrwParseExtension();
        $this->defaultConfig = [
            'app_id'   => '',
            'rest_key' => '',
            'base_url' => ''
        ];
    }

    public function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testClientClassDefinitionDefined()
    {
        $this->loadConfig();
        $this->assertEquals(
            $this->container->getParameter('devtrw_parse.http.client.class'),
            'Devtrw\\ParseBundle\\Http\\ClientTest'
        );
    }

    public function testClientDefinition()
    {
        $this->loadConfig();
        $this->assertTrue($this->container->hasDefinition('devtrw_parse.http.client'));
        $definition = $this->container->getDefinition('devtrw_parse.http.client');
        $this->assertEquals('%devtrw_parse.http.client.class%', $definition->getClass());
        $this->assertEquals('%devtrw_parse.client.config%', $definition->getArgument(0));
    }

    public function testConfigurationWithMasterKey()
    {
        $config = ['master_key' => 'test_master_key'];
        $this->loadConfig($config);
        $loadedConfig = $this->container->getParameter('devtrw_parse.client.config');

        $this->assertEquals($config['master_key'], $loadedConfig['master_key']);
    }

    public function testDefaultConfigParametersLoad()
    {
        $config = [
            'app_id'   => 'test_app_id',
            'rest_key' => 'test_rest_key',
            'base_url' => 'https://test.parse.com/v1/'
        ];
        $this->loadConfig($config);
        $expectedConfig = array_merge($config, ['master_key' => false]);

        $clientConfig = $this->container->getParameter('devtrw_parse.client.config');
        $this->assertEquals($expectedConfig, $clientConfig);
    }

    private function loadConfig(array $config = [])
    {
        $config = ['devtrw_parse' => array_merge($this->defaultConfig, $config)];
        $this->extension->load($config, $this->container);
    }
}
