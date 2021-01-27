<?php

namespace bSecure\HttpClient;

use bSecure\Exception;
use bSecure\bSecure;
use bSecure\Helpers\Constant;
use bSecure\Util;

// @codingStandardsIgnoreStart
// PSR2 requires all constants be upper case. Sadly, the CURL_SSLVERSION
// constants do not abide by those rules.

// Note the values come from their position in the enums that
// defines them in cURL's source code.

// Available since PHP 5.5.19 and 5.6.3
if (!\defined('CURL_SSLVERSION_TLSv1_2')) {
    \define('CURL_SSLVERSION_TLSv1_2', 6);
}
// @codingStandardsIgnoreEnd

// Available since PHP 7.0.7 and cURL 7.47.0
if (!\defined('CURL_HTTP_VERSION_2TLS')) {
    \define('CURL_HTTP_VERSION_2TLS', 4);
}

class CurlClient implements ClientInterface
{
    private static $instance;

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $defaultOptions;

    /** @var \bSecure\Util\RandomGenerator */
    protected $randomGenerator;

    protected $userAgentInfo;

    protected $enablePersistentConnections = true;

    protected $enableHttp2;

    protected $curlHandle;

    protected $requestStatusCallback;

    /**
     * CurlClient constructor.
     *
     * Pass in a callable to $defaultOptions that returns an array of CURLOPT_* values to start
     * off a request with, or an flat array with the same format used by curl_setopt_array() to
     * provide a static set of options. Note that many options are overridden later in the request
     * call, including timeouts, which can be set via setTimeout() and setConnectTimeout().
     *
     * Note that request() will silently ignore a non-callable, non-array $defaultOptions, and will
     * throw an exception if $defaultOptions returns a non-array value.
     *
     * @param null|array|callable $defaultOptions
     * @param null|\bSecure\Util\RandomGenerator $randomGenerator
     */
    public function __construct($defaultOptions = null, $randomGenerator = null)
    {
        $this->defaultOptions = $defaultOptions;
        $this->randomGenerator = $randomGenerator ?: new Util\RandomGenerator();
        $this->initUserAgentInfo();

        $this->enableHttp2 = $this->canSafelyUseHttp2();
    }

    public function __destruct()
    {
        $this->closeCurlHandle();
    }

    public function initUserAgentInfo()
    {
        $curlVersion = \curl_version();
        $this->userAgentInfo = [
          'httplib' => 'curl ' . $curlVersion['version'],
          'ssllib' => $curlVersion['ssl_version'],
        ];
    }

    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    public function getUserAgentInfo()
    {
        return $this->userAgentInfo;
    }

    /**
     * @return bool
     */
    public function getEnablePersistentConnections()
    {
        return $this->enablePersistentConnections;
    }

    /**
     * @param bool $enable
     */
    public function setEnablePersistentConnections($enable)
    {
        $this->enablePersistentConnections = $enable;
    }

    /**
     * @return bool
     */
    public function getEnableHttp2()
    {
        return $this->enableHttp2;
    }

    /**
     * @param bool $enable
     */
    public function setEnableHttp2($enable)
    {
        $this->enableHttp2 = $enable;
    }

    /**
     * @return null|callable
     */
    public function getRequestStatusCallback()
    {
        return $this->requestStatusCallback;
    }

    /**
     * Sets a callback that is called after each request. The callback will
     * receive the following parameters:
     * <ol>
     *   <li>string $rbody The response body</li>
     *   <li>integer $rcode The response status code</li>
     *   <li>\bSecure\Util\CaseInsensitiveArray $rheaders The response headers</li>
     *   <li>integer $errno The curl error number</li>
     *   <li>string|null $message The curl error message</li>
     *   <li>boolean $shouldRetry Whether the request will be retried</li>
     *   <li>integer $numRetries The number of the retry attempt</li>
     * </ol>.
     *
     * @param null|callable $requestStatusCallback
     */
    public function setRequestStatusCallback($requestStatusCallback)
    {
        $this->requestStatusCallback = $requestStatusCallback;
    }

    // USER DEFINED TIMEOUTS

    const DEFAULT_TIMEOUT = 80;
    const DEFAULT_CONNECT_TIMEOUT = 30;

    private $timeout = self::DEFAULT_TIMEOUT;
    private $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    public function setTimeout($seconds)
    {
        $this->timeout = (int) \max($seconds, 0);

        return $this;
    }

    public function setConnectTimeout($seconds)
    {
        $this->connectTimeout = (int) \max($seconds, 0);

        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    // END OF USER DEFINED TIMEOUTS

    public function request($method, $absUrl, $headers, $params, $hasFile = false)
    {
        $method = \strtolower($method);
        $optsHeaders = [];
        $opts = [];
        $opts[\CURLOPT_RETURNTRANSFER] = true;

        if ('get' === $method) {
            $opts[\CURLOPT_HTTPGET] = 1;
            if (\count($params) > 0) {
                $absUrl = "{$absUrl}?{$params}";
            }
        } elseif ('post' === $method) {
            $opts[\CURLOPT_POST] = true;
            $opts[\CURLOPT_POSTFIELDS] = http_build_query($params);
            $opts[\CURLOPT_VERBOSE] = true;
            $optsHeaders = [
              'X-HTTP-Method-Override: POST',
              'Content-Type:application/x-www-form-urlencoded',
              'Content-Length: ' .     strlen(http_build_query($params))
            ];
        } else {
            throw new Exception\UnexpectedValueException("Unrecognized method {$method}");
        }

        $opts[\CURLOPT_URL] = $absUrl;
        $opts[\CURLOPT_HTTPHEADER] = array_merge($optsHeaders,$headers);
        list($rbody, $rcode, $rheaders) = $this->executeRequestWithRetries($opts, $absUrl);

        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @param array $opts cURL options
     * @param string $absUrl
     */
    private function executeRequestWithRetries($opts, $absUrl)
    {
        $numRetries = 0;

        while (true) {
            $rcode = 0;
            $errno = 0;
            $message = null;

            // Create a callback to capture HTTP headers for the response
            $rheaders = new Util\CaseInsensitiveArray();
            $this->resetCurlHandle();
            \curl_setopt_array($this->curlHandle, $opts);
            $rbody = \curl_exec($this->curlHandle);


            if (false === $rbody) {
                $errno = \curl_errno($this->curlHandle);
                $message = \curl_error($this->curlHandle);
            } else {
                $rcode = \curl_getinfo($this->curlHandle, \CURLINFO_HTTP_CODE);
            }
            if (!$this->getEnablePersistentConnections()) {
                $this->closeCurlHandle();
            }

            break;
        }

        if (false === $rbody) {
            $this->handleCurlError($absUrl, $errno, $message, $numRetries);
        }
        return [$rcode, $rbody, $rheaders];
    }

    /**
     * @param string $url
     * @param int $errno
     * @param string $message
     * @param int $numRetries
     *
     * @throws Exception\ApiConnectionException
     */
    private function handleCurlError($url, $errno, $message, $numRetries)
    {
        switch ($errno) {
            case \CURLE_COULDNT_CONNECT:
            case \CURLE_COULDNT_RESOLVE_HOST:
            case \CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to bSecure ({$url}).  Please check your "
                  . 'internet connection and try again, or';

                break;

            case \CURLE_SSL_CACERT:
            case \CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify bSecure's SSL certificate.  Please make sure "
                  . 'that your network is not intercepting certificates.  '
                  . "(Try going to {$url} in your browser.)  "
                  . 'If this problem persists,';

                break;

            default:
                $msg = 'Unexpected error communicating with bSecure.  '
                  . 'If this problem persists,';
        }
        $msg .= ' let us know at '.Constant::SUPPORT_EMAIL.'.';

        $msg .= "\n\n(Network error [errno {$errno}]: {$message})";

        if ($numRetries > 0) {
            $msg .= "\n\nRequest was retried {$numRetries} times.";
        }

        throw new Exception\ApiConnectionException($msg);
    }

    /**
     * Initializes the curl handle. If already initialized, the handle is closed first.
     */
    private function initCurlHandle()
    {
        $this->closeCurlHandle();
        $this->curlHandle = \curl_init();
    }

    /**
     * Closes the curl handle if initialized. Do nothing if already closed.
     */
    private function closeCurlHandle()
    {
        if (null !== $this->curlHandle) {
            \curl_close($this->curlHandle);
            $this->curlHandle = null;
        }
    }

    /**
     * Resets the curl handle. If the handle is not already initialized, or if persistent
     * connections are disabled, the handle is reinitialized instead.
     */
    private function resetCurlHandle()
    {
        if (null !== $this->curlHandle && $this->getEnablePersistentConnections()) {
            \curl_reset($this->curlHandle);
        } else {
            $this->initCurlHandle();
        }
    }

    /**
     * Indicates whether it is safe to use HTTP/2 or not.
     *
     * @return bool
     */
    private function canSafelyUseHttp2()
    {
        // Versions of curl older than 7.60.0 don't respect GOAWAY frames
        // (cf. https://github.com/curl/curl/issues/2416), which bSecure use.
        $curlVersion = \curl_version()['version'];

        return \version_compare($curlVersion, '7.60.0') >= 0;
    }
}