<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarkdownImportController extends Controller
{
    /**
     * Parse an uploaded .md file and return structured data
     * (title, intro content, sections) — nothing is saved to DB here,
     * we just return the parsed structure so the frontend can auto-fill the form.
     */
    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:md,txt|max:2048',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());

        // Normalize line endings
        $content = str_replace("\r\n", "\n", $content);

        $lines   = explode("\n", $content);
        $title   = '';
        $intro   = [];
        $sections = [];

        $currentSection = null;
        $inIntro        = true;   // everything before the first H2
        $foundH1        = false;

        foreach ($lines as $line) {
            // H1 → document title
            if (!$foundH1 && preg_match('/^#\s+(.+)$/', $line, $m)) {
                $title    = trim($m[1]);
                $foundH1  = true;
                continue;
            }

            // H2 → new section
            if (preg_match('/^##\s+(.+)$/', $line, $m)) {
                // Save previous section
                if ($currentSection !== null) {
                    $sections[] = [
                        'sub_title' => $currentSection['sub_title'],
                        'content'   => trim(implode("\n", $currentSection['lines'])),
                    ];
                }
                $currentSection = ['sub_title' => trim($m[1]), 'lines' => []];
                $inIntro        = false;
                continue;
            }

            // Collect lines
            if ($inIntro) {
                $intro[] = $line;
            } elseif ($currentSection !== null) {
                $currentSection['lines'][] = $line;
            }
        }

        // Push last section
        if ($currentSection !== null) {
            $sections[] = [
                'sub_title' => $currentSection['sub_title'],
                'content'   => trim(implode("\n", $currentSection['lines'])),
            ];
        }

        $introText = trim(implode("\n", $intro));

        // If no H1 was found, try using the filename as title
        if ($title === '') {
            $title = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
            $title = ucwords(str_replace(['-', '_'], ' ', $title));
        }

        return response()->json([
            'title'    => $title,
            'content'  => $introText,
            'sections' => $sections,
        ]);
    }
}