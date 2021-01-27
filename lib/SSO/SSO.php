<?php

namespace bSecure;
use bSecure\Helpers\Constant;

/**
 * Class SSO.
 */
class SSO
{
    /** @var string The merchant order id to be used for Create Order requests. */
    public static $state = null;
    public static $stateDefinition = false;
    /** @var string The merchant order id to be used for Create Order requests. */
    public static $authCode = null;
    public static $authCodeDefinition = false;

    /** @var array The customer object to be used for Create Order requests. */
    private static $scope = Constant::SCOPE;

    /** @var array The products object to be used for Create Order requests. */
    private static $response_type = Constant::RESPONSE_TYPE;

    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    private static function setState($state)
    {
        self::$stateDefinition =true;
        self::$state = $state;
    }

    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    private static function setAuthCode($authCode)
    {
        self::$authCodeDefinition =true;
        self::$authCode = $authCode;
    }
    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
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
     * @return string the Auth Token used for requests
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
     * @return string the Auth Token used for requests
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

            return SSOController::authenticateClient(self::setAuthenticationPayload());
        }
    }
}