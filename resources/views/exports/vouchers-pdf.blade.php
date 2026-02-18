<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h3>Voucher Report</h3>

<table>
    <thead>
        <tr>
            <th>Voucher No</th>
            <th>Client</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vouchers as $voucher)
            <tr>
                <td>{{ $voucher->voucher_no }}</td>
                <td>{{ $voucher->Client->company_name ?? '' }}</td>
                <td>{{ number_format($voucher->payment_amount, 2) }}</td>
                <td>{{ strtoupper($voucher->payment_method) }}</td>
                <td>{{ $voucher->status == 1 ? 'Paid' : 'Pending' }}</td>
                <td>{{ $voucher->created_at->format('d-m-Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
