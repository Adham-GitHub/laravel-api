<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function createCategory(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required | string | unique:categories',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validation->errors(),
            ], 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => str_replace(' ', '-', strtolower($request->name)),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }


    public function allCategories()
    {
        return response()->json(Category::all(), 200);
    }


    public function oneCategory(Request $request)
    {
        $category = Category::whereSlug(strtolower($request->slug))
            ->first();

        if ($category) {
            return response()->json([
                'category' => $category,
                'products' => Product::whereCategoryId($category->id)
                    ->get()
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Category not found'
        ], 404);
    }


    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);

        if ($category) {
            $validation = Validator::make($request->all(), [
                'name' => 'required | string | unique:categories'
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors(),
                ], 400);
            }

            $category->update([
                'name' => $request->name,
                'slug' => str_replace(' ', '-', strtolower($request->name)),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'The category has been updated',
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Category not found',
        ], 404);
    }


    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if ($category) {
            $category->delete();

            return response()->json([
                'status' => true,
                'message' => 'The category has been deleted',
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Category not found',
        ], 404);
    }
}
