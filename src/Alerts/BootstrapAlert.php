<?php
namespace jdeathe\PhpHelloWorld\Alerts;

require_once 'Alerts/Alert.php';

class BootstrapAlert extends Alert implements AlertInterface
{
    const LABEL_DANGER = 'danger';
    const LABEL_SUCCESS = 'success';

    /**
     * @param array $attributes The Alert attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * Create a new Alert instance.
     *
     * @param array $attributes The Alert attributes
     * @return BootstrapAlert
     */
    public static function create(array $attributes = array())
    {
        return new self($attributes);
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
                $this->label = self::LABEL_DANGER;
                break;
            case self::LEVEL_WARNING:
                $this->label = self::LABEL_WARNING;
                break;
            case self::LEVEL_NOTICE:
                $this->label = self::LABEL_SUCCESS;
                break;
            case self::LEVEL_INFO:
            case self::LEVEL_DEBUG:
            default:
                $this->label = self::LABEL_INFO;
                break;
        }

        return $this->label;
    }
}