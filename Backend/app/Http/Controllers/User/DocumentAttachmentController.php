<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Helpers\Flash;

class DocumentAttachmentController extends Controller
{
    /**
     * Store a new attachment for the document.
     */
    public function store(Request $request, Document $document)
    {
        Gate::authorize('update', $document);

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB limit
        ]);

        $file = $request->file('file');
        
        // Store the file in a non-public directory
        $path = $file->store('attachments', 'local');

        $attachment = DocumentAttachment::create([
            'document_id'   => $document->id,
            'uploaded_by'   => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'disk'          => 'local',
            'file_type'     => $file->getClientOriginalExtension(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        Flash::make('File attached successfully.');

        return redirect()->back();
    }

    /**
     * Download the attachment.
     */
    public function download(DocumentAttachment $attachment)
    {
        // For downloads, we check if the associated document is published 
        // OR if the user has permission to update it (author/admin).
        $document = $attachment->document;
        
        if ($document->status !== 'published') {
            Gate::authorize('update', $document);
        }

        if (!Storage::disk($attachment->disk)->exists($attachment->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->file_path, 
            $attachment->original_name
        );
    }

    /**
     * Remove the attachment.
     */
    public function destroy(DocumentAttachment $attachment)
    {
        Gate::authorize('update', $attachment->document);

        // Delete from storage
        if (Storage::disk($attachment->disk)->exists($attachment->file_path)) {
            Storage::disk($attachment->disk)->delete($attachment->file_path);
        }

        // Delete from database
        $attachment->delete();

        Flash::make('Attachment removed.');

        return redirect()->back();
    }
}
