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

    public function __toString();

    public function getDismissible();

    public function getMessage();

    public function getLabel();

    public function getLevel();

    public function setDismissible($dismissible);

    public function setMessage($message);

    public function setLabel($label);

    public function setLevel($level);
}