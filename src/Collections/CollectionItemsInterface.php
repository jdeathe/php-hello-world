<?php
namespace jdeathe\PhpHelloWorld\Collections;

interface CollectionItemsInterface
{
    public static function create(CollectionInterface $collection);

    public function get($key, $default = null);

    public function getAll();
}