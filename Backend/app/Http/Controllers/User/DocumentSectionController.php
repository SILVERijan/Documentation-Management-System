<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Helpers\Flash;

class DocumentSectionController extends Controller
{
    /**
     * Show the page to manage sections for a document.
     */
    public function index(Document $document)
    {
        // Only the document creator or admin can manage sections
        Gate::authorize('update', $document);

        $sections = $document->sections()->get()->map(fn($s) => [
            'id'         => $s->id,
            'sub_title'  => $s->sub_title,
            'content'    => $s->content,
            'sort_order' => $s->sort_order,
        ]);

        return Inertia::render('User/Documents/Sections', [
            'document' => [
                'id'    => $document->id,
                'title' => $document->title,
                'slug'  => $document->slug,
            ],
            'sections' => $sections,
        ]);
    }

    /**
     * Store a new section.
     */
    public function store(Request $request, Document $document)
    {
        Gate::authorize('update', $document);

        $validated = $request->validate([
            'sub_title' => 'required|string|max:255',
            'content'   => 'required|string',
        ]);

        $maxOrder = $document->sections()->max('sort_order') ?? 0;

        $document->sections()->create([
            'user_id'    => auth()->id(),
            'sub_title'  => $validated['sub_title'],
            'content'    => $validated['content'],
            'sort_order' => $maxOrder + 1,
        ]);

        Flash::make('Section added successfully.');

        return redirect()->back();
    }

    /**
     * Update an existing section.
     */
    public function update(Request $request, Document $document, DocumentSection $section)
    {
        Gate::authorize('update', $document);

        $validated = $request->validate([
            'sub_title' => 'required|string|max:255',
            'content'   => 'required|string',
        ]);

        $section->update($validated);

        Flash::make('Section updated successfully.');

        return redirect()->back();
    }

    /**
     * Delete a section.
     */
    public function destroy(Document $document, DocumentSection $section)
    {
        Gate::authorize('update', $document);
        $section->delete();

        Flash::make('Section deleted.');

        return redirect()->back();
    }
}
