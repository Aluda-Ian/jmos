<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Expense::latest()->get());
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $expense,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'project' => 'nullable|string|max:255',
            'amount' => 'required|numeric',
            'etr' => 'nullable|in:yes,no,na',
            'etims_number' => 'nullable|string|max:100',
            'receipt_url' => 'nullable|string',
            'receipt_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|string|max:100',
            'receipt' => 'nullable|file|max:25600',
        ]);

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $uploadDir = public_path('uploads/expenses');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = uniqid('exp_', true).'.'.($extension ?: 'bin');
            $file->move($uploadDir, $safeName);

            $validated['receipt_url'] = asset('uploads/expenses/'.$safeName);
            $validated['receipt_name'] = $originalName;
            unset($validated['receipt']);
        }

        $expense = Expense::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense logged successfully.',
            'data' => $expense,
        ], 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'project' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'etr' => 'nullable|in:yes,no,na',
            'etims_number' => 'nullable|string|max:100',
            'receipt_url' => 'nullable|string',
            'receipt_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|max:25600',
        ]);

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $uploadDir = public_path('uploads/expenses');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = uniqid('exp_', true).'.'.($extension ?: 'bin');
            $file->move($uploadDir, $safeName);

            $validated['receipt_url'] = asset('uploads/expenses/'.$safeName);
            $validated['receipt_name'] = $originalName;
            unset($validated['receipt']);
        }

        $expense->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense updated successfully.',
            'data' => $expense,
        ]);
    }

    public function uploadReceipt(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:25600', // 25MB max
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        $isImage = str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $uploadDir = public_path('uploads/expenses');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = uniqid('exp_', true).'.'.($extension ?: 'bin');
        $file->move($uploadDir, $safeName);

        $url = asset('uploads/expenses/'.$safeName);

        return response()->json([
            'status' => 'success',
            'receipt_name' => $originalName,
            'receipt_url' => $url,
            'is_image' => $isImage,
            'size' => $file->getSize() ?: 0,
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense removed.',
        ]);
    }
}
