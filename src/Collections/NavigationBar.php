<?php
namespace jdeathe\PhpHelloWorld\Collections;

require_once 'Collections/CollectionInterface.php';
require_once 'Collections/CollectionItemsInterface.php';

class NavigationBar implements CollectionItemsInterface {

    /**
     * @var array|false
     */
    protected $collection = null;

    /**
     * @var array|false
     */
    protected $items = array();

    /**
     * @param CollectionsInterface $collection
     */
    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Create a new NavigationBar instance.
     * @param CollectionsInterface $collection
     */
    public static function create(CollectionInterface $collection)
    {
        return new self($collection);
    }

    /**
     * @param string $key Setting key.
     * @param string|null $default Value to return if $key is not found.
     */
    public function get($key, $default = null)
    {
        if (empty($this->items)) {
            $this->parse();
        }

        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        } else {
            return $default;
        }
    }

    /**
     * Return the items array.
     * @return array
     */
    public function getAll()
    {
        if (empty($this->items)) {
            $this->parse();
        }

        return $this->items;
    }

    /**
     * Parse the collection items.
     * @return void
     */
    private function parse()
    {
        if (!$this->collection instanceof CollectionInterface) {
            return null;
        }

        $collectionData = $this->collection->getAll();

        if (empty($collectionData)) {
            return null;
        }

        foreach ($collectionData as $collectionItem) {
            if (!is_object($collectionItem) ||
                !isset($collectionItem->type)
            ) {
                continue;
            }

            if (isset($collectionItem->attributes) && 
                !empty($collectionItem->attributes)
            ) {
                if (isset($collectionItem->attributes->condition)) {
                    if (is_string($collectionItem->attributes->condition) &&
                        $collectionItem->attributes->condition === 'false'
                    ) {
                        continue;
                    } elseif (is_object($collectionItem->attributes->condition) &&
                        property_exists($collectionItem->attributes->condition, 'function') &&
                        is_array($collectionItem->attributes->condition->function) &&
                        property_exists($collectionItem->attributes->condition, 'parameter') &&
                        is_array($collectionItem->attributes->condition->parameter) &&
                        forward_static_call_array(
                            $collectionItem->attributes->condition->function,
                            $collectionItem->attributes->condition->parameter
                        ) === false
                    ) {
                        continue;
                    }
                }

                $item = new \stdClass;
                $item->type = $collectionItem->type;
                $item->id = $collectionItem->id;

                foreach ($collectionItem->attributes as $key => $value) {
                    // Remove the condition attriubute from item
                    if ($key === 'condition') {
                        continue;
                    }
                    $item->{$key} = $value;
                }
            }

            $this->items[$item->id] = $item;
        }
    }

    /**
     * Determine if the URL should be included in the NavigationBar items.
     *
     * @return boolean
     */
    private static function visible($url) {
        switch ($url) {
            case '/phpinfo.php':
            case '/sessioninfo.php':
            case '/cdn_assets.php':
                if (realpath(
                        $_SERVER['DOCUMENT_ROOT'] . $url
                )) {
                    return true;
                } else {
                    return false;
                }
                break;
            case '/apcinfo.php':
                if (extension_loaded('apc') &&
                    realpath(
                        $_SERVER['DOCUMENT_ROOT'] . '/_apc.php'
                    )
                ) {
                    return true;
                } else {
                    return false;
                }
                break;
            case '/server-status':
                if (array_key_exists('SERVER_SOFTWARE', $_SERVER) &&
                    strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === 0 &&
                    array_key_exists('REMOTE_ADDR', $_SERVER) &&
                    $_SERVER['REMOTE_ADDR'] === '127.0.0.1'
                ) {
                    return true;
                } else {
                    return false;
                }
                break;
            case '/status?full':
                if (PHP_SAPI === 'fpm-fcgi' &&
                    array_key_exists('REMOTE_ADDR', $_SERVER) &&
                    $_SERVER['REMOTE_ADDR'] === '127.0.0.1'
                ) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
    }

    /**
     * Determine if the NavigationBar item is active.
     *
     * @return boolean
     */
    public function isActiveItem($item) {
        if (
            is_object($item)
            && property_exists($item, 'url')
            && $item->url == $_SERVER['REQUEST_URI']
        ) {
            return true;
        }

        return false;
    }

     /**
     * Determine if the current page is the homepage.
     *
     * @param string $defaultPath The default path to the site's homepage.
     * @param string $index The PHP directory index file.
     * @return boolean
     */
    public function isHomePage($defaultPath = '/', $index = 'index.php') {
        $url = (string) $defaultPath;
        $homeUrl = $url . $index;

        if (
            preg_match(
                "~^(?:$url|$homeUrl(?:\?.*)?)?$~",
                $_SERVER['REQUEST_URI']
            )
        ) {
            return true;
        }

        return false;
    }
}