<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficeController extends Controller
{
    public function list(Request $request)
    {
        $query = Office::query();

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, ['name']) ? $request->sort_by : 'name';
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $offices = $query->paginate(10)->withQueryString();

        return Inertia::render("Admin/Office/OfficeManagement", [
            'offices' => $offices,
            'filters' => $request->only(['search', 'has_hostel', 'sort_by', 'sort_order'])
        ]);
    }

    public function upsertForm(?int $id = null)
    {
        $office = Office::find($id);
        return Inertia::render("Admin/Office/UpsertOffice", [
            'office' => $office
        ]);
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'has_hostel' => 'required|boolean',
            'hostel_name' => 'nullable|string|max:255',
        ]);

        $office = Office::find($request->id);
        if (!$office) {
            $office = new Office();
        }

        $office->name = $validated['name'];
        $office->has_hostel = $validated['has_hostel'];
        $office->hostel_name = $validated['hostel_name'] ?? null;
        $office->save();

        return redirect()->route('office.list')->with(['success' => 'Office saved successfully.']);
    }

    public function delete($id)
    {
        $office = Office::findOrFail($id);

        // Check if there are users connected to the office
        if ($office->users()->exists()) {
            return redirect()->back()->with(['error' => 'Cannot delete office with connected users.']);
        }
        // Check if there are active reservations (not canceled or not checked out)
        if ($office->reservations()->whereNotIn('general_status', ['canceled', 'checked_out'])->exists()) {
            return redirect()->back()->with(['error' => 'Cannot delete office with active reservations.']);
        }

        $office->delete();
        return redirect()->back()->with(['success' => 'Office deleted successfully.']);
    }
}
