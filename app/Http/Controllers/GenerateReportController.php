<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\StayDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenerateReportController extends Controller
{
    public function list(Request $request)
    {
        $validated = $request->validate([
            'revenue_date' => ['nullable', 'date'],
            'collectable_date' => ['nullable', 'date'],
        ]);

        $selectedRevenueDate = $request->revenue_date ?
            Carbon::parse($validated['revenue_date'])->setTimezone('Asia/Manila') :
            Carbon::now();

        $selectedCollectableDate = $request->collectable_date ?
            Carbon::parse($validated['collectable_date'])->setTimezone('Asia/Manila') :
            Carbon::now();

        //Get all report data
        $revenueData = $this->getRevenueReportData($selectedRevenueDate);
        $collectableData = $this->getCollectableReportData($selectedCollectableDate);

        return Inertia::render('Admin/GenerateReport/ReportList', [
            'revenueReport' => $revenueData['reports'],
            'revenueTotalAmount' => $revenueData['totalAmount'],
            'collectableReport' => $collectableData['reports'],
            'collectableTotalAmount' => $collectableData['totalAmount'],
        ]);
    }

    public function download($selected_date, $type)
    {
        $selectedDate = Carbon::parse($selected_date)->setTimezone('Asia/Manila');

        //Get all report data
        $revenueData = $this->getRevenueReportData($selectedDate);
        $collectableData = $this->getCollectableReportData($selectedDate);

        $office = Office::find(Auth::user()->office_id);

        /* Generate PDF */
        //Collectable report
        if ($type === 'collectable') {
            $pdf = Pdf::loadView('pdf.collectable_report', [
                'reports' => $collectableData['reports'],
                'totalAmount' => $collectableData['totalAmount'],
                'officeName' => $office->name,
            ]);

            return $pdf->download("hrs-collectables-report-{$selected_date}.pdf");
        }

        //Revenue report
        $pdf = Pdf::loadView('pdf.revenue_report', [
            'reports' => $revenueData['reports'],
            'totalAmount' => $revenueData['totalAmount'],
            'officeName' => $office->name,
        ]);

        return $pdf->download("hrs-revenue-report-{$selected_date}.pdf");
    }

    public function print($selected_date, $type)
    {
        $selectedDate = Carbon::parse($selected_date)->setTimezone('Asia/Manila');

        //Get all report data
        $revenueData = $this->getRevenueReportData($selectedDate);
        $collectableData = $this->getCollectableReportData($selectedDate);

        $office = Office::find(Auth::user()->office_id);

        // Generate PDF
        if ($type === 'collectable') {
            $pdf = Pdf::loadView('pdf.collectable_report', [
                'reports' => $collectableData['reports'],
                'totalAmount' => $collectableData['totalAmount'],
                'officeName' => $office->name,
            ]);

            return $pdf->stream("hrs-collectables-report-{$selected_date}.pdf");
        }

        //Revenue report
        $pdf = Pdf::loadView('pdf.revenue_report', [
            'reports' => $revenueData['reports'],
            'totalAmount' => $revenueData['totalAmount'],
            'officeName' => $office->name,
        ]);

        return $pdf->stream("hrs-revenue-report-{$selected_date}.pdf");
    }


    private function getRevenueReportData($selectedDate)
    {
        $startDate = $selectedDate->copy()->startOfMonth();
        $endDate = $selectedDate->copy()->endOfMonth();

        $payments = Payment::with(['reservation.guests', 'reservation.stayDetails'])
            ->whereHas('reservation', function ($query) {
                $query->addSelect([
                    'min_check_in_date' => StayDetails::select('check_in_date')
                        ->whereColumn('reservation_id', 'reservations.id')
                        ->orderBy('check_in_date')
                        ->limit(1),
                    'max_check_out_date' => StayDetails::select('check_out_date')
                        ->whereColumn('reservation_id', 'reservations.id')
                        ->orderByDesc('check_out_date')
                        ->limit(1)
                ])
                ->where('hostel_office_id', Auth::user()->office_id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $revenueReport = $payments->map(function ($payment) {
            $checkInDate = Carbon::parse($payment->reservation->min_check_in_date);
            $checkOutDate = Carbon::parse($payment->reservation->max_check_out_date);
            $lengthOfStay = (int)$checkInDate->diffInDays($checkOutDate);

            if ($lengthOfStay == 0) {
                $lengthOfStay = 1;
            }

            $now = Carbon::now();

            return [
                'id' => "{$payment->id} - {$now->toDateString()}",
                'date' => $payment->created_at->toDateString(),
                'orNumber' => $payment->or_number,
                'bookedBy' => $payment->reservation->first_name . ' ' . $payment->reservation->last_name,
                'numberOfGuests' => $payment->reservation->guests->count(),
                'numberOfDays' => $lengthOfStay,
                'amount' => $payment->amount,
            ];
        });

        $totalRevenueAmount = $payments->sum('amount');

        $revenueData = [
            'reports' => $revenueReport,
            'totalAmount' => $totalRevenueAmount,
        ];

        return $revenueData;
    }


    private function getCollectableReportData($selectedDate)
    {
        $startDate = $selectedDate->copy()->startOfMonth();
        $endDate = $selectedDate->copy()->endOfMonth();

        $reservations = Reservation::with(['guests'])
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ])
            ->where('hostel_office_id', Auth::user()->office_id)
            ->where('remaining_balance', '>', 0)
            ->whereNotIn('general_status', ['pending', 'cancelled'])
            ->whereHas('stayDetails', function($query) use ($startDate, $endDate) {
                $query->whereBetween('check_out_date', [$startDate, $endDate])
                      ->orderByDesc('check_out_date')
                      ->limit(1);
            })
            ->get();

        $collectableReport = $reservations->map(function ($reservation) {
            $checkInDate = Carbon::parse($reservation->min_check_in_date);
            $checkOutDate = Carbon::parse($reservation->max_check_out_date);
            $lengthOfStay = (int)$checkInDate->diffInDays($checkOutDate);

            if ($lengthOfStay == 0) {
                $lengthOfStay = 1;
            }

            $now = Carbon::now();

            return [
                'id' => "{$reservation->id} - {$now->toDateString()}",
                'bookedBy' => "{$reservation->first_name} {$reservation->last_name}",
                'numberOfGuests' => $reservation->guests->count(),
                'numberOfDays' => $lengthOfStay,
                'amount' => $reservation->remaining_balance,
            ];
        });

        $totalCollectableAmount = $collectableReport->sum('amount');

        $collectableData = [
            'reports' => $collectableReport,
            'totalAmount' => $totalCollectableAmount,
        ];

        return $collectableData;
    }
}
