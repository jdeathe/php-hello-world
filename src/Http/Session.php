<?php
namespace jdeathe\PhpHelloWorld\Http;

/**
 * PHP session helper
 */
class Session
{
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
     * @param array $session The session variables
     */
    public function __construct(array $session = null)
    {
        $this->id = null;
        $this->name = null;
        $this->session = null;
        $this->write = false;

        if (
            isset($session)
        ) {
            $this->setSession(
                $session
            );
        }
    }

    /**
     * Write session data and end session
     *
     * Can help free up write lock if enabled for the session store
     *
     * @return boolean
     */
    public function close()
    {
        if (
            session_write_close()
        ) {
            $this->write = false;

            return true;
        }

        return false;
    }

    /**
     * Destroys all data registered to a session
     *
     * @return boolean
     */
    public function destroy()
    {
        if (
            ini_get('session.use_cookies')
        ) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                0,
                $params['path'],
                $params['domain'],
                $params['secure'],
                isset($params['httponly'])
            );
        }

        return session_destroy();
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
     * Get a session variable by name
     *
     * Does not check if the session variable exists; use has
     *
     * @param string $key The session variable key name
     * @return mixed The session variable value if it exits.
     */
    public function get($key = null)
    {
        try {
            if (
                ! is_string($key) &&
                ! is_int($key)
            ) {
                throw new \InvalidArgumentException('Invalid session variable name.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                is_array($this->session) &&
                array_key_exists($key, $this->session)
            ) {
                return $this->session[$key];
            }

            return null;
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
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
     * @return array The $_SESSION associative array
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
     * Get a session variable by name
     *
     * @param string $key The session variable key name
     */
    public function has($key = null)
    {
        try {
            if (
                ! is_string($key) &&
                ! is_int($key)
            ) {
                throw new \InvalidArgumentException('Invalid session variable name.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                is_array($this->session) &&
                array_key_exists($key, $this->session)
            ) {
                return true;
            }

            return false;
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Re-open a closed session for writing
     *
     * @return boolean
     */
    public function open()
    {
        $this->setName(
            $this->getName()
        );

        if (
            $this->write === true
        ) {
            return true;
        }

        if (
            session_start()
        ) {
            $this->setSession(
                $this->getSession()
            );

            $this->write = true;

            return true;
        }

        return false;
    }

    /**
     * Set a session variable by name
     *
     * @param string $key The session variable key name. Must not contain numeric, | or ! characters.
     * @param string $value The session variable value
     * @return Session|\InvalidArgumentException
     */
    public function set($key = null, $value = null)
    {
        try {
            if (
                is_numeric($key) ||
                ! is_string($key) ||
                strpos($key, '|') !== false ||
                strpos($key, '!') !== false
            ) {
                throw new \InvalidArgumentException('Invalid session variable name.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            if (
                $this->write !== true
            ) {
                $this->open();
            }

            $this->session[$key] = $value;
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
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
                ! is_string($id) ||
                trim($id) === '' ||
                ! preg_match(
                    '/^[a-zA-Z0-9,-]+$/',
                    trim($id)
                )
            ) {
                throw new \InvalidArgumentException('Invalid session id.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->id = trim($id);

                // Set the session id
                session_id($this->id);
            }
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session name
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
                ! is_string($name) ||
                trim($name) === '' ||
                is_numeric(trim($name))
            ) {
                throw new \InvalidArgumentException('Invalid session name.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->name = trim($name);

                // Set the session name
                session_name($this->name);
            }
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            exit;
        }

        return $this;
    }

    /**
     * Set the session data
     *
     * @return array The $_SESSION associative array
     * @return Session|\InvalidArgumentException
     */
    public function setSession(array $session = null)
    {
        try {
            if (
                ! is_array($session)
            ) {
                throw new \InvalidArgumentException('Invalid session array.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->session =& $session;
            }
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
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
            session_start()
        ) {
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

            $this->write = true;

            return true;
        }

        return false;
    }
}