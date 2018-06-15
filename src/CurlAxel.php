<?php

namespace CurlAxel;


use Curl\Curl;
use Curl\MultiCurl;
use CurlAxel\Exceptions\CurlException;
use CurlAxel\Handlers\Chunk\ChunkHandler;


/**
 * Class CurlAxel
 * @package CurlAxel
 */
class CurlAxel
{
    /**
     * @var array
     */
    private static $DEFAULT_OPTS = [
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_FRESH_CONNECT => true

    ];
    /**
     * @var MultiCurl
     */
    private $multicurl;
    /**
     * @var
     */
    private $url;

    /**
     * @var ChunkHandler
     */
    private $chunkHandler;

    /**
     * @var string
     */
    private $output;
    /**
     * @var int
     */
    private $sliceCount = 5;
    /**
     * @var
     */
    private $ranges;

    /**
     * CurlAxel constructor.
     */
    public function __construct()
    {
        $this->multicurl = new MultiCurl();
    }

    /**
     * @throws CurlException
     * @throws \ErrorException
     */
    public function download()
    {
        $this->buildHandlers();

        $this->multicurl->success(function ($instance) {
            echo 'call to "' . $instance->url . '" was successful.' . "\n";
            echo 'response: ' . $instance->response . "\n";
        });
        $this->multicurl->error(function ($instance) {
            echo 'call to "' . $instance->url . '" was unsuccessful.' . "\n";
            echo 'error code: ' . $instance->errorCode . "\n";
            echo 'error message: ' . $instance->errorMessage . "\n";
        });
        $this->multicurl->complete(function ($instance) {
            echo 'call to "' . $instance->url . '" completed.' . "\n";
        });

        $this->multicurl->start();
        $this->combineFiles($this->output);
    }

    /**
     * @throws CurlException
     * @throws \ErrorException
     */
    private function buildHandlers()
    {
        $this->ranges = RangeUtils::getDashedSlices(
            $this->getFileSize(),
            $this->sliceCount
        );

        foreach ($this->ranges as $range) {
            $this->multicurl->addCurl(
                $this->buildSingleHandler($range)
            );
        }
    }

    /**
     * @return int
     * @throws CurlException
     * @throws \ErrorException
     */
    private function getFileSize()
    {
        $curl = new Curl();
        $curl->head($this->url);

        if ($curl->error) {
            throw new CurlException($curl);
        }

        return (integer)$curl->responseHeaders['content-length'];
    }

    /**
     * @param $range
     * @return Curl
     * @throws \ErrorException
     */
    private function buildSingleHandler($range)
    {
        $curl = new Curl();
        $curl->setUrl($this->url);
        $curl->setOpts(self::$DEFAULT_OPTS);
        $curl->setOpt(CURLOPT_RANGE, $range);

        $curl = $this->chunkHandler->add($curl, $range);

        return $curl;
    }

    /**
     * @param $output
     */
    private function combineFiles($output)
    {
        $outputHandler = \fopen($output, 'w+');
        rewind($outputHandler);

        $this->chunkHandler->combine($outputHandler);

        fclose($outputHandler);
    }

    /**
     * Get the value of sliceCount
     */
    public function getSliceCount()
    {
        return $this->sliceCount;
    }

    /**
     * Set the value of sliceCount
     *
     * @param $sliceCount
     * @return  self
     */
    public function setSliceCount($sliceCount)
    {
        $this->sliceCount = $sliceCount;

        return $this;
    }

    /**
     * @param ChunkHandler $chunkHandler
     * @return CurlAxel
     */
    public function setChunkHandler(ChunkHandler $chunkHandler)
    {
        $this->chunkHandler = $chunkHandler;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return CurlAxel
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $output
     * @return CurlAxel
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
}
