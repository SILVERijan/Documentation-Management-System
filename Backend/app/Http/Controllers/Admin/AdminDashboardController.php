<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Application;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalDocuments = Document::count();
        
        // Documents per application
        $applicationsData = Application::withCount('documents')->get()->map(function ($app) {
            return [
                'label' => $app->name,
                'count' => $app->documents_count,
            ];
        });

        // Recent documents
        $recentDocuments = Document::with(['application', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'app' => $doc->application ? $doc->application->name : 'N/A',
                    'author' => $doc->user ? $doc->user->name : 'Unknown',
                    'time' => $doc->created_at->diffForHumans(),
                    'status' => $doc->status,
                ];
            });

        $applications = Application::all()->map(function ($app) {
            return [
                'name' => $app->name,
                'slug' => $app->slug,
                'color' => $app->color ?? 'bg-indigo-500',
            ];
        });

        return Inertia::render('AdminDashboard', [
            'stats' => [
                'totalUsers' => number_format($totalUsers),
                'totalDocuments' => number_format($totalDocuments),
                'activeApplications' => Application::count(),
                'uptime' => '99.9%', // Placeholder for now
            ],
            'applications' => $applications,
            'docsPerApp' => $applicationsData,
            'recentDocuments' => $recentDocuments,
        ]);
    }
}
