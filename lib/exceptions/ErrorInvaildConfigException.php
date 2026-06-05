<?php

namespace YXLib\exceptions;

class ErrorInvaildConfigException extends Exception
{
    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param array|string $raw
     */
    public function __construct($message, $raw = [])
    {
        parent::__construct('ERROR: '.$message, $raw, self::INVALID_CONFIG);
    }
}
