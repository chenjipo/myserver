<?php

namespace YXLib\exceptions;

class ErrorClassException extends Exception
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
        parent::__construct('ERROR_CLASS: '.$message, $raw, self::ERROR_CLASS);
    }
}
