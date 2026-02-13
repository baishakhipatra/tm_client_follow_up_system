<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Ledger</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .filter-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        td.amount {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #fafafa;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Project Ledger Report</h2>
    </div>

    <div class="filter-info">
        @if($fromDate || $toDate)
            <strong>Date Range:</strong>
            {{ $fromDate ?? 'Beginning' }} 
            to 
            {{ $toDate ?? 'Today' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Project</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Running Balance</th>
            </tr>
        </thead>
        <tbody>

        @php
            $totalDebit = 0;
            $totalCredit = 0;
        @endphp

        @foreach($ledgers as $ledger)
            @php
                $totalDebit += $ledger->debit;
                $totalCredit += $ledger->credit;
            @endphp
            <tr>
                <td>{{ $ledger->entry_date }}</td>
                <td>{{ ucwords(optional($ledger->project)->project_name) }}</td>
                <td>{{ ucwords($ledger->description) }}</td>
                <td class="amount">{{ number_format($ledger->debit, 2) }}</td>
                <td class="amount">{{ number_format($ledger->credit, 2) }}</td>
                <td class="amount">{{ number_format($ledger->running_balance, 2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="3">Total</td>
            <td class="amount">{{ number_format($totalDebit, 2) }}</td>
            <td class="amount">{{ number_format($totalCredit, 2) }}</td>
            <td></td>
        </tr>

        </tbody>
    </table>

</body>
</html>
