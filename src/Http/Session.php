<?php
namespace jdeathe\PhpHelloWorld\Http;

/**
 * PHP session helper
 */
class Session
{
    /**
     * The session bucket key.
     *
     * @var string
     */
    protected $bucketKey;

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
        $this->bucketKey = null;
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
            $this->setSession(
                $session
            );
        }
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
            if (
                $this->getBucketKey() === ''
            ) {
                unset(
                    $this->session[$key]
                );
            }
            else {
                unset(
                    $this->session[$this->getBucketKey()][$key]
                );
            }
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

        $keys = null;
        if (
            is_array(
                $this->session
            )
        ) {
            $keys = array_keys(
                $this->session
            );
        }

        if (
            ! empty(
                $keys
            )
        ) {
            foreach ($keys as $key) {
                unset(
                    $this->session[$key]
                );
            }
        }

        return session_destroy();
    }

    /**
     * Get a session attribute by name
     *
     * Does not check if the session attribute exists; check using has.
     *
     * @param string $key The session attribute key name
     * @return mixed The session attribute value if it exits.
     */
    public function get($key = null)
    {
        try {
            if (
                ! is_string(
                    $key
                ) ||
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
                return null;
            }

            if (
                $this->getBucketKey() !== ''
            ) {
                return $this->session[$this->getBucketKey()][$key];
            }

            return $this->session[$key];
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }
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
     * Get a session bucket's data
     *
     * Should be called after the setBucketKey method.
     *
     * @return array|null The session bucket data if it exits.
     */
    public function getBucketData()
    {
        if (
            $this->getBucketKey() === ''
        ) {
            return null;
        }

        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        if (
            ! array_key_exists(
                $this->getBucketKey(),
                $this->session
            )
        ) {
            $this->setBucketData();
        }

        return $this->session[$this->getBucketKey()];
    }

    /**
     * Get the current session bucket's key
     *
     * @return string The session bucket's key
     */
    public function getBucketKey()
    {
        if (
            $this->bucketKey === null
        ) {
            $this->bucketKey = '';
        }

        return $this->bucketKey;
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
            $this->session =& $_SESSION;
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
                ! is_string(
                    $key
                ) ||
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
                $this->getBucketKey() === '' &&
                is_array(
                    $this->session
                ) &&
                array_key_exists(
                    $key,
                    $this->session
                )
            ) {
                return true;
            }
            elseif (
                $this->getBucketKey() !== '' &&
                is_array(
                    $this->session
                ) &&
                array_key_exists(
                    $this->getBucketKey(),
                    $this->session
                ) &&
                is_array(
                    $this->session[$this->getBucketKey()]
                ) &&
                array_key_exists(
                    $key,
                    $this->session[$this->getBucketKey()]
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
     * Check if the session has been initialised
     *
     * @return boolean
     */
    public function isStarted()
    {
        if (
            $this->session === null
        ) {
            return false;
        }

        if (
            $this->id === null ||
            $this->id === '' ||
            session_id() === ''
        ) {
            return false;
        }

        return true;
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

        if (
            session_write_close()
        ) {
            return true;
        }

        return false;
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
                ! is_string(
                    $key
                ) ||
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

            if (
                $this->getBucketKey() === ''
            ) {
                $this->session[$key] = $value;
            }
            else {
                $this->getBucketData();
                $this->session[$this->getBucketKey()][$key] = $value;
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
     * Set/initialise the session bucket's data
     *
     * Must be called after the setBucketKey method.
     *
     * @param array $data The bucket's data
     * @return Session|\InvalidArgumentException
     */
    public function setBucketData(array $data = array())
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
                $this->getBucketKey() === ''
            ) {
                return $this;
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            $this->session[$this->getBucketKey()] = $data;
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
     * This should be called before the setBucketData method.
     *
     * @param string $key The session bucket's key
     * @return Session|\InvalidArgumentException
     */
    public function setBucketKey($key = '')
    {
        try {
            if (
                ! is_string(
                    $key
                ) ||
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
                throw new \InvalidArgumentException(
                    'Invalid session bucket key.'
                );
            }

            $this->bucketKey = $key;
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
    public function setSession(array $session = null)
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

            if (
                ! $this->isStarted()
            ) {
                $this->session =& $session;
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
     * @return boolean
     */
    public function start()
    {
        $this->setName(
            $this->getName()
        );

        if (
            $this->isStarted()
        ) {
            return true;
        }

        if (
            ! session_start()
        ) {
            return false;
        }

        if (
            $this->getId() === ''
        ) {
            $this->setId(
                session_id()
            );
        }

        if (
            $this->session === null
        ) {
            $this->session =& $_SESSION;
        }

        $this->setWrite(
            true
        );

        return true;
    }
}