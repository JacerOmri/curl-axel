<?php

namespace CurlAxel\Handlers\Chunk;


class Memory extends ChunkHandler
{

    /**
     * @return string
     */
    function getStreamPath()
    {
        return 'php://memory';
    }
}