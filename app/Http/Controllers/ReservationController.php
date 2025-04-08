<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
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
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'checked_in', 'checked_out', 'canceled'])],
            'balance' => ['nullable', Rule::in(['paid', 'has_balance'])],
            'sort_by' => ['nullable', Rule::in(['reservation_code', 'first_name', 'check_in_date', 'check_out_date', 'total_billings', 'remaining_balance', 'status'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'hostelOffice'])
            //Make sure that the reservations is ony accessible by authorized admin
            ->where('hostel_office_id', Auth::user()->office_id)
            //Make sure to not include the pending reservations because it has a dedicated page for that. (Waiting List Page)
            ->whereNotIn('status', ['pending']);


        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('reservation_code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && in_array($request->status, ['confirmed', 'checked_in', 'checked_out', 'canceled'])) {
            $query->where('status', $request->status);
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
                'reservation_code',
                'first_name',
                'check_in_date',
                'check_out_date',
                'total_billings',
                'remaining_balance',
                'status'
            ]) ? $request->sort_by : 'reservation_code';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $reservations = $query->paginate(10);

        return Inertia::render("Admin/Reservation/ReservationManagement", [
            'reservations' => $reservations,
            'filters' => $request->only(['search', 'status', 'balance', 'payment_type', 'sort_by', 'sort_order'])
        ]);
    }

    public function show(int $id)
    {
        $reservation = Reservation::with([
            'guests',
            'hostelOffice.region',
            'reservedBeds.room.eligibleGenderSchedules',
            'reservedBedsWithGuests'
        ])->where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);

        $this->authorize('view', $reservation);

        $isSuperAdmin = Auth::user()->role === 'super_admin';
        $hasRemainingBalance = $reservation->remaining_balance > 0;
        $validReservationStatus = $reservation->status !== 'pending'
            && $reservation->status !== 'checked_out'
            && $reservation->status !== 'canceled';

        $canExempt = $isSuperAdmin && $hasRemainingBalance && $validReservationStatus;

        return Inertia::render("Admin/Reservation/ReservationDetails/ReservationDetails", [
            'reservation' => $reservation,
            'canExempt' => $canExempt
        ]);
    }


    //Waiting list page
    public function waitingList(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in(['reservation_code', 'created_at', 'first_name', 'check_in_date', 'check_out_date'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'hostelOffice'])
            //Make sure that the reservations is only accessible by authorized admin
            ->where('hostel_office_id', Auth::user()->office_id)
            //Make sure the reservation status is pending
            ->whereIn('status', ['pending']);


        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('reservation_code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Sorting updates
        $sortBy = $request->filled('sort_by') && in_array($request->sort_by, [
            'reservation_code',
            'created_at',
            'first_name',
            'check_in_date',
            'check_out_date',
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
