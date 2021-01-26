<?php

namespace bSecure;

/**
 * Class ApiResponse.
 */
class ApiResponse
{
    /**
     * @var null|array
     */
    public $body;

    /**
     * @var int
     */
    public $status;

    /**
     * @var null|array
     */
    public $message;

    /**
     * @var null|array
     */
    public $exception;

    /**
     * @param null|array|string $body
     * @param int $status
     * @param null|array $message
     * @param null|array $exception
     */
    public function __construct( $body, $status, $message, $exception)
    {
        $this->body = $body;
        $this->status = $status;
        $this->message = $message;
        $this->exception = $exception;
    }
}