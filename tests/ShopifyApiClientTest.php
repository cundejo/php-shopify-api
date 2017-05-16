<?php

require(__DIR__ . '/../src/ShopifyApiClient.php');

use Devisfunny\PhpShopifyApi\ShopifyApiClient;

/**
 *  Corresponding Class to test YourClass class
 *
 *  For each class in your library, there should be a corresponding Unit-Test for it
 *  Unit-Tests should be as much as possible independent from other test going on.
 *
 * @author yourname
 */
class ShopifyApiClientTest extends PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = new ShopifyApiClient();
    }

    public function testIsThereAnySyntaxError()
    {
        $this->assertTrue(is_object($this->object));
    }


    public function testMethodIsValidRequest()
    {
        $this->assertTrue(true);
    }


    public function testMethodPermissionUrl()
    {
        $this->assertTrue(true);
    }

    public function testMethodIsValidShopName()
    {
        $this->assertTrue(true);
    }

}