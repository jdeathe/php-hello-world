<?php
namespace jdeathe\PhpHelloWorld\Collections;

require_once 'Collections/CollectionInterface.php';

class JsonFileCollection implements CollectionInterface {

    /**
     * @var array|false
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @param string $path Path to data file (absolute or relative to include_path).
     */
    public function __construct($path)
    {
        if (!is_string($path) || preg_match('~^[^\0]+$~', $path) !== 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid file path provided: %s',
                    $path
                )
            );
        }

        $this->path = stream_resolve_include_path(
            $path
        );
    }

    /**
     * @param string $key Setting key.
     * @param string|null $default Value to return if $key is not found.
     */
    public function get($key, $default = null)
    {
        if (empty($this->data)) {
            $this->parse();
        }

        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            return $default;
        }
    }

    /**
     * Return the data array.
     * @return array
     */
    public function getAll()
    {
        if (empty($this->data)) {
            $this->parse();
        }

        return $this->data;
    }

    /**
     * Parse the data file.
     * @return void
     */
    private function parse()
    {
        if ((string) $this->path === '') {
            return null;
        }

        $json = file_get_contents(
            $this->path,
            false
        );

        if ( ! is_string($json)) {
            return null;
        }

        $obj = json_decode(
            $json,
            false
        );

        if ($obj === null || ! is_object($obj)) {
            return null;
        } elseif ( ! $obj->{'data'}) {
            return null;
        }

        $this->data = $obj->{'data'};
    }
}