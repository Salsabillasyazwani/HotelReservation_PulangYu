<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservation Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        p.sub { margin-top: 0; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Reservation Report</h2>
    <p class="sub">Generated on {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Code</th>
                <th>Guest Name</th>
                <th>Room</th>
                <th>Room Type</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->reservation_code }}</td>
                    <td>{{ $r->guest_name }}</td>
                    <td>{{ $r->room->room_number ?? '-' }}</td>
                    <td>{{ $r->room->roomType->name ?? '-' }}</td>
                    <td>{{ optional($r->check_in)->format('d M Y') }}</td>
                    <td>{{ optional($r->check_out)->format('d M Y') }}</td>
                    <td>{{ $r->reservation_status }}</td>
                    <td>{{ $r->payment_status }}</td>
                    <td>Rp{{ number_format((float) $r->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
