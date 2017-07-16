<?php

namespace Devisfunny\PhpShopifyApi;

/**
 * Class ShopifyApiClient
 * In this class are encapsulated all the functions to work with Shopify.
 *
 * @package Devisfunny\PhpShopifyApi
 * @author Oliver Sosa <oliver@devisfunny.com>
 */
class ShopifyApiClient
{
    /**
     * Return the install URL of the app to the store.
     * @param $shop
     * @param $api_key
     * @return string
     */
    public static function install_url($shop, $api_key)
    {
        return "http://$shop/admin/api/auth?api_key=$api_key";
    }

    /**
     * Validates all the request from Shopify.
     * @param $query_params
     * @param $shared_secret
     * @return bool
     */
    public static function is_valid_request($query_params, $shared_secret)
    {
        $seconds_in_a_day = 24 * 60 * 60;
        $older_than_a_day = $query_params['timestamp'] < (time() - $seconds_in_a_day);
        if ($older_than_a_day) {
            return false;
        }

        $signature = $query_params['hmac'];
        unset($query_params['hmac']);

        foreach ($query_params as $key => $val) {
            $params[] = "$key=$val";
        }
        sort($params);
        $string_params = implode('&', $params);

        return (hash_hmac('sha256', $string_params, $shared_secret) === $signature);
    }

    /**
     * Return the permission URL where the store owner see all the permissions required by your app.
     * @param        $shop
     * @param        $api_key
     * @param array  $scope
     * @param string $redirect_uri
     * @return string
     */
    public static function permission_url($shop, $api_key, $scope = array(), $redirect_uri = '')
    {
        $scope = empty($scope) ? '' : '&scope=' . implode(',', $scope);
        $redirect_uri = empty($redirect_uri) ? '' : '&redirect_uri=' . urlencode($redirect_uri);

        return "https://$shop/admin/oauth/authorize?client_id=$api_key$scope$redirect_uri";
    }

    /**
     * @param $shop
     * @param $api_key
     * @param $shared_secret
     * @param $code
     * @return array|mixed
     */
    public static function oauth_access_token($shop, $api_key, $shared_secret, $code)
    {
        return self::_api('POST', "https://$shop/admin/oauth/access_token", NULL, array('client_id' => $api_key, 'client_secret' => $shared_secret, 'code' => $code));
    }

    /**
     * Generic API client function.
     * @param string $shop Shop name
     * @param string $shops_token
     * @param string $path Endpoint in the Shopify API
     * @param string $method HTTP Method to execute
     * @param array  $params Parameters to pass
     * @return array|mixed
     */
    public static function client($shop, $shops_token, $path, $method, $params = array())
    {
        $baseurl = "https://$shop/";
        $url = $baseurl . ltrim($path, '/');
        $query = in_array($method, array('GET', 'DELETE')) ? $params : array();
        $payload = in_array($method, array('POST', 'PUT')) ? preg_replace('/\\\\(?!n|\\")/', '', json_encode($params)) : array();

        $request_headers = array();
        array_push($request_headers, "X-Shopify-Access-Token: $shops_token");
        if (in_array($method, array('POST', 'PUT'))) {
            array_push($request_headers, "Content-Type: application/json; charset=utf-8");
        }

        return self::_api($method, $url, $query, $payload, $request_headers);
    }

    /**
     * @param        $method
     * @param        $url
     * @param string $query
     * @param string $payload
     * @param array  $request_headers
     * @param array  $response_headers
     * @return array|mixed
     * @throws \Devisfunny\PhpShopifyApi\ShopifyApiException
     */
    private static function _api($method, $url, $query = '', $payload = '', $request_headers = array(), &$response_headers = array())
    {
        $response = Wcurl::wcurl($method, $url, $query, $payload, $request_headers, $response_headers);
        $response = json_decode($response, true);

        if (isset($response['errors']) or ($response_headers['http_status_code'] >= 400)) {
            $errors = (isset($response['errors'])) ? $response['errors'] : $response['error'];
            throw new ShopifyApiException($errors, $response_headers['http_status_code']);
        }

        return (is_array($response) and !empty($response)) ? array_shift($response) : $response;
    }

    /**
     * @param string $shopname
     * @return int
     */
    public static function is_valid_shop_name($shopname)
    {
        return preg_match('/^[a-zA-Z0-9\-]+.myshopify.com$/', $shopname);
    }

    /**
     * @param $response_headers
     * @return int
     */
    public static function calls_made($response_headers)
    {
        return self::_shop_api_call_limit_param(0, $response_headers);
    }

    /**
     * @param $response_headers
     * @return int
     */
    public static function call_limit($response_headers)
    {
        return self::_shop_api_call_limit_param(1, $response_headers);
    }

    /**
     * @param $response_headers
     * @return int
     */
    public static function calls_left($response_headers)
    {
        return self::call_limit($response_headers) - self::calls_made($response_headers);
    }

    /**
     * @param $index
     * @param $response_headers
     * @return int
     */
    private static function _shop_api_call_limit_param($index, $response_headers)
    {
        $params = explode('/', $response_headers['http_x_shopify_shop_api_call_limit']);

        return (int)$params[$index];
    }
}





