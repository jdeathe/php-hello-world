<?php
namespace jdeathe\PhpHelloWorld\Http;

require_once 'Config/Session.php';

/**
 * PHP session helper
 */
class Session
{
    const BUCKET_DEFAULT = 'data';
    const BUCKET_FLASH_WRITE = 'flash_write';
    const BUCKET_FLASH_READ = 'flash_read';
    const BUCKET_METADATA = 'metadata';
    const METADATA_EXPIRES = 'expires';
    const METADATA_CREATED = 'created';
    const METADATA_ID = 'id';

    /**
     * The session bucket key stack.
     *
     * @var array
     */
    protected $bucketStack;

    /**
     * Has session flash read data been loaded.
     *
     * @var boolean
     */
    protected static $flashLoaded = false;

    /**
     * The session id.
     *
     * @var string
     */
    protected $id;

    /**
     * The session name.
     *
     * @var string
     */
    protected $name;

    /**
     * The session attributes.
     *
     * @var array
     */
    protected $session;

    /**
     * Session write access.
     *
     * @var boolean
     */
    protected $write;

    /**
     * Create a new HTTP session
     *
     * @param array $session The session attributes
     */
    public function __construct(array $session = null)
    {
        $this->bucketStack = array();
        $this->id = null;
        $this->name = null;
        $this->session = null;

        $this->setWrite(
            false
        );

        if (
            isset(
                $session
            )
        ) {
            $this
                ->setSession(
                    $session
                )
                ->loadFlash()
            ;
        }
    }

    /**
     * Create a new HTTP Session instance.
     *
     * @param array $session The session attributes
     * @return Session
     */
    public static function create(array $session = null)
    {
        return new self(
            $session
        );
    }

    /**
     * Free all session buckets
     *
     * @return boolean
     */
    public function clear()
    {
        foreach (array_keys($this->getSession()) as $key) {
            unset(
                $this->session[$key]
            );
        }

        return empty(
            $this->session
        );
    }

    /**
     * Free session bucket
     *
     * @return Session
     */
    public function clearBucket()
    {
        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        if (
            array_key_exists(
                $this->getBucket(),
                $this->getSession()
            )
        ) {
            unset(
                $this->session[$this->getBucket()]
            );
        }

        return $this;
    }

    /**
     * Delete a session attribute by name
     *
     * @param string $key The session attribute key name
     */
    public function delete($key = null)
    {
        if (
            $this->has(
                $key
            )
        ) {
            unset(
                $this->session[$this->getBucket()][$key]
            );
        }

        return $this;
    }

    /**
     * Destroys all data registered to a session
     *
     * @return boolean
     */
    public function destroy()
    {
        if (
            ini_get(
                'session.use_cookies'
            )
        ) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                0,
                $params['path'],
                $params['domain'],
                $params['secure'],
                isset(
                    $params['httponly']
                )
            );
        }

        return
            $this->clear() &&
            session_destroy()
        ;
    }

    /**
     * Get a session attribute by name
     *
     * @param string $key The session attribute key name
     * @param mixed $default Default value
     * @return mixed The session attribute value if it exits.
     */
    public function get($key = null, $default = null)
    {
        try {
            if (
                ! $this->isValidKey(
                    $key
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session attribute name.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                ! $this->has(
                    $key
                )
            ) {
                return $default;
            }

            $all = $this->getAll();

            return $all[$key];
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
    }

    /**
     * Get all data for the session bucket
     *
     * @return array The session bucket data.
     */
    public function getAll()
    {
        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        if (
            ! array_key_exists(
                $this->getBucket(),
                $this->getSession()
            )
        ) {
            $this->loadBucket();
        }

        return $this->session[$this->getBucket()];
    }

    /**
     * Get all session buckets
     *
     * @return array The session buckets.
     */
    public function getAllBuckets()
    {
        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        if (
            null === $this->getSession()
        ) {
            return array();
        }

        $session = $this->getSession();

        return array_filter(
            array_keys(
                $session
            ),
            function($key) use ($session) {
                return is_array(
                    $session[$key]
                );
            }
        );
    }

    /**
     * Get the current session bucket's key
     *
     * @return string The session bucket's key
     */
    public function getBucket()
    {
        return ! empty(
                $this->bucketStack
            )
            ? end(
                $this->bucketStack
            )
            : self::BUCKET_DEFAULT
        ;
    }

    /**
     * Get the current session id
     *
     * @return string The session name
     */
    public function getId()
    {
        if (
            $this->id === null
        ) {
            $this->id = session_id();
        }

        return $this->id;
    }

    /**
     * Get the current session name
     *
     * @return string The session name
     */
    public function getName()
    {
        if (
            $this->name === null
        ) {
            $this->name = session_name();
        }

        return $this->name;
    }

    /**
     * Get the session data
     *
     * Defaults to the $_SESSION global if not explicitly set.
     *
     * @return array The session data
     */
    public function getSession()
    {
        if (
            $this->session === null
        ) {
            $this->session = &$_SESSION;
        }

        return $this->session;
    }

    /**
     * Get the current write status
     *
     * @return boolean The session write status
     */
    public function getWrite()
    {
        return $this->write;
    }

    /**
     * Get a session attribute by name
     *
     * @param string $key The session attribute key name
     */
    public function has($key = null)
    {
        try {
            if (
                ! $this->isValidKey(
                    $key
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session attribute name.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                array_key_exists(
                    $this->getBucket(),
                    $this->getSession()
                ) &&
                array_key_exists(
                    $key,
                    $this->getAll()
                )
            ) {
                return true;
            }

            return false;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
    }

    /**
     * Check if any session buckets have been loaded
     *
     * @return boolean
     */
    public function hasBuckets()
    {
        return
            count(
                $this->getAllBuckets()
            ) !== 0
        ;
    }

    /**
     * Invalidate the current session.
     *
     * Delete session data and migrate to a new session id.
     *
     * @return boolean
     */
    public function invalidate()
    {
        return
            $this->clear() &&
            $this->migrate(
                true
            )
        ;
    }

    /**
     * Check if the session bucket is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return
            count(
                $this->getAll()
            ) === 0
        ;
    }

    /**
     * Check if the session has expired
     *
     * Expired session should be invalidated; delete session data and migrate.
     *
     * @return boolean
     */
    public function isExpired()
    {
        $this->setBucket(
            self::BUCKET_METADATA
        );

        if (
            $this->has(
                self::METADATA_EXPIRES
            ) &&
            0 != $this->get(
                self::METADATA_EXPIRES
            ) &&
            $this->has(
                self::METADATA_ID
            )
        ) {
            if (
                $this->getId() == $this->get(
                    self::METADATA_ID
                ) &&
                time() >= $this->get(
                    self::METADATA_EXPIRES
                )
            ) {
                $this->restoreBucket();

                return true;
            }
        }

        $this->restoreBucket();

        return false;
    }

    /**
     * Check if the session has been initialised
     *
     * @return boolean
     */
    public function isStarted()
    {
        if (
            $this->session === null ||
            $this->getId() === '' ||
            session_id() === ''
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if a key is valid for session usage
     *
     * @param string $key The key name
     * @return boolean
     */
    private function isValidKey($key = null)
    {
        if (
            ! is_string(
                $key
            ) ||
            $key === '' ||
            ctype_digit(
                $key
            ) ||
            strpos(
                $key,
                '|'
            ) !== false ||
            strpos(
                $key,
                '!'
            ) !== false
        ) {
            return false;
        }

        return true;
    }


    /**
     * Set/initialise the session bucket's data
     *
     * Must be called after the setBucket method.
     *
     * @param array $data The bucket's data
     * @return Session|\InvalidArgumentException
     */
    public function loadBucket(array $data = array())
    {
        try {
            if (
                ! is_array(
                    $data
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid bucket data array.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            $this->session[$this->getBucket()] = $data;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Load flash from the session bucket.
     *
     * @return Session
     */
    private function loadFlash()
    {
        if (
            self::$flashLoaded === true
        ) {
            return $this;
        }

        $flashWrite = $this
            ->setBucket(
                self::BUCKET_FLASH_WRITE
            )
            ->getAll()
        ;

        $this
            ->clearBucket()
            ->setBucket(
                self::BUCKET_FLASH_READ
            )
            ->loadBucket(
                $flashWrite
            )
            ->restoreBucket()
            ->restoreBucket()
        ;

        self::$flashLoaded = true;

        return $this;
    }

    /**
     * Migrate the current session.
     *
     * Migrate session attributes to a new session id.
     *
     * @param boolean @delete Delete the old session
     * @param integer @ttl Time to live in seconds. Sets an expiry time for the
     *                     session (if not deleting it) before migrating.
     * @return boolean|\InvalidArgumentException
     */
    public function migrate($delete = false, $ttl = 60)
    {
        try {
            if (
                ! is_bool(
                    $delete
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session delete.'
                );
            }

            if (
                ! ctype_digit(
                    (string) $ttl
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session ttl.'
                );
            }

            if (
                ! $this->getWrite() ||
                ! $this->isStarted()
            ) {
                return false;
            }

            if (
                $delete === false
            ) {
                $this
                    ->setMetadataExpires(
                        $ttl,
                        true
                    )
                    ->restart()
                ;
            }
            else {
                $this->setMetadataCreated(
                    true
                );
            }

            return session_regenerate_id(
                $delete
            );
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
    }

    /**
     * Get a session attribute by name and remove it
     *
     * @param string $key The session attribute key name
     * @param mixed $default Default value
     * @return mixed The session attribute value if it exits.
     */
    public function pull($key = null, $default = null)
    {
        try {
            if (
                ! $this->isValidKey(
                    $key
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session attribute name.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                ! $this->has(
                    $key
                )
            ) {
                return $default;
            }

            $all = $this->getAll();
            $value = $all[$key];

            $this->delete(
                $key
            );

            return $value;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
    }

    /**
     * Commit session data and start back up
     *
     * @return Session
     */
    public function restart()
    {
        $this->save();
        $this->id = null;
        $this->start(
            true
        );

        return $this;
    }

    /**
     * Restore the previous session bucket
     *
     * Sets the bucket by poping the last key off the stack that was added
     * using the setBucket method or the default value.
     *
     * @return Session
     */
    public function restoreBucket()
    {
        if (
            ! empty(
                $this->bucketStack
            )
        ) {
            array_pop(
                $this->bucketStack
            );
        }

        return $this;
    }

    /**
     * Write session data and end session
     *
     * Can help free up write lock if enabled for the session store
     *
     * @return boolean
     */
    public function save()
    {
        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        $this->setWrite(
            false
        );

        return session_write_close();
    }

    /**
     * Set a session attribute by name
     *
     * The key must not be numeric or contain "|" or "!" characters.
     *
     * @param string $key The session attribute key name
     * @param string $value The session attribute value
     * @return Session|\InvalidArgumentException
     */
    public function set($key = null, $value = null)
    {
        try {
            if (
                ! $this->isValidKey(
                    $key
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session attribute name.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                $this->getWrite() === false
            ) {
                // Session data is read only.
                return $this;
            }

            // Initialise bucket
            if (
                ! array_key_exists(
                    $this->getBucket(),
                    $this->getSession()
                )
            ) {
                $this->loadBucket();
            }

            $this->session[$this->getBucket()][$key] = $value;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session bucket's key
     *
     * @param string $key The session bucket's key
     * @return Session|\InvalidArgumentException
     */
    public function setBucket($key = self::BUCKET_DEFAULT)
    {
        try {
            if (
                ! $this->isValidKey(
                    $key
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session bucket key.'
                );
            }

            $this->bucketStack[] = $key;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session id
     *
     * This should be called before the start method.
     *
     * @param string $id The session id
     * @return Session|\InvalidArgumentException
     */
    public function setId($id = '')
    {
        try {
            if (
                ! is_string(
                    $id
                ) ||
                ! preg_match(
                    '~^[a-zA-Z0-9,-]+$~',
                    $id
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session id.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->id = $id;

                // Set the session id
                session_id(
                    $this->id
                );
            }
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Sets the session created metadata
     *
     * @param boolean $overwrite Overwrite existing value if true.
     * @return Session|\InvalidArgumentException
     */
    private function setMetadataCreated($overwrite = false)
    {
        try {
            if (
                ! is_bool(
                    $overwrite
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid overwrite.'
                );
            }

            $this->setBucket(
                self::BUCKET_METADATA
            );

            if (
                $overwrite === true ||
                ! $this->has(
                    self::METADATA_CREATED
                )
            ) {
                $this
                    ->set(
                        self::METADATA_CREATED,
                        time()
                    )
                ;
            }

            $this->restoreBucket();
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Sets the session expiry metadata
     *
     * @param integer $ttl Time to live in seconds.
     * @param boolean $overwrite Overwrite existing value if true.
     * @return Session|\InvalidArgumentException
     */
    private function setMetadataExpires($ttl = 1440, $overwrite = false)
    {
        try {
            if (
                ! ctype_digit(
                    (string) $ttl
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid ttl.'
                );
            }

            if (
                ! is_bool(
                    $overwrite
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid overwrite.'
                );
            }

            $this->setBucket(
                self::BUCKET_METADATA
            );

            if (
                $overwrite === true ||
                ! $this->has(
                    self::METADATA_EXPIRES
                )
            ) {
                $this
                    ->set(
                        self::METADATA_EXPIRES,
                        strtotime(
                            sprintf(
                                '+%u seconds',
                                $ttl
                            )
                        )
                    )
                    ->set(
                        self::METADATA_ID,
                        $this->getId()
                    )
                ;
            }
            elseif (
                $this->has(
                    self::METADATA_EXPIRES
                ) &&
                $this->has(
                    self::METADATA_ID
                ) &&
                $this->getId() !== $this->get(
                    self::METADATA_ID
                )
            ) {
                // Reset expires on migrated session
                $this
                    ->set(
                        self::METADATA_EXPIRES,
                        strtotime(
                            sprintf(
                                '+%u seconds',
                                $ttl
                            )
                        )
                    )
                    ->set(
                        self::METADATA_ID,
                        $this->getId()
                    )
                ;
            }

            $this->restoreBucket();
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session name
     *
     * The session name references the name of the session, which is used in
     * cookies and URLs. It should contain only alphanumeric characters; it
     * should be short and descriptive.
     *
     * This should be called before the start method.
     *
     * @param string $name The session name
     * @return Session|\InvalidArgumentException
     */
    public function setName($name = '')
    {
        try {
            if (
                ! is_string(
                    $name
                ) ||
                ctype_digit(
                    $name
                ) ||
                ! preg_match(
                    '~^[a-zA-Z0-9_-]+$~',
                    $name
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session name.'
                );
            }

            if (
                ! $this->isStarted()
            ) {
                $this->name = $name;

                // Set the session name
                session_name(
                    $this->name
                );
            }
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session data
     *
     * @param array $session The session data
     * @return Session|\InvalidArgumentException
     */
    public function setSession(array &$session = null)
    {
        try {
            if (
                ! is_array(
                    $session
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session array.'
                );
            }

            $this->session = $session;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session write status
     *
     * @param boolean $write The session write status
     * @return Session|\InvalidArgumentException
     */
    private function setWrite($write = false)
    {
        try {
            if (
                ! is_bool(
                    $write
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid session write.'
                );
            }

            $this->write = $write;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Start session
     *
     * @param boolean $force Force session start.
     * @return boolean|\RuntimeException
     */
    public function start($force = false)
    {
        try {
            $this->setName(
                $this->getName()
            );

            if (
                ! $force &&
                $this->isStarted()
            ) {
                return true;
            }

            if (
                ! session_start()
            ) {
                throw new \RuntimeException(
                    'Session start failed.'
                );
            }

            if (
                $this->getId() === ''
            ) {
                return false;
            }

            $this->getSession();

            $this
                ->loadFlash()
                ->setWrite(
                    true
                )
                ->setMetadataCreated()
                ->setMetadataExpires(
                    (int) ini_get(
                        'session.gc_maxlifetime'
                    )
                )
            ;

            return true;
        }
        catch (
            RuntimeException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
    }
}