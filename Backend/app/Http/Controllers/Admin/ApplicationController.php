<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::withCount('documents')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;

        while (Application::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Application::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => 'grid', // Default icon
            'color' => '#6366f1', // Default hex color
            'status' => $request->status,
            'sort_order' => 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Application created successfully.');
    }

    public function update(Request $request, Application $application)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->name !== $application->name) {
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;

            while (Application::where('slug', $slug)->where('id', '!=', $application->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $application->slug = $slug;
        }

        $application->name = $request->name;
        $application->description = $request->description;
        $application->status = $request->status;
        $application->save();

        return redirect()->back()->with('success', 'Application updated successfully.');
    }

    public function destroy(Application $application)
    {
        $application->delete();

        return redirect()->back()->with('success', 'Application deleted successfully.');
    }
}
