<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $userId = Auth::user()->id;
            $categories = Category::where('user_id', $userId)->orWhereNull('user_id')->get();


            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong please refresh'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'color' => 'required|string',
                'icon' => 'required|json'
            ]);

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message'  => 'Category created successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong' . $e,
            ]);
        }
    }
}
