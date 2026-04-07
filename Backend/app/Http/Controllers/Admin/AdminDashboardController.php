<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Application;
use Illuminate\Http\Request;
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
                'color' => $app->color ?? 'indigo',
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
                    'appColor' => $doc->application ? ($doc->application->color ?? 'indigo') : 'gray',
                    'author' => $doc->user ? $doc->user->name : 'Unknown',
                    'time' => $doc->created_at->diffForHumans(),
                    'status' => $doc->status,
                ];
            });

        return Inertia::render('AdminDashboard', [
            'stats' => [
                'totalUsers' => number_format($totalUsers),
                'totalDocuments' => number_format($totalDocuments),
                'activeApplications' => $applicationsData->count(),
                'uptime' => '99.9%',
            ],
            'docsPerApp' => $applicationsData,
            'docStatusDistribution' => Document::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->mapWithKeys(fn($count, $status) => [ucfirst($status) => $count])
                ->all(),
            'recentDocuments' => $recentDocuments,
        ]);
    }

    public function activity(Request $request)
    {
        $activities = Document::with(['application', 'user', 'updater'])
            ->latest('updated_at')
            ->paginate(15)
            ->through(function ($doc) {
                // Determine the action type
                $isNew = $doc->created_at->eq($doc->updated_at);
                $action = $isNew ? 'created' : 'updated';
                
                return [
                    'id' => $doc->id,
                    'type' => 'document',
                    'action' => $action,
                    'title' => $doc->title,
                    'document_slug' => $doc->slug,
                    'app' => $doc->application ? $doc->application->name : 'N/A',
                    'app_slug' => $doc->application ? $doc->application->slug : 'unassigned',
                    'appColor' => $doc->application ? ($doc->application->color ?? 'indigo') : 'gray',
                    'user' => $doc->updater ? $doc->updater->name : ($doc->user ? $doc->user->name : 'Unknown'),
                    'time' => $doc->updated_at->diffForHumans(),
                    'timestamp' => $doc->updated_at->toDateTimeString(),
                    'status' => $doc->status,
                ];
            });

        return Inertia::render('Admin/ActivityLog', [
            'activities' => $activities,
        ]);
    }
}
