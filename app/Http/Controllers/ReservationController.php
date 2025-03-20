<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function list(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending', 'checked_in', 'checked_out', 'canceled'])],
            'balance' => ['nullable', Rule::in(['paid', 'has_balance'])],
            'sort_by' => ['nullable', Rule::in(['reservation_code', 'first_name', 'check_in_date', 'check_out_date', 'total_billing', 'remaining_balance', 'status'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'guestOffice', 'hostelOffice'])
            //Make sure that the reservations is ony accessible by authorized admin
            ->where('hostel_office_id', Auth::user()->office_id)
            //Make sure to not include the pending reservations because it has dedicated page for that (Waiting List Pages)
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
            'filters' => $request->only(['search', 'status', 'balance', 'sort_by', 'sort_order'])
        ]);
    }

    public function show(int $id)
    {
        $reservation = Reservation::with([
            'guestOffice.region',
            'hostelOffice.region',
            'reservedBeds'
        ])->where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);

        return Inertia::render("Admin/Reservation/ReservationDetails/ReservationDetails", [
            'reservation' => $reservation,
        ]);
    }

    public function extendForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Reservation/ReservationExtendForm', [
            'reservation' => $reservation
        ]);
    }

    public function editStatusForm(int $id)
    {
        return Inertia::render('Admin/Reservation/EditReservationStatus');
    }

    public function editBedAssignmentForm(int $id)
    {
        return Inertia::render('Admin/Reservation/EditBedAssignment');
    }

    public function checkStatusForm()
    {
        return Inertia::render('Guest/CheckReservationStatus/CheckReservationStatus', [
            'canLogin' => Route::has('login'),
        ]);
    }

    public function checkStatus(string $code)
    {
        $reservation = Reservation::where('reservation_code', $code)->first();

        if (!$reservation) {
            return redirect()->back()->with('error','Reservation doesn\'t exist.');
        }

        return Inertia::render('Guest/CheckReservationStatus/ReservationStatusResult', [
            'reservation' => $reservation,
            'canLogin' => Route::has('login'),
        ]);
    }
}
