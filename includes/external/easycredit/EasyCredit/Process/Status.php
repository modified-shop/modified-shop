<?php

namespace EasyCredit\Process;

use EasyCredit\Transfer\ProcessInitialize;

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
     * ONLY if integration type is API and decision is approved, then a MTAN needs to be verified. Status means MTAN is verified and valid, transaction can be confirmed.
     * 
     * @const
     */
    const MTAN = "MTANVERIFIED";
    
    const TRANSITIONS = [
        ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE => [
            self::NONE => [
                self::INITIALIZED
            ],
            self::INITIALIZED => [
                self::SAVED,
                self::ACCEPTED,
                self::DECLINED
            ],
            self::SAVED => [
                self::SAVED,
                self::ACCEPTED,
                self::DECLINED
            ],
            self::ACCEPTED => [
                self::ACCEPTED,
                self::CONFIRMED,
                self::SAVED
            ],
            self::CONFIRMED => [
            ],
            self::DECLINED => [
                self::INITIALIZED
            ]
        ],
        ProcessInitialize::INTEGRATION_TYPE_SERVICE_INTEGRATION => [
            self::NONE => [
                self::INITIALIZED
            ],
            self::INITIALIZED => [
                self::SAVED,
                self::ACCEPTED,
                self::DECLINED
            ],
            self::SAVED => [
                self::SAVED,
                self::ACCEPTED,
                self::DECLINED
            ],
            self::ACCEPTED => [
                self::ACCEPTED,
                self::MTAN,
                self::SAVED
            ],
            self::MTAN => [
                self::SAVED,
                self::MTAN,
                self::CONFIRMED
            ],
            self::CONFIRMED => [
            ],
            self::DECLINED => [
                self::INITIALIZED
            ]
        ]
    ];
    
    /**
     * Returns all statuses.
     * 
     * @param string $integrationType
     * @return array
     */
    public static function getStatuses($integrationType = ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE)
    {
        if ($integrationType == ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE) {
            return array(self::NONE, self::INITIALIZED, self::SAVED, self::ACCEPTED, self::DECLINED, self::CONFIRMED);
        } else {
            return array(self::NONE, self::INITIALIZED, self::SAVED, self::ACCEPTED, self::DECLINED, self::MTAN, self::CONFIRMED);
        }
    }

    /**
     * Returns the possible transitions (follow-up statuses)
     * of a given status
     *
     * @param string $status
     * @param string $integrationType
     * @return array
     * @throws \Exception
     */
    public static function getTransitions($status, $integrationType = ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE)
    {
        $transitions = self::TRANSITIONS[$integrationType];
        
        if (array_key_exists($status, $transitions) ) {
            return $transitions[$status];
        } else {
            throw new \Exception(
                $status . " is not an EasyCredit process status"
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
     * @param string $integrationType
     * @return boolean
     */
    public static function isValidTransition($currentStatus, $expectedStatus, $integrationType = ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE)
    {
        return in_array($expectedStatus, self::getTransitions($currentStatus, $integrationType));
    }
}
