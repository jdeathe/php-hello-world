<?php
namespace jdeathe\PhpHelloWorld\Http;

class Session
{
    /**
     * The session name.
     *
     * @var string
     */
    protected $name = null;

    /**
     * The session attributes.
     *
     * @var array
     */
    protected $session = null;

    /**
     * Create a new HTTP session
     *
     * @param array $session The session environment variables
     */
    public function __construct(array $session = null)
    {
        if (
            isset($session)
        ) {
            $this->session =& $session;
        }
    }

    public function destroy()
    {
        if (ini_get('session.use_cookies')) {
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

        session_destroy();
    }

    public function isStarted()
    {
        if ($this->session === null) {
            return false;
        }

        return true;
    }

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

    public function getName()
    {
        if ($this->name === null) {
            return session_name();
        }

        return $this->name;
    }

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

    public function set($key = null, $value = null)
    {
        try {
            // Ref: http://php.net/manual/en/session.configuration.php#ini.session.serialize-handler
            // Numeric and special characters are not supported in keys if using
            // the default session.serialize_handler. PHP >= 5.5.4 introduceds
            // the option of using php_serialize.
            if (
                ! is_string($key) &&
                strpos($key, '|') !== false &&
                strpos($key, '!') !== false
            ) {
                throw new \InvalidArgumentException('Invalid session variable name.');
            }

            if (
                ! $this->isStarted()
            ) {
                $this->start();
            }

            $this->session[$key] = $value;
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            exit;
        }

        return $this;
    }

    public function setName($name = '')
    {
        try {
            if (
                ! is_string($name) ||
                trim($name) === '' ||
                preg_match('/^[0-9]+$/', trim($name))
            ) {
                throw new \InvalidArgumentException('Invalid session name.');
            }

            if ( ! $this->isStarted()) {
                $this->name = trim($name);
            }
            else {
                $this->name = session_name();
            }

            session_name($this->name);
        }
        catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            exit;
        }

        return $this;
    }

    public function close()
    {
        if (
            ! $this->isStarted()
        ) {
            $this->start();
        }

        return session_write_close();
    }

    public function start()
    {
        if ($this->isStarted()) {
            return true;
        }

        $this->setName(
            $this->getName()
        );

        if (
            session_start()
        ) {
            if (
                $this->session === null
            ) {
                $this->session =& $_SESSION;
            }

            return true;
        }

        return false;
    }
}