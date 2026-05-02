<!DOCTYPE html>
<html>
<head>
    <title>FDR Report</title>
    <style>
        body { font-family: Arial; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>

<h2>FDR Enterprise Report</h2>

<table>
    <thead>
        <tr>
            <th>FDR No</th>
            <th>Fund</th>
            <th>Bank</th>
            <th>Amount</th>
            <th>Interest</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $fdr)
            <tr>
                <td>{{ $fdr->fdr_number }}</td>
                <td>{{ $fdr->fund->name }}</td>
                <td>{{ $fdr->bank->name }}</td>
                <td>{{ number_format($fdr->amount, 2) }}</td>
                <td>{{ $fdr->interest_rate }}%</td>
                <td>{{ $fdr->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>