<?php

namespace CurlAxel\Handlers\Chunk;


class Custom extends ChunkHandler
{
    private $uri;

    /**
     * Custom ChunkHandler constructor.
     * @param $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    function getStreamPath()
    {
        return tempnam($this->uri, 'ca');
    }
}