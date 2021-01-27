<?php

namespace bSecure;
use bSecure\Helpers\Constant;

/**
 * Class SSO.
 */
class SSO
{
    /** @var string The state to be used to verify successful authorization callback. */
    public static $state = null;
    public static $stateDefinition = false;
    /** @var string The authCode to be used for receiving customer profile after successful client authorization. */
    public static $authCode = null;
    public static $authCodeDefinition = false;

    /** @var string The scope is to be used for authorization callback. */
    private static $scope = Constant::SCOPE;

    /** @var string The response_type is to be used for authorization callback. */
    private static $response_type = Constant::RESPONSE_TYPE;

    /**
     * Sets the state to be used to verify successful authorization callback.
     *
     * @param string $state
     */
    private static function setState($state)
    {
        self::$stateDefinition =true;
        self::$state = $state;
    }

    /**
     * Sets the authCode to be used for receiving customer profile after successful client authorization.
     *
     * @param string $authCode
     */
    private static function setAuthCode($authCode)
    {
        self::$authCodeDefinition =true;
        self::$authCode = $authCode;
    }

    /**
     * Sets the authorization request link to be used for sso requests.
     *
     */
    private static function setAuthenticationLink()
    {
        $clientId = bSecure::getClientId();
        $scope = self::$scope;
        $response_type = self::$response_type;
        $state = self::$state;
        return bSecure::$loginBase.'?client_id='.$clientId.'&scope='.$scope.'&response_type='.$response_type.'&state='.$state;
    }
    /**
     * Sets the authentication request payload.
     *
     */
    private static function setAuthenticationPayload()
    {
        $clientId = bSecure::getClientId();
        if($clientId == null)
        {
            $msg = 'No charges provided.  (HINT: set your sub_total, discount and total amount using '
              . '"bSecure::setCharges(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }
        return [
          "client_id" => $clientId,
          "scope" => self::$scope,
          "response_type" => self::$response_type,
          "state" => self::$state
        ];
    }

    /**
     * @return array The customer object
     */
    public static function customerProfile($authCode)
    {
        self::setAuthCode($authCode);
        if(!self::$authCodeDefinition)
        {
            $msg = 'No charges provided.  (HINT: set your sub_total, discount and total amount using '
              . '"bSecure::setCharges(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }
            return SSOController::customerProfile([
              "code" => $authCode,
            ]);
    }


    /**
     * @return string the authentication request weblink used for sso requests
     *
     * @throws \bSecure\Exception\UnexpectedValueException if the request fails
     *
     */
    public static function clientAuthenticate($state)
    {
        self::setState($state);
        if(!self::$stateDefinition)
        {
            $msg = 'No charges provided.  (HINT: set your sub_total, discount and total amount using '
              . '"bSecure::setCharges(<ARRAY>). See"'
              . Constant::DOCUMENTATION_LINK.' for details, '
              . 'or email '.Constant::SUPPORT_EMAIL.' if you have any questions.';
            throw new Exception\UnexpectedValueException($msg);
        }

        else{
            self::setAuthenticationPayload();
            return self::setAuthenticationLink();
        }
    }
}