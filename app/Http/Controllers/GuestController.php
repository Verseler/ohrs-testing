<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function list(Request $request)
    {
        $request->validate([
            'region_id' => ['nullable', 'exists:regions,id'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in(['first_name', 'last_name', 'gender', 'office_id', 'check_in_date', 'check_out_date', 'phone', 'region_id'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Guest::whereHas('reservation', function ($query) {
            $query->whereNotIn('status', ['pending', 'canceled'])
            ->where('hostel_office_id', Auth::user()->office_id);
        })->with(['office.region', 'reservation']);

        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%");
            });
        }

        // Region Filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Region Filter
        if ($request->filled('region_id')) {
            $query->whereHas('office.region', function ($query) use ($request) {
                $query->where('regions.id', $request->region_id);
            });
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';

            switch ($request->sort_by) {
                case 'office_id':
                    $query->join('offices', 'guests.office_id', '=', 'offices.id')
                        ->orderBy('offices.name', $sortOrder)
                        ->select('guests.*');
                    break;

                case 'region_id':
                    $query->join('offices', 'guests.office_id', '=', 'offices.id')
                        ->join('regions', 'offices.region_id', '=', 'regions.id')
                        ->orderBy('regions.name', $sortOrder)
                        ->select('guests.*');
                    break;

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
        $regions = Region::all();

        return Inertia::render('Admin/Guest/GuestList', [
            'guests' => $guests,
            'regions' => $regions,
            'filters' => $request->only(['search', 'gender', 'sort_by', 'sort_order', 'region_id'])
        ]);
    }
}
