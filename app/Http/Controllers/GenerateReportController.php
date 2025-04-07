<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenerateReportController extends Controller
{
    public function list(Request $request)
    {
        $validated = $request->validate([
            'selected_date' => ['nullable', 'date'],
        ]);

        $selectedDate = $request->selected_date ?
            Carbon::parse($validated['selected_date'])->setTimezone('Asia/Manila') :
            Carbon::now();

        //Get all report data
        $data = $this->getReportData($selectedDate);

        return Inertia::render('Admin/GenerateReport/ReportList', [
            'reports' => $data['reports'],
            'totalAmount' => $data['totalAmount'],
        ]);
    }

    public function download($selected_date)
    {
        $selectedDate = Carbon::parse($selected_date)->setTimezone('Asia/Manila');

        //Get all report data
        $data = $this->getReportData($selectedDate);

        $office = Office::find(Auth::user()->office_id);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', [
            'reports' => $data['reports'],
            'totalAmount' => $data['totalAmount'],
            'officeName' => $office->name,
        ]);

        return $pdf->download("hrs-report-{$selected_date}.pdf");
    }

    public function print($selected_date)
    {
        $selectedDate = Carbon::parse($selected_date)->setTimezone('Asia/Manila');

        //Get all report data
        $data = $this->getReportData($selectedDate);

        $office = Office::find(Auth::user()->office_id);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', [
            'reports' => $data['reports'],
            'totalAmount' => $data['totalAmount'],
            'officeName' => $office->name,
        ]);

        return $pdf->stream("hrs-report-{$selected_date}.pdf");
    }


    private function getReportData($selectedDate)
    {
        if ($selectedDate->isCurrentMonth()) {
            $startDate = $selectedDate->startOfMonth();
            $endDate = Carbon::now('Asia/Manila');
        } else {
            $startDate = $selectedDate->startOfMonth();
            $endDate = $selectedDate->endOfMonth();
        }

        $payments = Payment::with('reservation.guests')
            ->whereHas('reservation', function ($query) {
                $query->where('hostel_office_id', Auth::user()->office_id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $reports = $payments->map(function ($payment) {
            $checkInDate = Carbon::parse($payment->reservation->check_in_date);
            $checkOutDate = Carbon::parse($payment->reservation->check_out_date);
            $lengthOfStay = $checkInDate->diffInDays($checkOutDate, false);

            if ($lengthOfStay === 0) {
                $lengthOfStay = 1;
            }

            return [
                'date' => $payment->created_at->toDateString(),
                'orNumber' => $payment->or_number,
                'bookedBy' => $payment->reservation->first_name . ' ' . $payment->reservation->last_name,
                'numberOfGuests' => $payment->reservation->guests->count(),
                'numberOfDays' => $lengthOfStay,
                'amount' => $payment->amount,
            ];
        });

        $totalAmount = $payments->sum('amount');

        $data = [
            'reports' => $reports,
            'totalAmount' => $totalAmount,
        ];

        return $data;
    }
}
