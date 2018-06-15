<?php

namespace CurlAxel\Handlers\Chunk;


use Curl\Curl;

abstract class ChunkHandler
{

    /**
     * @var array
     */
    private $sliceMap;

    /**
     * @param Curl $curl
     * @param $range
     * @return Curl
     */
    public function add(Curl $curl, $range)
    {
        $handler = fopen($this->getStreamPath(), 'w+');
        $curl->setOpt(CURLOPT_FILE, $handler);
        $this->sliceMap[$range] = $handler;

        return $curl;
    }

    /**
     * @param $destination
     */
    public function combine($destination)
    {
        foreach (array_keys($this->sliceMap) as $range) {
            $handler = $this->sliceMap[$range];

            rewind($handler);
            stream_copy_to_stream($handler, $destination);
            fclose($handler);
        }
    }

    /**
     * @return string
     */
    abstract protected function getStreamPath();
}