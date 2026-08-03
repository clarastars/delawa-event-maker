<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->get();

        return view('admin.team.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.team.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        User::create($validated);

        return redirect()->route('admin.team.index')->with('status', 'Team member created successfully.');
    }

    public function edit(User $team): View
    {
        return view('admin.team.edit', ['user' => $team]);
    }

    public function update(Request $request, User $team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($team->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $team->update($validated);

        return redirect()->route('admin.team.index')->with('status', 'Team member updated successfully.');
    }

    public function destroy(User $team): RedirectResponse
    {
        if (auth()->id() === $team->id) {
            return back()->with('status', 'You cannot delete yourself.');
        }

        $team->delete();

        return redirect()->route('admin.team.index')->with('status', 'Team member deleted successfully.');
    }
}
