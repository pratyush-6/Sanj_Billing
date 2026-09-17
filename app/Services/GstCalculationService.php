<?php

namespace App\Services;

class GstCalculationService
{
    /**
     * Splits a line's GST into CGST+SGST (same state) or IGST (different state) —
     * both Company.state and Party.state are constrained to the same canonical
     * India state list (config/india.php), so this is an exact string comparison,
     * not fuzzy matching. Unknown state on either side falls back to IGST rather
     * than guessing same-state.
     */
    public function splitGst(float $taxableAmount, float $gstRate, ?string $companyState, ?string $partyState): array
    {
        $gstAmount = round($taxableAmount * $gstRate / 100, 2);

        $sameState = $companyState !== null && $partyState !== null && $companyState === $partyState;

        if ($sameState) {
            $cgst = round($gstAmount / 2, 2);

            return [
                'cgst_amount' => $cgst,
                'sgst_amount' => round($gstAmount - $cgst, 2),
                'igst_amount' => 0.0,
            ];
        }

        return [
            'cgst_amount' => 0.0,
            'sgst_amount' => 0.0,
            'igst_amount' => $gstAmount,
        ];
    }
}
