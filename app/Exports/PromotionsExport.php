<?php

namespace App\Exports;

use App\Models\Promotion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PromotionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Promotion::orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'Promotion Name',
            'Promo Code',
            'Type',
            'Discount',
            'Minimum Purchase',
            'Maximum Discount',
            'Start Date',
            'End Date',
            'Status',
            'Quota',
        ];
    }

    public function map($promotion): array
    {
        return [
            $promotion->promo_name,
            $promotion->promo_code,
            $promotion->discount_type,
            $promotion->discount_value,
            $promotion->minimum_booking,
            $promotion->maximum_discount ?? '-',
            optional($promotion->start_date)->format('Y-m-d'),
            optional($promotion->end_date)->format('Y-m-d'),
            $promotion->computed_status,
            $promotion->quota ?? 'Unlimited',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
