<?php

namespace Devisfunny\PhpShopifyApi;

class ShopifyApiClient
{

    public static function install_url($shop, $api_key)
    {
        return "http://$shop/admin/api/auth?api_key=$api_key";
    }


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


    public static function permission_url($shop, $api_key, $scope = array(), $redirect_uri = '')
    {
        $scope = empty($scope) ? '' : '&scope=' . implode(',', $scope);
        $redirect_uri = empty($redirect_uri) ? '' : '&redirect_uri=' . urlencode($redirect_uri);
        return "https://$shop/admin/oauth/authorize?client_id=$api_key$scope$redirect_uri";
    }


    public static function oauth_access_token($shop, $api_key, $shared_secret, $code)
    {
        return self::_api('POST', "https://$shop/admin/oauth/access_token", NULL, array('client_id' => $api_key, 'client_secret' => $shared_secret, 'code' => $code));
    }


    public static function client($shop, $shops_token, $api_key, $shared_secret, $private_app = false)
    {
        $baseurl = "https://$shop/";

        return function ($method, $path, $params = array(), &$response_headers = array()) use ($baseurl, $shops_token) {
            $url = $baseurl . ltrim($path, '/');
            $query = in_array($method, array('GET', 'DELETE')) ? $params : array();
            $payload = in_array($method, array('POST', 'PUT')) ? stripslashes(json_encode($params)) : array();

            $request_headers = array();
            array_push($request_headers, "X-Shopify-Access-Token: $shops_token");
            if (in_array($method, array('POST', 'PUT'))) array_push($request_headers, "Content-Type: application/json; charset=utf-8");

            return self::_api($method, $url, $query, $payload, $request_headers, $response_headers);
        };
    }

    private static function _api($method, $url, $query = '', $payload = '', $request_headers = array(), &$response_headers = array())
    {
        $response = wcurl::wcurl($method, $url, $query, $payload, $request_headers, $response_headers);
        $response = json_decode($response, true);

        if (isset($response['errors']) or ($response_headers['http_status_code'] >= 400)) {
            throw new ApiException(compact('method', 'path', 'params', 'response_headers', 'response', 'shops_myshopify_domain', 'shops_token'));
        }

        return (is_array($response) and !empty($response)) ? array_shift($response) : $response;
    }


    public static function calls_made($response_headers)
    {
        return self::_shop_api_call_limit_param(0, $response_headers);
    }


    public static function call_limit($response_headers)
    {
        return self::_shop_api_call_limit_param(1, $response_headers);
    }


    public static function calls_left($response_headers)
    {
        return self::call_limit($response_headers) - self::calls_made($response_headers);
    }


    private static function _shop_api_call_limit_param($index, $response_headers)
    {
        $params = explode('/', $response_headers['http_x_shopify_shop_api_call_limit']);
        return (int)$params[$index];
    }
}





