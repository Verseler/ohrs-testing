<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Brick\Math\BigInteger;
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
            $sortBy = in_array($request->sort_by, ['name', 'has_hostel']) ? $request->sort_by : 'name';
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $offices = $query->paginate(10)->withQueryString();


        return Inertia::render("OfficeManagement/OfficeManagement", [
            'offices' => $offices,
            'filters'=> $request->only(['search', 'has_hostel', 'sort_by', 'sort_order'])
        ]);
    }

    public function upsertForm(?int $id = null)
    {
        $office = Office::find($id);
        return Inertia::render("OfficeManagement/UpsertOffice", [
            'office' => $office
        ]);
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'has_hostel' => 'required|boolean',
        ]);

        $office = Office::find($request->id);
        if (!$office) {
            $office = new Office();
        }


        $office->name = $validated['name'];
        $office->has_hostel = $validated['has_hostel'];
        $office->save();

        return redirect()->route('office.list');
    }

    public function delete($id)
    {
        $office = Office::findOrFail($id);
        $office->delete();
        return redirect()->back();
    }
}
