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
    protected $session;

    /**
     * Create a new HTTP session
     *
     * @param array $sessionParams The session environment variables
     */
    public function __construct(array $session = null)
    {
            $this->$session = isset($session)
                ? $session
                : null
            ;
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
        if ($this->$session === null) {
            return false;
        }

        return true;
    }

    public function setName($name = '')
    {
        try {
            if (
                ! is_string($name) ||
                trim($name) === '' ||
                preg_match('/^[0-9]+$/', trim($name))
            ) {
                throw new InvalidArgumentException('Invalid session name.');
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
    }

    public function start()
    {
        if (isStarted()) {
            return true;
        }

        return session_start();
    }

    public function getName()
    {
        if ($this->name === null) {
            return session_name();
        }

        return $this->name;
    }
}