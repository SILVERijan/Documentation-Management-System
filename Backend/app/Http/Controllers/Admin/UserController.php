<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Helpers\Flash;

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
                    'permission' => 'write', 
                    'assigned_by' => auth()->id(), 
                    'assigned_at' => now()
                ];
            }
            $user->applications()->sync($applicationsSync);
        }

        Flash::make('User created successfully.');

        return redirect()->back();
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
                    'permission' => 'write', 
                    'assigned_by' => auth()->id(), 
                    'assigned_at' => now()
                ];
            }
            $user->applications()->sync($applicationsSync);
        } else {
             $user->applications()->detach();
        }

        Flash::make('User updated successfully.');

        return redirect()->back();
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            Flash::make('You cannot delete yourself.', 'error');
            return redirect()->back();
        }

        $user->delete();

        Flash::make('User deleted successfully.');

        return redirect()->back();
    }
}
