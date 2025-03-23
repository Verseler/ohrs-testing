<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Http\Request;

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


        if ($selectedDate->isCurrentMonth()) {
            $startDate = $selectedDate->startOfMonth();
            $endDate = Carbon::now('Asia/Manila');
        } else {
            $startDate = $selectedDate->startOfMonth();
            $endDate = $selectedDate->endOfMonth();
        }

        $payments = Payment::with('reservation.guests')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $reports = $payments->map(function ($payment) {
            return [
                'date' => $payment->created_at->toDateString(),
                'orNumber' => $payment->or_number,
                'particulars' => $payment->reservation->first_name . ' ' . $payment->reservation->last_name,
                'numberOfGuests' => $payment->reservation->guests->count(),
                'amount' => $payment->amount,
            ];
        });

        $totalAmount = $payments->sum('amount');

        return Inertia::render('Admin/GenerateReport/ReportList', [
            'reports' => $reports,
            'totalAmount' => $totalAmount,
        ]);
    }
}
