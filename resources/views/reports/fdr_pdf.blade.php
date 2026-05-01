<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FDR Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<h2>FDR Report</h2>

<table>
    <thead>
        <tr>
            <th>FDR No</th>
            <th>Fund</th>
            <th>Bank</th>
            <th>Amount</th>
            <th>Interest %</th>
            <th>Status</th>
            <th>Maturity Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($fdrs as $fdr)
            <tr>
                <td>{{ $fdr->fdr_number }}</td>
                <td>{{ $fdr->fund->name ?? '' }}</td>
                <td>{{ $fdr->bank->name ?? '' }}</td>
                <td>{{ $fdr->amount }}</td>
                <td>{{ $fdr->interest_rate }}</td>
                <td>{{ $fdr->status }}</td>
                <td>{{ $fdr->maturity_date }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>