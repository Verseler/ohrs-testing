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
            'monthly_revenue_year' => ['nullable', 'date']
        ]);

        $monthlyRevenueYear = $request->monthly_revenue_year ?
            Carbon::parse($validated['monthly_revenue_year'])->setTimezone('Asia/Manila') :
            Carbon::now();

        $pendingReservationsCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id],
        ])
            ->whereHas('stayDetails', function ($query) {
                $query->where('status', 'pending');
            })
            ->count();

        $unpaidReservationsCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id],
        ])->where('remaining_balance', '>', 0)->count();

        $overdueCheckinCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id],
        ])
            ->whereHas('stayDetails', function ($query) {
                $query->where('status', 'confirmed');
                $query->where('check_in_date', '<', Carbon::now());
            })
            ->count();

        $overdueCheckoutCount = Reservation::where([
            ['hostel_office_id', '=', Auth::user()->office_id],
        ])
            ->whereHas('stayDetails', function ($query) {
                $query->where('status', 'checked_in');
                $query->where('check_out_date', '<', Carbon::now());
            })
            ->count();

        //For monthly revenue of selected year ($monthlyRevenueYear)
        $monthlyRevenues = [
            ['name' => 'Jan', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Feb', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Mar', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Apr', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'May', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Jun', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Jul', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Aug', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Sep', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Oct', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Nov', 'revenue' => 0, 'collectables' => 0],
            ['name' => 'Dec', 'revenue' => 0, 'collectables' => 0],
        ];

        $runningRevenue = 0;
        $runningCollectables = 0;

        foreach ($monthlyRevenues as &$monthData) {
            $monthNumber = array_search($monthData['name'], ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) + 1;

            // get all collectables per month for selected year
            $monthData['collectables'] = Reservation::select('remaining_balance')
                ->where([
                    ['hostel_office_id', '=', Auth::user()->office_id],
                    ['general_status', '!=', 'pending'],
                    ['general_status', '!=', 'canceled'],
                ])
                ->whereHas('stayDetails', function ($query) use ($monthlyRevenueYear, $monthNumber) {
                    $query->whereYear('check_in_date', $monthlyRevenueYear->year)
                        ->whereMonth('check_in_date', $monthNumber);
                })
                ->sum('remaining_balance');

            // get all revenue per month for selected year
            $monthData['revenue'] = Payment::whereHas('reservation', function ($query) use ($monthlyRevenueYear, $monthNumber) {
                $query->where([
                    ['hostel_office_id', '=', Auth::user()->office_id],
                    ['general_status', '!=', 'pending'],
                    ['general_status', '!=', 'canceled'],
                ])
                    ->whereHas('stayDetails', function ($query) use ($monthlyRevenueYear, $monthNumber) {
                        $query->whereYear('check_in_date', $monthlyRevenueYear->year)
                            ->whereMonth('check_in_date', $monthNumber);
                    });
            })
                ->sum('amount');

            $runningRevenue += $monthData['revenue'];
            $runningCollectables += $monthData['collectables'];
        }

        return Inertia::render('Admin/Dashboard/Dashboard', [
            'pendingReservationsCount' => $pendingReservationsCount,
            'unpaidReservationsCount' => $unpaidReservationsCount,
            'overdueCheckinCount' => $overdueCheckinCount,
            'overdueCheckoutCount' => $overdueCheckoutCount,
            'monthlyRevenues' => $monthlyRevenues,
            'runningRevenue' => $runningRevenue,
            'runningCollectables' => $runningCollectables,
        ]);
    }
}
