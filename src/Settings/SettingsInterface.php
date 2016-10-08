<?php
namespace jdeathe\PhpHelloWorld\Settings;

interface SettingsInterface
{
    public function get($key, $default = null);

    public function getAll();

    public function parse();
}