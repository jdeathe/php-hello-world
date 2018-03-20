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
     * @param string $message The Alert message
     * @param integer $level The Alert level
     * @param boolean $dismissible Allow the Alert to be dismissed
     * @param string $label The optional Alert type label
     */
    public function __construct($message = '', $level = self::LEVEL_INFO, $dismissible = false, $label = null)
    {
        $this
            ->setDismissible(
                $dismissible
            )
            ->setLevel(
                $level
            )
            ->setMessage(
                $message
            )
        ;

        if (
            is_string(
                $label
            ) &&
            $label != ''
        ) {
            $this->setLabel(
                $label
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
     * Determine if the Alert is dissmissible.
     *
     * @return boolean True if Alert is dissmissible
     */
    public function getDismissible()
    {
        return $this->dismissible;
    }

    /**
     * Gets the Alert message.
     *
     * @return string The Alert message
     */
    public function getMessage()
    {
        return $this->message;
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

    public function getLevel()
    {
        return $this->level;
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
                ! is_string($message)
            ) {
                throw new \InvalidArgumentException(
                    'Invalid message - string required.'
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