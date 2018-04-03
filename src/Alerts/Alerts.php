<?php
namespace jdeathe\PhpHelloWorld\Alerts;

require_once 'Alerts/AlertInterface.php';
require_once 'Alerts/AlertsInterface.php';

class Alerts implements AlertsInterface
{
    /**
     * @var string
     */
    protected $itemClass;

    /**
     * @var array
     */
    protected $items;

    /**
     * @param AlertInterface $alert
     * @param AlertInterface[] @items Alert items from which to populate
     */
    public function __construct(AlertInterface $alert, array $items = array())
    {
        $this->itemClass = get_class(
            $alert
        );
        $this->items = array();

        if (
            ! empty(
                $items
            )
        ) {
            $this->populate(
                $alert,
                $items
            );
        }
    }

    /**
     * Clone Alerts
     */
    function __clone()
    {
        array_walk(
            $this->items,
            function (&$item) {
                $item = clone $item;
            }
        );
    }

    /**
     * Add an alert
     *
     * @param AlertInterface $alert An alert item
     * @return Alerts
     */
    public function add(AlertInterface $alert)
    {
        $this->items[] = $alert;
        $this->sort();

        return $this;
    }

    /**
     * Create a new Alerts instance.
     *
     * @param AlertInterface $alert
     * @param AlertInterface[] @items Alert items from which to populate
     * @return Alerts
     */
    public static function create(AlertInterface $alert, array $items = array())
    {
        return new self(
            $alert,
            $items
        );
    }

    /**
     * Extract the Alert items
     *
     * @return AlertInterface[] The Alert items
     */
    public function extract()
    {
        $items = array();

        if (
            empty(
                $this->items
            )
        ) {
            return $this;
        }

        foreach ($this->items as $item) {
            $items[] = $item->extract();
        }

        return $items;
    }

    /**
     * Get all Alerts by level
     *
     * @param integer $level The Alert level
     * @return AlertInterface[]
     */
    public function get($level)
    {
        return array_filter(
            $this->items,
            function($alert) use ($level) {
                return
                    $level == $alert->getLevel()
                ;
            }
        );
    }

    /**
     * Get all Alerts
     *
     * @param integer $level The Alert level limit
     * @return AlertInterface[]
     */
    public function getAll($level = AlertInterface::LEVEL_INFO)
    {
        return array_filter(
            $this->items,
            function($alert) use ($level) {
                return
                    $level >= $alert->getLevel()
                ;
            }
        );
    }

    /**
     * Populates the Alerts from Alert items
     *
     * @param AlertInterface $alert
     * @param AlertInterface[] $items Alert items from which to populate
     * @return Alerts
     */
    public function populate(AlertInterface $alert, array $items = array())
    {
        if (
            empty(
                $items
            )
        ) {
            return $this;
        }

        foreach ($items as $item) {
            $this->add(
                $alert->populate(
                    $item
                )
            );
        }

        return $this;
    }

    /**
     * Sort AlertInterface items by Level
     */
    private function sort()
    {
        if (
            empty(
                $this->items
            )
        ) {
            return;
        }

        usort(
            $this->items,
            function($a, $b) {
                if (
                    $a->getLevel() === $b->getLevel()
                ) {
                    return 0;
                }

                return
                    $a->getLevel() < $b->getLevel()
                    ? -1
                    : 1
                ;
            }
        );
    }
}