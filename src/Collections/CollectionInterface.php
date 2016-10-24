<?php
namespace jdeathe\PhpHelloWorld\Collections;

interface CollectionInterface
{
    public function get($key, $default = null);

    public function getAll();
}