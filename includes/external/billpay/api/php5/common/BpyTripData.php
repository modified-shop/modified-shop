<?php
class BpyTripData extends ArrayObject
{

    public function setHistoricalTripCount($tripCount)
    {
        $this['historicaltripcount'] = $tripCount;

        return $this;
    }

    public function setHistoricalTripAmount($amount, $currency)
    {
        $this['historicaltripamount'] = $amount;
        $this['historicaltripcurrency'] = strtoupper($currency);

        return $this;
    }

    public function setTravelers(array $travelerList)
    {
        $this['traveler_list'] = $travelerList;
        $this['traveler_list']['groupsize'] = count($travelerList);

        return $this;
    }

    public function addTraveler(BpyTripTraveler $traveler)
    {
        if (isset($this['traveler_list']) === false) $this['traveler_list'] = array();
        if (isset($this['traveler_list']['traveler']) === false) $this['traveler_list']['traveler'] = array();

        $this['traveler_list']['traveler'][] = (array)$traveler;
        $this['traveler_list']['groupsize'] = count($this['traveler_list']['traveler']);

        return $this;
    }
}