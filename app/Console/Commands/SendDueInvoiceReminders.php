<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDueInvoiceReminders extends Command
{
    protected $signature = 'invoice:send-due-reminders';

    protected $description = 'Send daily reminder emails for overdue invoices';

    public function handle()
    {
        Log::info('Invoice due reminder command started');

        $today = Carbon::today();

        $invoices = Invoice::with(['Client', 'project'])
            ->whereDate('due_date', '<', $today)
            ->where('pending_amount', '>', 0)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get();

        // Terminal output
        $this->info('Overdue invoices found: ' . $invoices->count());

        // Log output
        Log::info('Overdue invoices count', [
            'count' => $invoices->count()
        ]);

        foreach ($invoices as $invoice) {

            if (!$invoice->Client || !$invoice->Client->primary_email) {
                Log::warning('Invoice skipped (no client email)', [
                    'invoice_id' => $invoice->id
                ]);
                continue;
            }

            Log::info('Sending invoice reminder email', [
                'invoice_id'      => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'client_email'    => $invoice->Client->primary_email,
                'due_date'        => $invoice->due_date,
                'pending_amount'  => $invoice->pending_amount,
            ]);

            Mail::raw(
                "Dear {$invoice->Client->client_name},

                Your invoice {$invoice->invoice_number} for project {$invoice->project->project_name}
                was due on {$invoice->due_date}.

                Pending Amount: ₹{$invoice->pending_amount}

                Please make the payment at the earliest.

                Thank you.",
                            function ($message) use ($invoice) {
                                $message->to($invoice->Client->primary_email)
                                    ->subject('Invoice Due Reminder - ' . $invoice->invoice_number);
                            }
                        );
                    }

                    Log::info('Invoice due reminder command finished');

                    $this->info('Reminder emails processed successfully.');

                    return Command::SUCCESS;
            }
}

