<?php

namespace EasyCredit\Process;

/**
 * Class Status
 *
 * @package EasyCredit\Process
 */
class Status
{

    /**
     * Means the process status is just been created.
     *
     * @const
     */
    const NONE = "NONE";

    /**
     * Means the process status has been initialized with basic values.
     *
     * @const
     */
    const INITIALIZED = "INITIALIZED";

    /**
     * Means the process status has been updated and saved with detailed values.
     *
     * @const
     */
    const SAVED = "SAVED";

    /**
     * Means a payment by easycredit has been accepted, a.k.a. "is possible".
     *
     * @const
     */
    const ACCEPTED = "ACCEPTED";

    /**
     * Means a payment by easycredit has been declined.
     *
     * @const
     */
    const DECLINED = "DECLINED";

    /**
     * Means the payment by easycredit has been finally processed and confirmed.
     *
     * @const
     */
    const CONFIRMED = "CONFIRMED";

    /**
     * Returns all statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(self::NONE, self::INITIALIZED, self::SAVED, self::ACCEPTED, self::DECLINED, self::CONFIRMED);
    }

    /**
     * Returns the possible transitions (follow-up statuses)
     * of a given status
     *
     * @param string $status
     * @return array
     * @throws \Exception
     */
    public static function getTransitions($status)
    {
        switch ($status) {
            case self::NONE:
                return array(self::INITIALIZED);
            case self::INITIALIZED:
                return array(self::SAVED);
            case self::SAVED:
                return array(self::SAVED, self::ACCEPTED, self::DECLINED);
            case self::ACCEPTED:
                return array(self::ACCEPTED, self::CONFIRMED, self::SAVED);
            case self::DECLINED:
                return array(self::INITIALIZED);
            case self::CONFIRMED:
                return array();
            default:
                throw new \Exception(
                    $status." is not an EasyCredit process status"
                );
        }
    }

    /**
     * Return true if the given status is valid, otherwise false.
     *
     * @param string $status
     * @return boolean
     */
    public static function isValidStatus($status)
    {
        return in_array($status, self::getStatuses());
    }

    /**
     * Returns true if there is a valid transition from the old status to the new status,
     * otherwise false.
     *
     * @param string $currentStatus
     * @param string $expectedStatus
     * @return boolean
     */
    public static function isValidTransition($currentStatus, $expectedStatus)
    {
        return in_array($expectedStatus, self::getTransitions($currentStatus));
    }
}
