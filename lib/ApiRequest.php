<?php

namespace bSecure;
use bSecure\Helpers\Constant;

/**
 * Class ApiRequest.
 */
class ApiRequest
{

    /**
     * @var string
     */
    private $_apiBase;

    /**
     * @var HttpClient\ClientInterface
     */
    private static $_httpClient;

    /**
     * ApiRequest constructor.
     *
     * @param null|string $apiBase
     */
    public function __construct( $apiBase = null)
    {
        if (!$apiBase) {
            $apiBase = bSecure::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    /**
     * @param string $method
     * @param string $url
     * @param null|array $params
     * @param null|array $headers
     *
     * @throws Exception\ApiErrorException
     *
     * @return array tuple containing (ApiReponse, API key)
     */
    public function request($method, $url, $params = null, $authKey = Constant::NO)
    {
        $params = $params ?: [];
        list($rcode, $rbody, $rheaders) =
          $this->_requestRaw($method, $url, $params, $authKey);
        list($message, $exception, $rbody) = $this->_interpretResponse( $rcode,$rbody, $rheaders);

        $resp = new ApiResponse($rbody, $rcode, $message, $exception);
        return [$resp];
    }

    /**
     * @static
     *
     * @param string $apiKey
     *
     * @return array
     */
    private static function _defaultHeaders($apiKey = false)
    {
        $authHeader = [];
        $defaultHeaders = [
//          'Content-Type' => 'application/json',
        ];

        if($apiKey)
        {
            $authHeader = ['Authorization' => 'Bearer ' .bSecure::getAuthToken()];
        }
        $headers = array_merge($defaultHeaders,$authHeader);
        return $headers;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     *
     * @throws Exception\AuthenticationException
     * @throws Exception\ApiConnectionException
     *
     * @return array
     */
    private function _requestRaw($method, $url, $params, $authKey)
    {
        $absUrl = $this->_apiBase . $url;
        $defaultHeaders = $this->_defaultHeaders($authKey);

        $combinedHeaders = $defaultHeaders;
        $rawHeaders = [];

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }
        list($rbody, $rcode, $rheaders) = $this->httpClient()->request(
          $method,
          $absUrl,
          $rawHeaders,
          $params,
          false
        );
        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @param string $rbody
     * @param int $rcode
     * @param array $rheaders
     *
     * @throws Exception\UnexpectedValueException
     * @throws Exception\ApiErrorException
     *
     * @return array
     */
    private function _interpretResponse( $rcode,$rbody, $rheaders)
    {
        $resp = \json_decode($rbody, true);
        $jsonError = \json_last_error();

        if (null === $resp && \JSON_ERROR_NONE !== $jsonError) {
            $msg = "Invalid response body from API: {$rbody} "
              . "(HTTP response code was {$rcode}, json_last_error() was {$jsonError})";
            throw new Exception\UnexpectedValueException($msg, $rcode);
        }
        $message = $resp['message'];
        $exception = $resp['exception'];
        $rbody = $resp['body'];

        return [$message, $exception, $rbody];
    }

    /**
     * @param string $rbody a JSON string
     * @param int $rcode
     * @param array $rheaders
     * @param array $resp
     *
     * @throws Exception\UnexpectedValueException
     * @throws Exception\ApiErrorException
     */
    public function handleErrorResponse($rbody, $rcode, $rheaders, $resp)
    {
        $errorData = $resp['message'];
        $error = null;
        if (\is_array($errorData) && $errorData != []) {
            $error = self::_specificAPIError($resp,$rheaders,$rcode);
        }else{
            if (!\is_array($resp) || !isset($resp['error'])) {
                $msg = "Invalid response object from API: {$rbody} "
                  . "(HTTP response code was {$rcode})";

                throw new Exception\UnexpectedValueException($msg);
            }
        }

        throw $error;
    }
    /**
     * @static
     *
     * @param string $rbody
     * @param int    $rcode
     * @param array  $rheaders
     * @param array  $resp
     * @param array  $errorData
     *
     * @return Exception\ApiErrorException
     */
    private static function _specificAPIError($errorData,$rheaders, $code)
    {
        $msg = isset($errorData['message']) ? $errorData['message'] : null;
        $body = isset($errorData['body']) ? $errorData['body'] : null;
        $code = isset($errorData['status']) ? $errorData['status'] : null;
        $exception = isset($errorData['exception']) ? $errorData['exception'] : null;

        return Exception\InvalidRequestException::factory($msg, $code, $body, $exception, $rheaders, $code);
    }
    /**
     * @static
     *
     * @param HttpClient\ClientInterface $client
     */
    public static function setHttpClient($client)
    {
        self::$_httpClient = $client;
    }


    /**
     * @return HttpClient\ClientInterface
     */
    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance();
        }

        return self::$_httpClient;
    }
}