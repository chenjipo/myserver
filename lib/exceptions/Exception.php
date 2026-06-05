<?php

namespace YXLib\exceptions;

class Exception extends \Exception
{
    const UNKNOWN_ERROR = 9999;

    const ERROR_METHOD = 1;

    const ERROR_CLASS = 2;

    const INVALID_CONFIG = 3;

    const INVALID_ARGUMENT = 4;

    const ERROR_GATEWAY = 5;

    const INVALID_SIGN = 6;

    const ERROR_BUSINESS = 7;


    /**
     * Raw error info.
     *
     * @var array
     */
    public $raw;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param array|string $raw
     * @param int|string   $code
     */
    public function __construct($message = '', $raw = [], $code = self::UNKNOWN_ERROR)
    {
        $message = '' === $message ? 'Unknown Error' : $message;
        $this->raw = is_array($raw) ? $raw : [$raw];

        parent::__construct($message, intval($code));
    }
}
