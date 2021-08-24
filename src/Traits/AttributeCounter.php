<?php

namespace Haxibiao\Breeze\Traits;

trait AttributeCounter
{
    public function incrCounter($counter = null)
    {
        $status   = false;
        $counters = $this->getAttributeCounters($counter);
        foreach ($counters as $counter => $relationship) {
            $this->$counter++;
            $status = true;
        }

        return $status;
    }

    public function decrCounter($counter = null)
    {
        $status   = false;
        $counters = $this->getAttributeCounters($counter);
        foreach ($counters as $counter => $relationship) {
            $this->$counter--;
            $status = true;
        }

        return $status;
    }

    public function syncCounter($counter = null)
    {
        $status   = false;
        $counters = $this->getAttributeCounters($counter);
        foreach ($counters as $counter => $relationship) {
            $this->$counter = $this->$relationship()->count();
            $status         = true;
        }

        return $status;
    }

    public function getAttributeCounters($counter = null)
    {
        return !is_null($counter) ? (array_key_exists($counter, $this->attributeCounters) ? [
            $counter => $this->attributeCounters[$counter],
        ] : []) : $this->attributeCounters;
    }
}
