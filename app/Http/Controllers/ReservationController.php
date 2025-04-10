<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\StayDetails;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    //Reservation Management
    public function list(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'general_status' => ['nullable', Rule::in(['pending', 'confirmed', 'checked_in', 'checked_out', 'canceled'])],
            'balance' => ['nullable', Rule::in(['paid', 'has_balance'])],
            'sort_by' => ['nullable', Rule::in(['code', 'first_name', 'check_in_date', 'check_out_date', 'total_billings', 'remaining_balance', 'general_status'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'hostelOffice', 'stayDetails'])
            ->where('hostel_office_id', Auth::user()->office_id)
            ->whereNot('general_status', 'pending')
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ])->orderBy('general_status', 'desc');

        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Status Filter
        if ($request->filled('general_status') && in_array($request->general_status, ['confirmed', 'checked_in', 'checked_out', 'canceled'])) {
            $query->where('general_status', $request->general_status);
        }

        // Balance Filter
        if ($request->filled('balance')) {
            match ($request->balance) {
                'paid' => $query->where('remaining_balance', 0),
                'has_balance' => $query->where('remaining_balance', '>', 0),
            };
        }

        // Payment Type Filter
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Sorting updates
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, [
                'code',
                'first_name',
                'check_in_date',
                'check_out_date',
                'total_billings',
                'remaining_balance',
                'general_status'
            ]) ? $request->sort_by : 'code';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $reservations = $query->paginate(10);

        return Inertia::render("Admin/Reservation/ReservationManagement", [
            'reservations' => $reservations,
            'filters' => $request->only(['search', 'general_status', 'balance', 'payment_type', 'sort_by', 'sort_order'])
        ]);
    }

    public function show(int $id)
    {
        $reservation = Reservation::with([
            'guests',
            'hostelOffice.region',
            'reservedBedsWithGuests'
        ])
        ->withCount([
            'stayDetails as confirmed_count' => function($query) {
                $query->where('status', 'confirmed');
            },
            'stayDetails as checked_in_count' => function($query) {
                $query->where('status', 'checked_in');
            },
            'stayDetails as checked_out_count' => function($query) {
                $query->where('status', 'checked_out');
            },
            'stayDetails as canceled_count' => function($query) {
                $query->where('status', 'canceled');
            }
        ])
        ->whereNot('general_status', 'pending')
        ->where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);

        $isSuperAdmin = Auth::user()->role === 'super_admin';
        $hasRemainingBalance = $reservation->remaining_balance > 0;

        $canExempt = $isSuperAdmin && $hasRemainingBalance && ($reservation->confirmed_count > 0 || $reservation->checked_in_count > 0);


        return Inertia::render("Admin/Reservation/ReservationDetails/ReservationDetails", [
            'reservation' => $reservation,
            'canExempt' => $canExempt,
        ]);
    }


    //Waiting list page
    public function waitingList(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in(['code', 'created_at', 'first_name', 'min_check_in_date', 'max_check_out_date'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'hostelOffice', 'stayDetails'])
            ->where('hostel_office_id', Auth::user()->office_id)
            ->where('general_status', 'pending')
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ]);

        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Sorting updates
        $sortBy = $request->filled('sort_by') && in_array($request->sort_by, [
            'code',
            'created_at',
            'first_name',
            'min_check_in_date',
            'max_check_out_date',
        ]) ? $request->sort_by : 'created_at';

        $sortOrder = $request->filled('sort_order') && $request->sort_order === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $reservations = $query->paginate(10);

        return Inertia::render('Admin/WaitingList/WaitingList', [
            'reservations' => $reservations,
            'filters' => $request->only(['search', 'sort_by', 'sort_order'])
        ]);
    }
}
