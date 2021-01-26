<?php

namespace bSecure;

use bSecure\Helpers\Constant;

abstract class OAuth
{

    // Self-referential 'abstract' declaration
    const GRANT_TYPE = Constant::AUTH_GRANT_TYPE;

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
    public static function token()
    {
        $credentials = self::verifyAppCredentials();

        $requestor = new ApiRequest();
        $response = $requestor->request(
          'post',
          Constant::API_ENDPOINTS['oauth'],
          $credentials,
          Constant::NO
        );
        return $response[0];
    }

    private static function _getClientId()
    {
        $APP_INFO = bSecure::getAppInfo();

        $clientId = ($APP_INFO && \array_key_exists('client_id', $APP_INFO)) ? $APP_INFO['client_id'] : null;
        if (null === $clientId) {
            $clientId = bSecure::getClientId();
        }
        if (null === $clientId) {
            $msg = 'No client_id provided.  (HINT: set your client_id using '
              . '"bSecure::setClientId(<CLIENT-ID>)".  You can find your client_ids '
              . 'in your bSecure Builder\'s dashboard at '
              . Constant::BUILDERS_DASHBOARD_LINK.', '
              . 'after registering your account as a platform. See '
              . '.Constant::SUPPORT_EMAIL.'.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\AuthenticationException($msg);
        }

        return $clientId;
    }

    private static function _getClientSecret()
    {
        $APP_INFO = bSecure::getAppInfo();

        $clientSecret = ($APP_INFO && \array_key_exists('client_secret', $APP_INFO)) ? $APP_INFO['client_secret'] : null;

        if (null === $clientSecret) {
            $clientSecret = bSecure::getClientSecret();
        }
        if (null === $clientSecret) {
            $msg = 'No client_secret provided.  (HINT: set your client_secret using '
              . '"bSecure::setClientSecret(<CLIENT-SECRET>)".  You can find your client_secrets '
              . 'in your bSecure Builder\'s dashboard at '
              . Constant::BUILDERS_DASHBOARD_LINK.', '
              . 'after registering your account as a platform. See '
              . Constant::INTEGRATION_TAB_LINK.','.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';

            throw new Exception\AuthenticationException($msg);
        }

        return $clientSecret;
    }

    private static function verifyAppCredentials()
    {

        return [
          "grant_type"=> self::GRANT_TYPE,
          'client_id' => self::_getClientId(),
          'client_secret' => self::_getClientSecret(),
        ];
    }
}