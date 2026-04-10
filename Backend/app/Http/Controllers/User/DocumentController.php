<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Document::with(['application', 'user', 'updater']);

        // Admin sees all documents, standard users see only their own
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest()->paginate(10)->through(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'application' => $doc->application ? [
                    'name' => $doc->application->name,
                    'slug' => $doc->application->slug,
                    'color' => $doc->application->color ?? 'bg-indigo-500',
                ] : null,
                'author' => $doc->user ? $doc->user->name : 'Unknown',
                'updater' => $doc->updater ? $doc->updater->name : null,
                'status' => $doc->status,
                'version' => $doc->version,
                'view_count' => $doc->view_count,
                'updated_at' => $doc->updated_at->diffForHumans(),
            ];
        });

        // Stats should also reflect visibility
        $statsQuery = Document::query();
        if (!$user->isAdmin()) {
            $statsQuery->where('user_id', $user->id);
        }

        return Inertia::render('User/Documents/Index', [
            'documents' => $documents,
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'published' => (clone $statsQuery)->where('status', 'published')->count(),
                'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Document::class);

        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $applications = Application::all();
        } else {
            // Show all assigned applications so user can select which one to write for
            $applications = $user->applications()->get();
        }

        return Inertia::render('User/Documents/Create', [
            'assignableApplications' => $applications->map(fn($app) => ['id' => $app->id, 'name' => $app->name])
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Document::class);

        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'sub_title'            => 'nullable|string|max:255',
            'application_id'       => 'required|exists:applications,id',
            'content'              => 'required|string',
            'status'               => 'required|in:draft,published',
            'sections'             => 'nullable|array',
            'sections.*.sub_title' => 'required_with:sections|string|max:255',
            'sections.*.content'   => 'required_with:sections|string',
            'sections.*.level'     => 'required_with:sections|integer|in:2,3',
        ]);
        
        $user = auth()->user();
        if (!$user->isAdmin()) {
            // Check user is assigned to this application (any permission level)
            $hasAccess = $user->applications()
                ->where('applications.id', $validated['application_id'])
                ->exists();
            if (!$hasAccess) {
                abort(403, 'You are not assigned to this application.');
            }
        }

        $validated['slug'] = Str::slug($validated['title']);
        $validated['user_id'] = $user->id;
        $validated['updated_by'] = $user->id;

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $document = Document::create($validated);

        // Save sections if provided (e.g. from markdown import)
        if (!empty($validated['sections'])) {
            $lastParentId = null;

            foreach ($validated['sections'] as $index => $sectionData) {
                $level = $sectionData['level'] ?? 2;
                
                $section = $document->sections()->create([
                    'user_id'    => $user->id,
                    'sub_title'  => $sectionData['sub_title'],
                    'content'    => $sectionData['content'],
                    'level'      => $level,
                    'parent_id'  => ($level === 3) ? $lastParentId : null,
                    'sort_order' => $index + 1,
                ]);

                if ($level === 2) {
                    $lastParentId = $section->id;
                }
            }
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.documents.index')->with('success', 'Document created successfully.');
        }

        return redirect()->route('user.documents.index')->with('success', 'Document created successfully.');
    }

    public function edit(Document $document)
    {
        Gate::authorize('update', $document);

        $user = auth()->user();
        if ($user->isAdmin()) {
            $applications = Application::all();
        } else {
            $applications = $user->applications()->wherePivotIn('permission', ['write', 'admin'])->get();
        }

        return Inertia::render('User/Documents/Edit', [
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'sub_title' => $document->sub_title,
                'application_id' => $document->application_id,
                'content' => $document->content,
                'status' => $document->status,
                'attachments' => $document->attachments()->get()->map(fn($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'file_size' => $a->file_size,
                    'file_type' => $a->file_type,
                    'created_at' => $a->created_at->diffForHumans(),
                ]),
            ],
            'assignableApplications' => $applications->map(fn($app) => ['id' => $app->id, 'name' => $app->name])
        ]);
    }

    public function update(Request $request, Document $document)
    {
        Gate::authorize('update', $document);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'application_id' => 'required|exists:applications,id',
            'content'        => 'required|string',
            'status'         => 'required|in:draft,published',
        ]);

        $user = auth()->user();
        if (!$user->isAdmin() && $document->application_id != $validated['application_id']) {
            $hasAccess = $user->applications()
                ->where('applications.id', $validated['application_id'])
                ->wherePivotIn('permission', ['write', 'admin'])
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($document->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        $validated['updated_by'] = $user->id;
        $validated['version'] = $document->version + 1;
        
        if ($validated['status'] === 'published' && $document->status !== 'published') {
            $validated['published_at'] = now();
        }

        $document->update($validated);

        if ($user->isAdmin()) {
            return redirect()->route('admin.documents.index')->with('success', 'Document updated successfully.');
        }

        return redirect()->route('user.documents.index')->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);
        $document->delete();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}