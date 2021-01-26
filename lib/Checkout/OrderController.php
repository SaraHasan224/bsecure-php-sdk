<?php

namespace bSecure;

use bSecure\Helpers\Constant;

abstract class OrderController
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
    public static function createOrder($orderPayload)
    {
        $requestor = new ApiRequest();
        $response = $requestor->request(
          'post',
          Constant::API_ENDPOINTS['create_order'],
          $orderPayload,
          Constant::YES
        );
        return $response[0];
    }
}