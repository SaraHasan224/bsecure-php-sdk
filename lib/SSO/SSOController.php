<?php

namespace bSecure;

use bSecure\Helpers\Constant;

abstract class SSOController
{
    /**
     * Generate authorization credentials to connect your builder's account to your platform and
     * fetch the record from bSecure.
     *
     * @param array $params
     *
     * @throws \Stripe\Exception\OAuth\OAuthErrorException if the request fails
     *
     * @return OAuthObject object containing your authorization credentials
     */
    public static function authenticateClient($payload)
    {
        $requestor = new ApiRequest();
        $response = $requestor->request(
          'post',
          Constant::API_ENDPOINTS['verify_client'],
          $payload,
          Constant::NO
        );
        return $response[0];
    }
    /**
     * Generate authorization credentials to connect your builder's account to your platform and
     * fetch the record from bSecure.
     *
     * @param array $params
     *
     * @throws \Stripe\Exception\OAuth\OAuthErrorException if the request fails
     *
     * @return OAuthObject object containing your authorization credentials
     */
    public static function customerProfile($param)
    {
        $requestor = new ApiRequest();
        $response = $requestor->request(
          'post',
          Constant::API_ENDPOINTS['customer_profile'],
          $param,
          Constant::YES
        );
        return $response[0];
    }
}