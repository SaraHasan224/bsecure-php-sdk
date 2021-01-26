<?php

namespace bSecure;
use bSecure\Helpers\Constant;

/**
 * Class bSecure.
 */
class bSecure
{
    /** @var string The base URL to be used for requests. */
    public static $apiBase = Constant::AUTH_SERVER_URL;

    /** @var string The login URL to be used for requests. */
    public static $loginBase = Constant::LOGIN_REDIRECT_URL;

    /** @var string The bSecure auth token to be used for Connect requests. */
    private static $authToken;

    /** @var string The bSecure client id to be used for Connect requests. */
    private static $clientId;

    /** @var string The bSecure client secret to be used for Connect requests. */
    private static $clientSecret;

    /** @var string The bSecure application environment to be used for Connect requests. */
    public static $appEnv = Constant::DEFAULT_APP_ENVIRONMENT;

    /** @var string The bSecure application environment to be used for Connect requests. */
    public static $authTokenEnv = Constant::DEFAULT_APP_ENVIRONMENT;

    /** @var null|string The version of the Stripe API to use for requests. */
    public static $apiVersion = Constant::API_VERSION;

    /** @var array The application's information (name, version, URL) */
    public static $appInfo = null;

    public static $initialize = false;

    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    public static function initialize()
    {
        self::$clientId = null;
        self::$initialize = true;
        self::$clientSecret = null;
        self::$authToken = null;
        self::$authTokenEnv = Constant::DEFAULT_APP_ENVIRONMENT;
        self::$appEnv = Constant::DEFAULT_APP_ENVIRONMENT;
    }
    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    public static function setClientId($clientId)
    {
        self::$clientId = $clientId;
    }

    /**
     * @return string the client_id used for Connect requests
     */
    public static function getClientId()
    {
        return self::$clientId;
    }

    /**
     * Sets the clientSecret to be used for Connect requests.
     *
     * @param string $clientSecret
     */
    public static function setClientSecret($clientSecret)
    {
        self::$clientSecret = $clientSecret;
    }


    /**
     * @return string the client_id used for Connect requests
     */
    public static function getClientSecret()
    {
        return self::$clientSecret;
    }


    /**
     * Sets the app_environment to be used for Connect requests.
     *
     * @param string $appEnv
     */
    public static function setAppEnvironment($appEnv)
    {
        $val = in_array($appEnv, Constant::APP_ENVIRONMENT) ? $appEnv : null;
        self::$appEnv = $val;
        self::$authTokenEnv = $val;
    }

    /**
     * @return string the Auth Token used for requests
     */
    public static function getAuthToken()
    {
        if(self::$authToken == null || gettype(self::$authToken) != "string"){
            $token =  self::setAuthToken();
            $tokenBody = (array_key_exists('body', $token)) ? $token->body : $token;
            if(array_key_exists('access_token',$tokenBody)){
                self::$authTokenEnv = array_key_exists('environment',$tokenBody) ? $tokenBody['environment'] :null;
                self::$authToken = array_key_exists('access_token',$tokenBody) ? $tokenBody['access_token'] :null;
            }else{
                return $token;
            }
        }
        if(self::$appEnv == self::$authTokenEnv)
            return self::$authToken;
        else
            throw new Exception\UnexpectedValueException('Selected environment keys are invalid');
    }

    /**
     * Sets the authToken to be used for Connect requests.
     *
     * @param string $authToken
     */
    public static function setAuthToken()
    {
        return OAuth::token();
    }

    /**
     * @return array | null The application's environment
     */
    public static function getAppInfo()
    {
        self::setAppInfo();
        return self::$appInfo;
    }


    /**
     * @return string The application's api version
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $authToken The application's authentication token
     * @param null|string $environment The application's environment
     * @param null|string $version The application's API version
     */
    public static function setAppInfo()
    {
        self::$appInfo = self::$appInfo ?: [];
        self::$appInfo['client_id'] = self::$clientId;
        self::$appInfo['client_secret'] = self::$clientSecret;
        self::$appInfo['environment'] = self::$appEnv;
    }
}