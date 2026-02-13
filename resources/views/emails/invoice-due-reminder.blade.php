<p>Dear {{ $invoice->client->client_name }},</p>

<p>This is a reminder that the following invoice is still unpaid:</p>

<ul>
    <li><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</li>
    <li><strong>Project:</strong> {{ $invoice->project->project_name }}</li>
    <li><strong>Invoice Date:</strong> {{ $invoice->invoice_date }}</li>
    <li><strong>Due Date:</strong> {{ $invoice->due_date }}</li>
    <li><strong>Pending Amount:</strong> ₹{{ number_format($invoice->pending_amount,2) }}</li>
</ul>

<p>Please make the payment at the earliest.</p>

<p>Regards,<br>Tech Mantra Softwares</p>
