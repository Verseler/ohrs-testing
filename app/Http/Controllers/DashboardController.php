<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'selected_date' => ['nullable', 'date'],
            'monthly_revenue_year' => ['nullable', 'date']
        ]);

        $selectedDate = $request->selected_date ?
            Carbon::parse($validated['selected_date'])->setTimezone('Asia/Manila') :
            Carbon::now();

        $monthlyRevenueYear = $request->monthly_revenue_year ?
            Carbon::parse($validated['monthly_revenue_year'])->setTimezone('Asia/Manila') :
            Carbon::now();

        //For the selected month and year (selectedDate)
        $pendingReservationsCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id],
            ['status', '=', 'pending']
        ])
            ->whereYear('check_in_date', $selectedDate->year)
            ->whereMonth('check_in_date', $selectedDate->month)
            ->count();

        //total reservation
        $totalReservationsCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id]
        ])
            ->whereNotIn('status', ['pending', 'canceled'])
            ->whereYear('check_in_date', $selectedDate->year)
            ->whereMonth('check_in_date', $selectedDate->month)
            ->count();

        //total guests
        $totalGuestsCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id]
        ])
            ->whereNotIn('status', ['pending', 'canceled'])
            ->whereYear('check_in_date', $selectedDate->year)
            ->whereMonth('check_in_date', $selectedDate->month)
            ->withCount('guests')
            ->get()
            ->sum('guests_count');

        //total revenue (Already paid)
        $totalRevenue = Payment::whereHas('reservation', function ($query) use ($selectedDate) {
            $query->where([
                ['hostel_office_id', '=', Auth::user()->office_id]
            ])
                ->whereYear('check_in_date', $selectedDate->year)
                ->whereMonth('check_in_date', $selectedDate->month);
        })
            ->sum('amount');

        //For monthly revenue of selected year ($monthlyRevenueYear)
        $monthlyRevenue = [
            ['name' => 'Jan', 'revenue' => 0],
            ['name' => 'Feb', 'revenue' => 0],
            ['name' => 'Mar', 'revenue' => 0],
            ['name' => 'Apr', 'revenue' => 0],
            ['name' => 'May', 'revenue' => 0],
            ['name' => 'Jun', 'revenue' => 0],
            ['name' => 'Jul', 'revenue' => 0],
            ['name' => 'Aug', 'revenue' => 0],
            ['name' => 'Sep', 'revenue' => 0],
            ['name' => 'Oct', 'revenue' => 0],
            ['name' => 'Nov', 'revenue' => 0],
            ['name' => 'Dec', 'revenue' => 0],
        ];

        foreach ($monthlyRevenue as &$monthData) {
            $monthNumber = array_search($monthData['name'], ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) + 1;
            $monthData['revenue'] = Payment::whereHas('reservation', function ($query) use ($monthlyRevenueYear, $monthNumber) {
                $query->where([
                    ['hostel_office_id', '=', Auth::user()->office_id]
                ])
                    ->whereYear('check_in_date', $monthlyRevenueYear->year)
                    ->whereMonth('check_in_date', $monthNumber);
            })
                ->sum('amount');
        }

        return Inertia::render('Admin/Dashboard/Dashboard', [
            'pendingReservationsCount' => $pendingReservationsCount,
            'totalReservationsCount' => $totalReservationsCount,
            'totalGuestsCount' => $totalGuestsCount,
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue
        ]);
    }
}
