<?php

namespace bSecure;

use bSecure\Helpers\Constant;

abstract class SSOController
{
    /**
     * Returns your customer's profile object on successfull authorization request and
     * fetch the record from bSecure.
     *
     * @param array $params
     *
     * @throws \bSecure\ApiResponse if the request fails
     *
     * @return \bSecure\ApiResponse  Returns Profile object containing your customer profile
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