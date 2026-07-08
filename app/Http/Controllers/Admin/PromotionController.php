<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\RoomType;
use App\Exports\PromotionsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->search($request->q)
            ->ofType($request->type)
            ->ofStatus($request->status)
            ->dateRange($request->date_from, $request->date_to)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalPromotion = Promotion::count();

        $activePromotion = Promotion::where('status', 'Active')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();

        $endingPromotion = Promotion::where('status', 'Active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays(7))
            ->count();

        $expiredPromotion = Promotion::whereDate('end_date', '<', Carbon::today())
            ->count();

        $roomTypes = RoomType::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.promotion.promotion', compact(
            'promotions',
            'totalPromotion',
            'activePromotion',
            'endingPromotion',
            'expiredPromotion',
            'roomTypes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        try {

            if ($request->hasFile('banner')) {

                $path = $request->file('banner')->store('promotions', 'public');

                $validated['banner'] = $path;

                Log::info('Banner berhasil disimpan', [
                    'path' => $path
                ]);
            }

            $validated['rooms'] = $request->input('rooms', []);

            Promotion::create($validated);

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion created successfully.');

        } catch (\Exception $e) {

            Log::error('Gagal menyimpan promotion', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan promotion.');
        }
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $this->validateData($request, $promotion->id);

        try {

            if ($request->hasFile('banner')) {

                if ($promotion->banner && Storage::disk('public')->exists($promotion->banner)) {
                    Storage::disk('public')->delete($promotion->banner);
                }

                $validated['banner'] = $request->file('banner')->store('promotions', 'public');
            }

            $validated['rooms'] = $request->input('rooms', []);

            $promotion->update($validated);

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion updated successfully.');

        } catch (\Exception $e) {

            Log::error('Gagal update promotion', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate promotion.');
        }
    }

    public function destroy(Promotion $promotion)
    {
        try {

            if ($promotion->banner && Storage::disk('public')->exists($promotion->banner)) {
                Storage::disk('public')->delete($promotion->banner);
            }

            $promotion->delete();

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion deleted successfully.');

        } catch (\Exception $e) {

            Log::error('Gagal hapus promotion', [
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus promotion.');
        }
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');
        $filenameBase = 'promotions_' . now()->format('Ymd_His');

        if ($type === 'excel') {
            return Excel::download(new PromotionsExport, "{$filenameBase}.xlsx");
        }

        if ($type === 'pdf') {

            $promotions = Promotion::latest()->get();

            $pdf = Pdf::loadView(
                'admin.promotion.export-pdf',
                compact('promotions')
            )->setPaper('a4', 'landscape');

            return $pdf->download("{$filenameBase}.pdf");
        }

        $promotions = Promotion::latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filenameBase}.csv",
        ];

        $callback = function () use ($promotions) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Name',
                'Code',
                'Type',
                'Discount',
                'Minimum Purchase',
                'Maximum Discount',
                'Start',
                'End',
                'Status',
                'Quota'
            ]);

            foreach ($promotions as $promotion) {

                fputcsv($file, [
                    $promotion->promo_name,
                    $promotion->promo_code,
                    $promotion->discount_type,
                    $promotion->discount_value,
                    $promotion->minimum_booking,
                    $promotion->maximum_discount,
                    optional($promotion->start_date)->format('Y-m-d'),
                    optional($promotion->end_date)->format('Y-m-d'),
                    $promotion->computed_status,
                    $promotion->quota,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $codeRule = 'required|string|max:50|unique:promotions,promo_code';

        if ($ignoreId) {
            $codeRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'promo_name' => 'required|string|max:150',
            'promo_code' => $codeRule,
            'discount_type' => 'required|in:Percentage,Voucher,Fixed Amount',
            'discount_value' => 'required|numeric|min:0',
            'minimum_booking' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:Active,Inactive',
            'quota' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|max:2048',
            'rooms' => 'nullable|array',
            'rooms.*' => 'integer|exists:room_types,id',
        ]);
    }
}
