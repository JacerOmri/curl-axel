<?php

namespace CurlAxel;

use CurlAxel\Exceptions\InvalidChunkHandlerException;
use CurlAxel\Handlers\Chunk\ChunkHandler;
use ReflectionClass;

class Factory
{
    /**
     * @param $type
     * @param array $args
     * @return CurlAxel
     * @throws InvalidChunkHandlerException
     */

    const baseChunkHandlerNS = '\\CurlAxel\\Handlers\\Chunk';

    public static function create($type = 'TempFile', $args = [])
    {
        $ca = new CurlAxel();

        if ($type instanceof ChunkHandler) {
            $ca->setChunkHandler($type);
            return $ca;
        } elseif (is_string($type)) {
            if(!class_exists($type)) {
                $type = implode('\\', [
                    self::baseChunkHandlerNS,
                    $type
                ]);
            }
            try {
                $r = new ReflectionClass($type);
            } catch (\ReflectionException $e) {
                throw new InvalidChunkHandlerException($type);
            }
            /** @var ChunkHandler $chunkHandler */
            $chunkHandler = $r->newInstanceArgs($args);
            $ca->setChunkHandler($chunkHandler);
            return $ca;
        }

        throw new InvalidChunkHandlerException($type);
    }
}