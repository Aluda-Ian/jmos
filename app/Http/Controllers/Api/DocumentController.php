<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['lead', 'client', 'project'])->latest();

        if ($request->filled('folder') && $request->string('folder') !== 'all') {
            $query->where('folder', $request->string('folder'));
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('file_name', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->integer('lead_id'));
        }

        $allDocs = Document::all();
        $folderCounts = [
            'all' => $allDocs->count(),
            'contracts' => $allDocs->where('folder', 'contracts')->count(),
            'proposals' => $allDocs->where('folder', 'proposals')->count(),
            'brand_guides' => $allDocs->where('folder', 'brand_guides')->count(),
            'briefs' => $allDocs->where('folder', 'briefs')->count(),
            'grants' => $allDocs->where('folder', 'grants')->count(),
            'general' => $allDocs->where('folder', 'general')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
            'counts' => $folderCounts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'folder' => 'required|string|in:contracts,proposals,brand_guides,briefs,grants,general',
            'file' => 'nullable|file|max:51200', // 50MB
            'external_url' => 'nullable|url|max:1000',
            'file_type' => 'nullable|string|max:50',
            'lead_id' => 'nullable|exists:leads,id',
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = 0;
        $fileType = $validated['file_type'] ?? 'pdf';

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $fileName = $uploaded->getClientOriginalName();
            $fileSize = $uploaded->getSize();
            $fileType = strtolower($uploaded->getClientOriginalExtension());
            $filePath = $uploaded->store('documents', 'public');
        } elseif (! empty($validated['external_url'])) {
            $fileName = 'Cloud Link';
            $fileType = 'url';
        }

        $doc = Document::create([
            'title' => $validated['title'],
            'folder' => $validated['folder'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'external_url' => $validated['external_url'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'uploaded_by' => auth('sanctum')->user()?->name ?? 'Barny Kiome',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record('UPLOAD', "Uploaded document '{$doc->title}' to {$doc->folder} folder", 'Document', $doc->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Document '{$doc->title}' added to {$doc->folder}.",
            'data' => $doc->load(['lead', 'client', 'project']),
        ], 201);
    }

    public function download(Document $document): BinaryFileResponse|JsonResponse
    {
        if ($document->external_url) {
            return response()->json(['url' => $document->external_url]);
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document file not found on server.',
            ], 404);
        }

        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name ?? $document->title);
    }

    public function destroy(Document $document): JsonResponse
    {
        $title = $document->title;
        $id = $document->id;

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        AuditLog::record('DELETE', "Removed document '{$title}'", 'Document', $id);

        return response()->json([
            'status' => 'success',
            'message' => "Document '{$title}' deleted.",
        ]);
    }
}
