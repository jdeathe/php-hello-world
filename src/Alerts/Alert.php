<?php
namespace jdeathe\PhpHelloWorld\Alerts;

require_once 'Alerts/AlertInterface.php';

class Alert implements AlertInterface
{
    /**
     * @var boolean
     */
    protected $dismissible;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param array $attributes The Alert attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->level = Alert::LEVEL_INFO;

        if (
            ! empty($attributes)
        ) {
            $this->populate(
                $attributes
            );
        }
    }

    /**
     * Gets the Alert message.
     *
     * @return string The Alert message
     */
    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * Create a new Alert instance.
     *
     * @param array $attributes The Alert attributes
     * @return Alert
     */
    public static function create(array $attributes = array())
    {
        return new self(
            $attributes
        );
    }

    /**
     * Extract the Alert attributes
     *
     * @return array The Alert attributes
     */
    public function extract()
    {
        $attributes = array();

        $getters = array(
            'dissmissible' => 'getDismissible',
            'message' => 'getMessage',
            'label' => 'getLabel',
            'level' => 'getLevel'
        );

        foreach ($getters as $attribute => $getter) {
            $attributes[$attribute] = $this->{$getters[$attribute]}();
        }

        return $attributes;
    }

    /**
     * Determine if the Alert is dissmissible.
     *
     * @return boolean True if Alert is dissmissible
     */
    public function getDismissible()
    {
        return (boolean) $this->dismissible;
    }

    /**
     * Gets the Alert message.
     *
     * @return string The Alert message
     */
    public function getMessage()
    {
        return (string) $this->message;
    }

    /**
     * Get the Alert type/contextual label.
     *
     * @return string The label
     */
    public function getLabel()
    {
        if (
            is_string(
                $this->label
            )
        ) {
            return $this->label;
        }

        switch (
            $this->getLevel()
        ) {
            case self::LEVEL_EMERG:
            case self::LEVEL_ALERT:
            case self::LEVEL_CRIT:
            case self::LEVEL_ERR:
                $this->label = self::LABEL_ERROR;
                break;
            case self::LEVEL_WARNING:
                $this->label = self::LABEL_WARNING;
                break;
            case self::LEVEL_NOTICE:
                $this->label = self::LABEL_NOTICE;
                break;
            case self::LEVEL_INFO:
            case self::LEVEL_DEBUG:
            default:
                $this->label = self::LABEL_INFO;
                break;
        }

        return $this->label;
    }

    /**
     * Get the Alert level.
     *
     * @return integer The Alert level
     */
    public function getLevel()
    {
        return (int) $this->level;
    }

    /**
     * Populates the Alert from attributes
     *
     * @param array $attributes The Alert attributes
     * @return Alert
     */
    public function populate(array $attributes = array())
    {
        if (
            empty(
                $attributes
            )
        ) {
            return $this;
        }

        $setters = array(
            'dissmissible' => 'setDismissible',
            'message' => 'setMessage',
            'label' => 'setLabel',
            'level' => 'setLevel'
        );

        foreach ($attributes as $attribute => $value) {
            if (
                ! array_key_exists(
                    $attribute,
                    $setters
                )
            ) {
                continue;
            }

            $this->{$setters[$attribute]}(
                $value
            );
        }

        return $this;
    }

    /**
     * Set if the Alert can be dismissed.
     *
     * @param boolean $dismissible Allow the Alert to be dismissed
     * @return Alert|InvalidArgumentException
     */
    public function setDismissible($dismissible)
    {
        try {
            if (
                ! is_bool($dismissible)
            ) {
                throw new \InvalidArgumentException(
                    'Invalid dismissible - boolean required.'
                );
            }

            $this->dismissible = $dismissible;
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
     * Set the Alert message.
     *
     * @param string $message Alert message
     * @return Alert|InvalidArgumentException
     */
    public function setMessage($message)
    {
        try {
            if (
                ! is_string($message) ||
                '' === $message
            ) {
                throw new \InvalidArgumentException(
                    'Invalid message - non-empty string required.'
                );
            }

            $this->message = $message;
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
     * Set the Alert type/context label.
     *
     * @param string $label The Alert label
     * @return Alert|InvalidArgumentException
     */
    public function setLabel($label)
    {
        try {
            if (
                ! is_string($label) ||
                $label == ''
            ) {
                throw new \InvalidArgumentException(
                    'Invalid label - non-empty string required.'
                );
            }

            $this->label = $label;
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
     * Set the Alert level.
     *
     * Level should be beween 0 and 7 inclusive.
     * - 0 - emerg.
     * - 1 - alert.
     * - 2 - crit.
     * - 3 - err.
     * - 4 - warning.
     * - 5 - notice.
     * - 6 - info.
     * - 7 - debug.
     *
     * @param integer $message Alert level
     * @return Alert|InvalidArgumentException
     */
    public function setLevel($level)
    {
        try {
            if (
                ! is_int($level) ||
                $level < 0 ||
                $level > 7
            ) {
                throw new \InvalidArgumentException(
                    'Invalid level - 0-7 expected.'
                );
            }

            $this->level = $level;
        }
        catch (
            InvalidArgumentException $exception
        ) {
            echo $exception->getMessage();
            exit;
        }

        return $this;
    }
}