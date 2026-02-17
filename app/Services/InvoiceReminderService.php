<?php

namespace App\Services;

use App\Models\Invoice;
use App\Mail\InvoiceDueReminderMail;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderService
{
    public function sendReminder(Invoice $invoice): bool
    {
        if (
            !$invoice->client?->primary_email ||
            $invoice->status === 'paid' ||
            $invoice->pending_amount <= 0
        ) {
            return false;
        }

        Mail::to($invoice->client->primary_email)
            ->send(new InvoiceDueReminderMail($invoice));

        return true;
    }
}
