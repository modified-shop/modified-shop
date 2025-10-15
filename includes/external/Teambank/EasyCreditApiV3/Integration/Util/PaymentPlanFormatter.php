<?php
namespace Teambank\EasyCreditApiV3\Integration\Util;

class PaymentPlanFormatter {
    public function format($paymentPlan) { 
        if (is_string($paymentPlan)) {
            $paymentPlan = json_decode($paymentPlan);
        }
		if (!is_object($paymentPlan)) {
            return '';
        }
        return sprintf('%d Raten à %0.2f€ (%d x %0.2f€, %d x %0.2f€)',
            (int)   $paymentPlan->numberOfInstallments,
            (float) $paymentPlan->installment,
            (int)   $paymentPlan->numberOfInstallments - 1,
            (float) $paymentPlan->installment,
            1,
            (float) $paymentPlan->lastInstallment
        );
    }
}
