<?php
class BpyTripTraveler extends ArrayObject
{
    const MEMBERSHIP_NOT_AVAILABLE=0;
    const MEMBERSHIP_YES=1;
    const MEMBERSHIP_NO=2;

    public function setSalutation($salutation)
    {
        $this['salutation'] = $salutation;

        return $this;
    }

    public function setFirstName($firstName)
    {
        $this['firstname'] = $firstName;

        return $this;
    }

    public function setLastName($lastName)
    {
        $this['lastname'] = $lastName;

        return $this;
    }

    public function setDayOfBirth($dayOfBirth)
    {
        $this['birthday'] = $dayOfBirth;

        return $this;
    }

    public function setFrequentFlyerDetails($membership = 0, $program = 0, $membershipId = 0)
    {
        $this['flight_information']['frequent_flyer'] = array(
            'membership' => $membership,
            'program' => $program,
            'membershipid' => $membershipId
        );

        return $this;
    }
}
