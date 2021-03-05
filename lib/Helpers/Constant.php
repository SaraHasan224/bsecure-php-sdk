<?php

namespace bSecure\Helpers;

class Constant
{
    const YES = 1;
    const NO = 0;

    const SCOPE = "profile";
    const RESPONSE_TYPE = "code";

    const HTTP_RESPONSE_STATUSES = [
      'success'             => 200,
      'failed'              => 400,
      'validationError'     => 422,
      'authenticationError' => 401,
      'authorizationError'  => 403,
      'serverError'         => 500,
    ];

    const AUTH_GRANT_TYPE = "client_credentials";

    const AUTH_SERVER_URL = 'https://api.bsecure.pk/';

    const LOGIN_REDIRECT_URL = 'https://login.bsecure.pk/auth/sso';


    const DEFAULT_APP_ENVIRONMENT = 'sandbox';

    const API_VERSION = 'v1';

    const API_ENDPOINTS = [
      'oauth'               => Constant::API_VERSION . '/oauth/token',
      'create_order' => Constant::API_VERSION . '/order/create',
      'order_status' => Constant::API_VERSION . '/order/status',
      'verify_client' => Constant::API_VERSION . '/sso/verify-client',
      'customer_profile' => Constant::API_VERSION . '/sso/customer/profile',
    ];

    const APP_ENVIRONMENT = [
      'live',
      'sandbox',
    ];

    const APP_TYPE = [
      'checkout' => 1,
      'sdk' => 2,
    ];

    const OrderStatus = [
      'created'       => 1,
      'initiated'     => 2,
      'placed'        => 3,
      'awaiting-confirmation' => 4,
      'canceled' => 5,
      'expired' => 6,
      'failed' => 7
    ];

    const BUILDERS_DASHBOARD_LINK = "http://builder.bsecure.pk/";
    const INTEGRATION_TAB_LINK = "http://builder.bsecure.pk/integration-live";
    const SUPPORT_EMAIL = "hello@bsecure.pk";
    const DOCUMENTATION_LINK = "https://www.bsecure.pk/developers";

}
