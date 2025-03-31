<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficeController extends Controller
{
    public function list(Request $request)
    {
        $query = Office::query()->with('region');

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Region Filter
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, ['name', 'region_id']) ? $request->sort_by : 'name';
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $offices = $query->paginate(10)->withQueryString();
        $regions = Region::all();

        return Inertia::render("Admin/Office/OfficeManagement", [
            'offices' => $offices,
            'regions' => $regions,
            'filters' => $request->only(['search', 'has_hostel', 'sort_by', 'sort_order', 'region_id'])
        ]);
    }

    public function upsertForm(?int $id = null)
    {
        $office = Office::find($id);
        $regions = Region::all();
        return Inertia::render("Admin/Office/UpsertOffice", [
            'office' => $office,
            'regions' => $regions
        ]);
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
            'has_hostel' => 'required|boolean',
        ]);

        //verify if selected region exists
        Region::findOrFail($validated['region_id']);

        $office = Office::find($request->id);
        if (!$office) {
            $office = new Office();
        }


        $office->region_id = $validated['region_id'];
        $office->name = $validated['name'];
        $office->has_hostel = $validated['has_hostel'];
        $office->save();

        return redirect()->route('office.list');
    }

    public function delete($id)
    {
        $office = Office::findOrFail($id);

        // Check if there are users connected to the office
        if ($office->users()->exists()) {
            return redirect()->back()->with(['error' => 'Cannot delete office with connected users.']);
        }
        // Check if there are active reservations (not canceled or not checked out)
        if ($office->reservations()->whereNotIn('status', ['canceled', 'checked_out'])->exists()) {
            return redirect()->back()->with(['error' => 'Cannot delete office with active reservations.']);
        }

        $office->delete();
        return redirect()->back();
    }
}
