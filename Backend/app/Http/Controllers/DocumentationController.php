<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Document;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DocumentationController extends Controller
{
    public function show($appSlug, $docSlug = null)
    {
        $application = Application::where('slug', $appSlug)->firstOrFail();

        // Get all published documents for the left nav bar (Select only necessary columns)
        $documents = Document::where('application_id', $application->id)
                             ->where('status', 'published')
                             ->select(['id', 'title', 'slug', 'status', 'application_id', 'sort_order', 'created_at'])
                             ->orderBy('sort_order', 'asc')
                             ->orderBy('created_at', 'asc')
                             ->get();

        if ($documents->isEmpty()) {
            return Inertia::render('Documentation/Show', [
                'application'     => $application,
                'documents'       => [],
                'currentDocument' => null,
                'toc'             => [],
            ]);
        }

        if ($docSlug) {
            $currentDocMeta = $documents->firstWhere('slug', $docSlug);
            if (!$currentDocMeta) {
                abort(404, 'Document not found.');
            }
        } else {
            $currentDocMeta = $documents->first();
            return redirect()->route('app.show.doc', ['appSlug' => $appSlug, 'docSlug' => $currentDocMeta->slug]);
        }

        // Fetch full document with content
        $document = Document::with('attachments')->findOrFail($currentDocMeta->id);

        // Load sections
        $sections = $document->sections()->get(['id', 'sub_title', 'content', 'level', 'sort_order']);

        // Build TOC from sections' sub_titles
        $toc = $sections->map(fn($s) => [
            'id'    => $this->makeAnchor($s->sub_title),
            'title' => $s->sub_title,
            'level' => $s->level,
        ])->values()->toArray();

        // Convert main document content markdown -> HTML
        $mainContent = \Illuminate\Support\Str::markdown($document->content ?? '');

        // Convert each section content markdown -> HTML
        $renderedSections = $sections->map(fn($s) => [
            'id'       => $this->makeAnchor($s->sub_title),
            'sub_title' => $s->sub_title,
            'content'  => \Illuminate\Support\Str::markdown($s->content),
            'level'    => $s->level,
        ])->values()->toArray();

        return Inertia::render('Documentation/Show', [
            'application'     => $application,
            'documents'       => $documents->map(fn($d) => [
                'id'    => $d->id,
                'title' => $d->title,
                'slug'  => $d->slug,
                'status' => $d->status,
            ]),
            'currentDocument' => [
                'id'         => $document->id,
                'title'      => $document->title,
                'content'    => $mainContent,
                'updated_at' => $document->updated_at,
                'status'     => $document->status,
                'attachments' => $document->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'file_size' => $a->file_size,
                    'file_type' => $a->file_type,
                ]),
            ],
            'sections'        => $renderedSections,
            'toc'             => $toc,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = Document::where('status', 'published')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['application' => function($q) {
                $q->select('id', 'name', 'slug', 'color');
            }])
            ->select(['id', 'title', 'slug', 'application_id', 'status'])
            ->limit(8)
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'slug' => $doc->slug,
                    'app_name' => $doc->application->name,
                    'app_slug' => $doc->application->slug,
                    'app_color' => $doc->application->color ?? 'bg-indigo-500',
                ];
            });

        return response()->json($results);
    }

    private function makeAnchor(string $text): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    }

   

}
