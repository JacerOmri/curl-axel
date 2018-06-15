<?php

namespace CurlAxel\Handlers\Chunk;


class TempFile extends ChunkHandler
{

    /**
     * @return string
     */
    function getStreamPath()
    {
        return tempnam(sys_get_temp_dir(), 'ca');
    }
}