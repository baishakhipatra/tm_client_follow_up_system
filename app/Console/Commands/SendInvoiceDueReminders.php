<?php

namespace App\Console\Commands;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDueReminderMail;

class SendInvoiceDueReminders extends Command
{
    protected $signature = 'invoice:send-due-reminders';
    protected $description = 'Send daily invoice due reminders';

    public function handle()
    {
        $today = now()->toDateString();

        $invoices = Invoice::with(['Client', 'project'])
            ->whereDate('due_date', '<=', $today)
            ->where('status', '!=', 'paid')
            ->where('pending_amount', '>', 0)
            ->get();

        foreach ($invoices as $invoice) {

            if (!$invoice->client?->primary_email) {
                continue;
            }

            Mail::to($invoice->client->primary_email)
                ->send(new InvoiceDueReminderMail($invoice));

            $this->info('Reminder sent for invoice ' . $invoice->invoice_number);
        }

        return Command::SUCCESS;
    }
}
