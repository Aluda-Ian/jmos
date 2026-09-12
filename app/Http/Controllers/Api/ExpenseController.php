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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'project' => 'nullable|string',
            'amount' => 'required|numeric',
            'etr' => 'nullable|in:yes,no,na',
            'date' => 'required|string',
        ]);

        $expense = Expense::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense logged.',
            'data' => $expense
        ], 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'category' => 'nullable|string',
            'project' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'etr' => 'nullable|in:yes,no,na',
            'date' => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense updated.',
            'data' => $expense
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense removed.'
        ]);
    }
}
