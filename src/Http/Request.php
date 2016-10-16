<?php
namespace jdeathe\PhpHelloWorld\Http;

class Request
{
    const SECURE_SERVER_PORT = '8443';
    const SECURE_REQUEST_HEADER = 'X-Forwarded-Proto';
    const SECURE_REQUEST_HEADER_VALUE = 'https';

    /**
     * The server parameters; typically from the $_SERVER superglobal.
     *
     * @var array
     */
    protected $serverParams;

    /**
     * The request headers.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Create a new HTTP server request
     *
     * @param array $serverParams The server environment variables
     */
    public function __construct(array $serverParams)
    {
        $this->serverParams = $serverParams;
    }

    /**
     * Append HTTP header values.
     *
     * @param string $name The request header name
     * @param string|string[] $value The request header value(s)
     */
    private function addHeader($name, $value)
    {
        $headers = array_change_key_case(
            $this->headers
        );
        $values = array();

        // Retrive existing header values if any exist.
        if (array_key_exists(
            strtolower(
                $name
            ),
            $headers
        )) {
            $values = $headers[strtolower(
                $name
            )];
        }

        // Append the new value to any existing values.
        $this->setHeader(
            $name,
            array_merge(
                $values,
                is_array($value)
                    ? array_values($value)
                    : array($value)
            )
        );

        unset(
            $headers,
            $values
        );
    }

    /**
     * Get request header values by name.
     *
     * @param string $name Request header name (case-insensitive).
     * @return string[] An array of request header values.
     */
    public function getHeader($name)
    {
        $value = array();

        if ($this->hasHeader($name)) {
            $headers = array_change_key_case(
                $this->getHeaders()
            );

            $value = $headers[strtolower(
                $name
            )];

            unset(
                $headers
            );
        }

        return $value;
    }

    /**
     * Get all request headers.
     *
     * @return string[][] An associative array of request headers. Each key is a
     *    header name and each value is an array of string header values.
     */
    public function getHeaders()
    {
        if ( ! empty($this->headers)) {
            return $this->headers;
        }

        foreach ($this->getServerParams() as $name => $value) {

            if ( ! preg_match(
                '~^HTTP_~i',
                $name
            )) {
                continue;
            }

            $this->addHeader(
                $name,
                $value
            );
        }

        return $this->headers;
    }

    /**
     * Retrieve server parameters.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Determine if a request header exists by name.
     *
     * @param string $name Request header name (case-insensitive).
     * @return bool Return true if header name exists.
     */
    public function hasHeader($name)
    {
        if (array_key_exists(
            strtolower(
                $name
            ),
            array_change_key_case(
                $this->getHeaders()
            )
        )) {
            return true;
        }

        return false;
    }

    /**
     * Check if request is considered secure.
     *
     * @return boolean
     */
    public function isSecure()
    {
        // Check if protocol is HTTPS
        if (array_key_exists(
                'HTTPS',
                $this->serverParams
            ) &&
            ! empty(
                $this->serverParams['HTTPS']
            ) &&
            strtolower(
                $this->serverParams['HTTPS']
            ) !== 'off'
        ) {
            return true;
        }

        return $this->isTlsTerminated();
    }

    /**
     * Check if request was SSL/TLS terminated upstream.
     *
     * For valid SSL/TLS termination the upstream service must set the 
     * X-Forwarded-Proto header to 'https' and make requests via port 8443.
     *
     * @return boolean
     */
    public function isTlsTerminated()
    {
        // Limit SSL/TLS terminated connections to a pre-defined port.
        // This allows for a Firewall ACL to be applied.
        if ( ! array_key_exists(
                'SERVER_PORT',
                $this->serverParams
            ) ||
            $this->serverParams['SERVER_PORT'] !== self::SECURE_SERVER_PORT
        ) {
            return false;
        }

        $headerForwardedProto = $this->getHeader(
            self::SECURE_REQUEST_HEADER
        );

        if ( ! empty(
            $headerForwardedProto
            ) &&
            $headerForwardedProto[0] === self::SECURE_REQUEST_HEADER_VALUE
        ) {
            unset(
                $headerForwardedProto
            );

            return true;
        }

        unset(
            $headerForwardedProto
        );

        return false;
    }

    /**
     * Set (and replace) HTTP header values.
     *
     * @param string $name The request header name
     * @param string|string[] $value The header value(s)
     */
    private function setHeader($name, $value)
    {
        $this->headers[sprintf(
            '%s',
            preg_replace(
                array(
                    '~^HTTP_~i',
                    '~_~',
                ),
                array(
                    '',
                    '-'
                ),
                $name
            )
        )] = is_array($value)
            ? array_values($value)
            : array($value)
        ;
    }
}