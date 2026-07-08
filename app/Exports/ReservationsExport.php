<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $reservations;

    public function __construct($reservations)
    {
        $this->reservations = $reservations;
    }

    public function collection()
    {
        return $this->reservations;
    }

    public function headings(): array
    {
        return [
            'No',
            'Reservation Code',
            'Guest Name',
            'Room',
            'Room Type',
            'Check In',
            'Check Out',
            'Reservation Status',
            'Payment Status',
            'Total Amount',
        ];
    }

    public function map($reservation): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $reservation->reservation_code,
            $reservation->guest_name,
            $reservation->room->room_number ?? '-',
            $reservation->room->roomType->name ?? '-',
            optional($reservation->check_in)->format('d M Y'),
            optional($reservation->check_out)->format('d M Y'),
            $reservation->reservation_status,
            $reservation->payment_status,
            (float) $reservation->total_amount,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
