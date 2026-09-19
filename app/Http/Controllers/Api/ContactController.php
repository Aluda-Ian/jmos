<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::with(['client', 'lead'])->latest();

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('contact_name', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'contact_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'owner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['contact_name'])) {
            $nameParts = array_filter([$validated['first_name'] ?? '', $validated['last_name'] ?? '']);
            $validated['contact_name'] = ! empty($nameParts) ? implode(' ', $nameParts) : 'New Contact';
        }

        $contact = Contact::create($validated);

        AuditLog::record('CREATE', "Created contact person '{$contact->contact_name}'", 'Contact', $contact->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact created successfully.',
            'data' => $contact->load(['client', 'lead']),
        ], 201);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'contact_name' => 'sometimes|required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'owner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        AuditLog::record('UPDATE', "Updated contact person '{$contact->contact_name}'", 'Contact', $contact->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact updated.',
            'data' => $contact->fresh(['client', 'lead']),
        ]);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $name = $contact->contact_name;
        $id = $contact->id;

        $contact->delete();

        AuditLog::record('DELETE', "Removed contact '{$name}'", 'Contact', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact removed.',
        ]);
    }
}
