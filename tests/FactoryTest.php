<?php

use CurlAxel\Factory;
use CurlAxel\Handlers\Chunk\ChunkHandler;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{

    public function testCreateFile()
    {
        $ca = Factory::create('TempFile');

        $reflection = new \ReflectionClass(get_class($ca));
        $prop = $reflection->getProperty('chunkHandler');
        $prop->setAccessible(true);
        $class = get_class($prop->getValue($ca));

        $this->assertEquals($class, \CurlAxel\Handlers\Chunk\TempFile::class);
    }

    public function testCreateMemory()
    {
        $ca = Factory::create('Memory');

        $reflection = new \ReflectionClass(get_class($ca));
        $prop = $reflection->getProperty('chunkHandler');
        $prop->setAccessible(true);
        $class = get_class($prop->getValue($ca));

        $this->assertEquals($class, \CurlAxel\Handlers\Chunk\Memory::class);
    }

    public function testCreateDummyInstance()
    {
        $ca = Factory::create(new DummyChunkHandler());

        $reflection = new \ReflectionClass(get_class($ca));
        $prop = $reflection->getProperty('chunkHandler');
        $prop->setAccessible(true);
        $class = get_class($prop->getValue($ca));

        $this->assertEquals($class, DummyChunkHandler::class);
    }

    public function testCreateDummyClassName()
    {
        $ca = Factory::create(DummyChunkHandler::class);

        $reflection = new \ReflectionClass(get_class($ca));
        $prop = $reflection->getProperty('chunkHandler');
        $prop->setAccessible(true);
        $class = get_class($prop->getValue($ca));

        $this->assertEquals($class, DummyChunkHandler::class);
    }
}

class DummyChunkHandler extends ChunkHandler
{

    /**
     * @return string
     */
    protected function getStreamPath()
    {
        return 'dummy';
    }
}
