<?php
namespace jdeathe\PhpHelloWorld\Alerts;

interface AlertInterface
{
    const LEVEL_EMERG = 0;
    const LEVEL_ALERT = 1;
    const LEVEL_CRIT = 2;
    const LEVEL_ERR = 3;
    const LEVEL_WARNING = 4;
    const LEVEL_NOTICE = 5;
    const LEVEL_INFO = 6;
    const LEVEL_DEBUG = 7;
    const LABEL_ERR = 'error';
    const LABEL_WARNING = 'warning';
    const LABEL_NOTICE = 'notice';
    const LABEL_INFO = 'info';

    /**
     * Gets the Alert message.
     *
     * @return string The Alert message
     */
    public function __toString();

    /**
     * Create a new Alert instance.
     *
     * @param array $attributes The Alert attributes
     * @return AlertInterface
     */
    public static function create(array $attributes = array());

    /**
     * Extract the Alert attributes
     *
     * @return array The Alert attributes
     */
    public function extract();

    public function getDismissible();

    public function getMessage();

    public function getLabel();

    public function getLevel();

    /**
     * Populates the Alert from attributes
     *
     * @param array $attributes The Alert attributes
     * @return AlertInterface
     */
    public function populate(array $attributes = array());

    public function setDismissible($dismissible);

    public function setMessage($message);

    public function setLabel($label);

    public function setLevel($level);
}