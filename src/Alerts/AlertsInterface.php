<?php
namespace jdeathe\PhpHelloWorld\Alerts;

interface AlertsInterface
{
    /**
     * Get all Alerts by level
     *
     * @param integer $level The Alert level
     * @return array
     */
    public function get($level);

    /**
     * Get all Alerts
     *
     * @param integer $level The Alert level limit
     * @return array
     */
    public function getAll($level = AlertInterface::LEVEL_INFO);

    /**
     * Add an alert
     *
     * @param AlertInterface $alert An alert item
     */
    public function add(AlertInterface $alert);
}