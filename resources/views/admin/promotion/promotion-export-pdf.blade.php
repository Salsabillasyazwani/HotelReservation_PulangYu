<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; color: #0f172a; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    p.sub { color: #64748B; margin-top: 0; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #E2E8F0; padding: 6px 8px; text-align: left; }
    th { background: #F8FAFC; font-size: 10px; }
</style>
</head>
<body>
    <h1>Promotion Report</h1>
    <p class="sub">Generated on {{ now()->format('d M Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Type</th>
                <th>Discount</th>
                <th>Min Purchase</th>
                <th>Max Discount</th>
                <th>Period</th>
                <th>Status</th>
                <th>Quota</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($promotions as $promotion)
                <tr>
                    <td>{{ $promotion->promo_name }}</td>
                    <td>{{ $promotion->promo_code }}</td>
                    <td>{{ $promotion->discount_type }}</td>
                    <td>{{ $promotion->discount_value }}</td>
                    <td>{{ $promotion->minimum_booking }}</td>
                    <td>{{ $promotion->maximum_discount ?? '-' }}</td>
                    <td>{{ optional($promotion->start_date)->format('d M Y') }} - {{ optional($promotion->end_date)->format('d M Y') }}</td>
                    <td>{{ $promotion->computed_status }}</td>
                    <td>{{ $promotion->quota ?? 'Unlimited' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
