<?php

use CurlAxel\Exceptions\CurlException;
use CurlAxel\Handlers\Chunk\Memory;
use CurlAxel\Handlers\Chunk\TempFile;
use PHPUnit\Framework\TestCase;

final class CurlAxelTest extends TestCase
{
    const ONE_MEGA_FILE_URL = 'http://ovh.net/files/1Mio.dat';
    const ONE_MEGA_FILE_MD5 = '6cb91af4ed4c60c11613b75cd1fc6116';

    public function testDownload()
    {
        $tmp = 'download';
        $ca = new \CurlAxel\CurlAxel();
        $ca->setUrl(self::ONE_MEGA_FILE_URL)
            ->setOutput($tmp)
            ->setChunkHandler(new TempFile());

        $ca->download();

        $this->assertEquals(self::ONE_MEGA_FILE_MD5, md5_file($tmp));
    }

    public function testDownloadInMemory()
    {
        $tmp = 'download';
        $ca = new \CurlAxel\CurlAxel();
        $ca->setUrl(self::ONE_MEGA_FILE_URL)
            ->setOutput($tmp)
            ->setChunkHandler(new Memory());

        $ca->download();

        $this->assertEquals(self::ONE_MEGA_FILE_MD5, md5_file($tmp));
    }

    public function testGetFileSize()
    {
        $ca = new \CurlAxel\CurlAxel();
        $ca->setUrl(self::ONE_MEGA_FILE_URL);
        $size = $this->invokeMethod($ca, 'getFileSize', []);

        $this->assertEquals(1048576, $size);
    }

    public function testGetFileSizeException()
    {
        $this->expectException(CurlException::class);

        $ca = new \CurlAxel\CurlAxel();
        $ca->setUrl(self::ONE_MEGA_FILE_URL . 'x');
        $this->invokeMethod($ca, 'getFileSize', []);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}