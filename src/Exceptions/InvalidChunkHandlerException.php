<?php

namespace CurlAxel\Exceptions;


class InvalidChunkHandlerException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct($this->format($name));
    }

    public function format($name)
    {
        return 'Error: ' . $name. ': is not a Chunk Handler class';
    }
}