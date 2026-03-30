<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('applications')->orderBy('created_at', 'desc')->get();
        $applications = Application::select('id', 'name')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'applications' => $applications
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
            'applications' => 'array'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        if ($request->has('applications')) {
            $applicationsSync = [];
            foreach ($request->applications as $appId) {
                $applicationsSync[$appId] = [
                    'permission' => 'read', 
                    'assigned_by' => auth()->id(), 
                    'assigned_at' => now()
                ];
            }
            $user->applications()->sync($applicationsSync);
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class.',email,'.$user->id,
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
            'applications' => 'array'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->status = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('applications')) {
            $applicationsSync = [];
            foreach ($request->applications as $appId) {
                $applicationsSync[$appId] = [
                    'permission' => 'read', 
                    'assigned_by' => auth()->id(), 
                    'assigned_at' => now()
                ];
            }
            $user->applications()->sync($applicationsSync);
        } else {
             $user->applications()->detach();
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
