<?php
/**
 * Created by PhpStorm.
 * User: jacer
 * Date: 09/06/18
 * Time: 22:56
 */

namespace CurlAxel\Exceptions;

use Curl\Curl;

class CurlException extends \Exception
{
    public function __construct(Curl $curl)
    {
        parent::__construct($this->format($curl));
    }

    public function format(Curl $curl)
    {
        return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
    }
}
