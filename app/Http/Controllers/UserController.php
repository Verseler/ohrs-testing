<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Region;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function list(Request $request)
    {
        $this->authorize('view', User::class);

        $query = User::query()->with('office.region');

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Role Filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Region Filter
        if ($request->filled('region_id')) {
            $query->whereHas('office', function ($q) use ($request) {
                $q->where('region_id', $request->region_id);
            });
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, ['name', 'email', 'role']) ? $request->sort_by : 'name';
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $users = $query->paginate(10)->withQueryString();
        $regions = Region::all();

        return Inertia::render('Admin/User/UserManagement', [
            'users' => $users,
            'regions' => $regions,
            'filters' => $request->only(['search', 'role', 'region_id', 'sort_by', 'sort_order'])
        ]);
    }

    public function createForm()
    {
        $this->authorize('create', User::class);

        $regions = Region::all();
        $offices = Office::all();

        return Inertia::render('Admin/User/CreateUser', [
            'regions' => $regions,
            'offices' => $offices
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:20'],
                'email' => ['required', 'email', 'unique:users'],
                'office_id' => ['required', 'exists:offices,id'],
                'role' => ['required', Rule::in(['admin', 'super_admin'])],
                'password' => [
                    'required',
                    'string',
                    Rules\Password::defaults()
                ],
                'confirm_password' => ['required', 'same:password']
            ]
        );

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'office_id' => $validated['office_id'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return to_route('user.list');
    }

    public function editForm(int $id)
    {
        $this->authorize('update', User::class);

        $user = User::with('office')->findOrFail($id);
        $regions = Region::all();
        $offices = Office::all();

        return Inertia::render('Admin/User/EditUser', [
            'user' => $user,
            'regions' => $regions,
            'offices' => $offices
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('update', User::class);

        $validated = $request->validate(
            [
                'id' => ['required', 'exists:users,id'],
                'name' => ['required', 'string', 'max:20'],
                'office_id' => ['required', 'exists:offices,id'],
                'role' => ['required', Rule::in(['admin', 'super_admin'])],
            ]
        );

        try {
            DB::transaction(function () use ($validated) {
                $user = User::findOrFail($validated['id']);
                $user->name = $validated['name'];
                $user->office_id = $validated['office_id'];
                $user->role = $validated['role'];
                $user->save();
            });
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return to_route('user.list')->with('success', 'Successfully updated the user details.');
    }

    public function delete(int $id)
    {
        $this->authorize('delete', User::class);

        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->back()->with('success', 'Successfully deleted the user account.');
    }
}
