<?php

namespace Devisfunny\PhpShopifyApi;


class ApiException extends \Exception
{
    protected $info;

    function __construct($info)
    {
        $this->info = $info;
        parent::__construct($info['response_headers']['http_status_message'], $info['response_headers']['http_status_code']);
    }

    function getInfo()
    {
        $this->info;
    }
}