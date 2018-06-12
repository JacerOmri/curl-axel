<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use CurlAxel\Exceptions\CurlException;

final class CurlAxelTest extends TestCase
{
    const ONE_MEGA_FILE_URL = 'http://ovh.net/files/1Mio.dat';
    const ONE_MEGA_FILE_MD5 = '6cb91af4ed4c60c11613b75cd1fc6116';

    public function testDownload(): void
    {
        $tmp = 'download';
        $c = new \CurlAxel\CurlAxel(self::ONE_MEGA_FILE_URL, $tmp);
        $c->download();

        $this->assertEquals(self::ONE_MEGA_FILE_MD5, md5_file($tmp));
    }

    public function testGetFileSize(): void
    {
        $ca = new \CurlAxel\CurlAxel(self::ONE_MEGA_FILE_URL);
        $size = $this->invokeMethod($ca, 'getFileSize', []);

        $this->assertEquals(1048576, $size);
    }

    public function testGetFileSizeException(): void
    {
        $this->expectException(CurlException::class);

        $ca = new \CurlAxel\CurlAxel(self::ONE_MEGA_FILE_URL . 'x');
        $size = $this->invokeMethod($ca, 'getFileSize', []);
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