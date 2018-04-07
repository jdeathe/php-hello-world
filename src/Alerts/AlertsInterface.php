<?php
namespace jdeathe\PhpHelloWorld\Alerts;

interface AlertsInterface
{
    /**
     * Create a new Alerts instance.
     *
     * @param AlertInterface $alert
     * @param AlertInterface[] @items Alert items from which to populate
     * @return AlertsInterface
     */
    public static function create(AlertInterface $alert, array $items = array());

    /**
     * Extract the Alert items
     *
     * @return AlertInterface[] The Alert items
     */
    public function extract();

    /**
     * Populates the Alerts from Alert items
     *
     * @param AlertInterface $alert
     * @param AlertInterface[] $items Alert items from which to populate
     * @return AlertsInterface
     */
    public function populate(AlertInterface $alert, array $items = array());

    /**
     * Get all Alerts by level
     *
     * @param integer $level The Alert level
     * @return AlertInterface[]
     */
    public function get($level);

    /**
     * Get all Alerts
     *
     * @param integer $level The Alert level limit
     * @return AlertInterface[]
     */
    public function getAll($level = AlertInterface::LEVEL_INFO);

    /**
     * Add an alert
     *
     * @param AlertInterface $alert An alert item
     * @return AlertsInterface
     */
    public function add(AlertInterface $alert);
}