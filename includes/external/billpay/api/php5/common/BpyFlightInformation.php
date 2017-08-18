<?php
class BpyFlightInformation extends ArrayObject
{

    const NOT_AVAILABLE = 'na';

    const BUSINESS_CLASS = 'bc';
    const FIRST_CLASS = 'fc';
    const COACH = 'co';

    const ROUND_TRIP = 'rt';
    const ONE_WAY = 'ow';

    const REPOOKING_NOT_POSSIBLE = 'nr';
    const REBOOKING_WITH_FEE = 'rf';
    const REBOOKING_WITHOUT_FEE = 'fl';

    /**
     * @param string $departureAirport
     * @return $this
     */
    public function setDepartingFrom($departureAirport)
    {
        $this['departingfrom'] = $departureAirport;

        return $this;
    }

    /**
     * @param String|DateTime $date
     * @return $this
     */
    public function setDepartingOn($date)
    {
        if (get_class($date) == 'DateTime') {
            $date = $date->format('Ymd');
        }
        $this['departingon'] = $date;

        return $this;
    }

    public function setArrivingAt($destinationAirport)
    {
        $this['arrivingat'] = $destinationAirport;

        return $this;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setArrivingOn($date)
    {
        if (get_class($date) == 'DateTime') {
            $date = $date->format('Ymd');
        }
        $this['arrivingon'] = $date;

        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setBookingClass($class)
    {
        $this['bookingclass'] = $class;

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setRouteType($type)
    {
        $this['routetype'] = $type;

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setTicketType($type)
    {
        $this['tickettype'] = $type;

        return $this;
    }
}