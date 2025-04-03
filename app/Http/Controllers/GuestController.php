<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function list(Request $request)
    {
        $request->validate([
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in(['first_name', 'last_name', 'gender', 'office', 'check_in_date', 'check_out_date'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Guest::whereHas('reservation', function ($query) {
            $query->whereNotIn('status', ['pending', 'canceled'])
            ->where('hostel_office_id', Auth::user()->office_id);
        })->with(['reservation']);

        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%");
            });
        }

        // Gender Filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';

            switch ($request->sort_by) {
                case 'check_in_date':
                case 'check_out_date':
                    $query->join('reservations', 'guests.reservation_id', '=', 'reservations.id')
                        ->orderBy("reservations.{$request->sort_by}", $sortOrder)
                        ->select('guests.*');
                    break;

                default:
                    $query->orderBy($request->sort_by, $sortOrder);
                    break;
            }
        }

        $guests = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Guest/GuestList', [
            'guests' => $guests,
            'filters' => $request->only(['search', 'gender', 'sort_by', 'sort_order'])
        ]);
    }
}
