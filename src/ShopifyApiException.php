<?php

namespace Devisfunny\PhpShopifyApi;

/**
 * Class ShopifyApiException
 *
 * @package Devisfunny\PhpShopifyApi
 * @author Oliver Sosa <oliver@devisfunny.com>
 */
class ShopifyApiException extends \Exception
{
    function __construct($errors, $statusCode)
    {
        parent::__construct('Shopify Api Exception: ' . json_encode($errors), $statusCode);
    }
}