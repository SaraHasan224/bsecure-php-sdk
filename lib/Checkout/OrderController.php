<?php

namespace bSecure;

use bSecure\Helpers\Constant;

abstract class OrderController
{
    /**
     * Create an order on your builder's behalf on bsecure server
     *
     * @param array $orderPayload
     *
     * @throws \bSecure\ApiResponse if the request fails
     *
     * @return \bSecure\ApiResponse
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

    /**
     * Return order's status
     *
     * @param string $orderRef
     *
     * @throws \bSecure\ApiResponse if the request fails
     * @return \bSecure\ApiResponse
    */

    public static function orderStatus($orderRef)
    {
        $requestor = new ApiRequest();
        $response = $requestor->request(
          'post',
          Constant::API_ENDPOINTS['order_status'],
          ['order_ref' => $orderRef],
          Constant::YES
        );
        return $response[0];
    }
}