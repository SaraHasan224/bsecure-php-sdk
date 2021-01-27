<?php

namespace bSecure\Exception;

/**
 * InvalidRequestException is thrown when a request is initiated with invalid
 * parameters.
 */
class InvalidRequestException extends ApiErrorException
{
    /**
     * Creates a new InvalidRequestException exception.
     *
     * @param string $message the exception message
     * @param null|int $httpStatus the HTTP status code
     * @param null|string $httpBody the HTTP body as a string
     * @param null|array $jsonBody the JSON deserialized body
     * @param null|array|\bSecure\Util\CaseInsensitiveArray $httpHeaders the HTTP headers array
     * @param null|string $code the bSecure error code
     *
     * @return InvalidRequestException
     */
    public static function factory(
      $message,
      $httpStatus = null,
      $httpBody = null,
      $jsonBody = null,
      $httpHeaders = null,
      $Code = null
    ) {
        var_dump($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $message = is_array($message) ? implode (", ", $message) : $message;
        $instance = parent::factory($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders,$Code);
        return $instance;
    }
}