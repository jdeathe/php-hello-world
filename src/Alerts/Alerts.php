<?php
namespace jdeathe\PhpHelloWorld\Alerts;

require_once 'Alerts/AlertInterface.php';
require_once 'Alerts/AlertsInterface.php';

class Alerts implements AlertsInterface
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @param AlertInterface[] $alerts Items implementing AlertInterface
     */
    public function __construct()
    {
        $this->items = array();
    }

    /**
     * {@inheritdoc}
     */
    public function add(AlertInterface $alert)
    {
        $this->items[] = $alert;
        $this->sort();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * Sort AlertInterface items by Level
     */
    private function sort()
    {
        if(
            empty(
                $this->items
            )
        ) {
            return void;
        }

        usort(
            $this->items,
            function($a, $b) {
                if ($a->getLevel() === $b->getLevel()) {
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