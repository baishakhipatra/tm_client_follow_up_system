<!DOCTYPE html>
<html>
<head>
    <title>Invoices Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; font-size: 12px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>Invoices Report</h2>

<table>
    <thead>
        <tr>
            <th>Invoice No</th>
            <th>Client</th>
            <th>Project</th>
            <th>Date</th>
            <th>Due</th>
            <th>Amount</th>
            <th>Paid</th>
            <th>Pending</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->invoice_number }}</td>
            <td>{{ ucwords($invoice->client->client_name) ?? '' }}</td>
            <td>{{ ucwords($invoice->project->project_name) ?? '' }}</td>
            <td>{{ $invoice->invoice_date }}</td>
            <td>{{ $invoice->due_date }}</td>
            <td>{{ $invoice->amount }}</td>
            <td>{{ $invoice->paid_amount }}</td>
            <td>{{ $invoice->pending_amount }}</td>
            <td>{{ ucfirst($invoice->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
