<?php

namespace App\Services;

use App\Models\Payment;

class VoucherNumberService
{
    public static function generate()
    {
        $today = now();

        $fyStart = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyEnd   = $fyStart + 1;

        $financialYear = substr($fyStart, -2) . substr($fyEnd, -2);

        $lastVoucher = Payment::where('voucher_no', 'like', "TM/{$financialYear}/%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('voucher_no');

        $nextSequence = $lastVoucher
            ? ((int) substr($lastVoucher, -4)) + 1
            : 1;

        return "TM/{$financialYear}/" . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
