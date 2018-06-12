<?php

namespace CurlAxel;

use \Curl\MultiCurl;
use \Curl\Curl;
use CurlAxel\Exceptions\CurlException;

class CurlAxel
{
    private $multicurl;
    private $url;
    private $output;
    private $sliceCount = 5;
    private $sliceMap;
    private $ranges;

    const DEFAULT_OPTS = [
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_FRESH_CONNECT => true

    ];

    public function __construct($url, $output = 'download')
    {
        $this->multicurl = new MultiCurl();
        $this->url = $url;
        $this->output = $output;
    }

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


    private function combineFiles($output)
    {
        $outputHandler = \fopen($output, 'w+');
        fseek($outputHandler, 0, SEEK_SET);

        foreach ($this->ranges as $range) {
            $handler = $this->sliceMap[$range];
            fseek($handler, 0, SEEK_SET);
            
            while (!feof($handler)) {
                $contents = fread($handler, 10*1024*1024);
                fwrite($outputHandler, $contents);
            }
            /* close current file handle */
            fclose($handler);
            
            /* remove part file */
            //$this->deleteFile($handler);
        }

        fclose($outputHandler);
    }

    private function buildHandlers()
    {
        $this->ranges = \CurlAxel\RangeUtils::getDashedSlices(
            $this->getFileSize(),
            $this->sliceCount
        );

        foreach ($this->ranges as $range) {
            $this->multicurl->addCurl(
                $this->buildSingleHandler($range)
            );
        }
    }

    private function deleteFile($handler)
    {
        $meta_data = stream_get_meta_data($handler);
        $filename = $meta_data["uri"];
        die($filename);
        unlink($filename);
    }

    private function buildSingleHandler($range)
    {
        $curl = new Curl();
        $curl->setUrl($this->url);
        $curl->setOpts(self::DEFAULT_OPTS);
        $curl->setOpt(CURLOPT_RANGE, $range);

        $handler = tmpfile();
        $curl->setOpt(CURLOPT_FILE, $handler);
        $this->sliceMap[$range] = $handler;

        return $curl;
    }

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
     * Get the value of sliceCount
     */
    public function getSliceCount()
    {
        return $this->sliceCount;
    }

    /**
     * Set the value of sliceCount
     *
     * @return  self
     */
    public function setSliceCount($sliceCount)
    {
        $this->sliceCount = $sliceCount;

        return $this;
    }
}
