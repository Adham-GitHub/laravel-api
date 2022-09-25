<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    private $searchQueries = [
        'name' => '',
        'description' => '',
    ];


    private $filterQueries = [
        'direction' => ['id', 'asc'],
        'price' => [0, 999999999],
        'limit' => 999999999,
        'offset' => 0
    ];


    public function createProduct(Request $request)
    {
        if ($request->category_id && !Category::find($request->category_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validation = Validator::make($request->all(), [
            'category_id' => 'required | numeric',
            'name' => 'required | string',
            'price' => 'required | numeric',
            'description' => 'required | string',
            'image' => 'required | image'
        ]);


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validation->errors(),
            ], 400);
        }

        if ($request->image && $request->image->isValid()) {
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => str_replace(' ', '-', strtolower($request->name)),
                'price' => $request->price,
                'description' => $request->description,
                'image' => Storage::disk('public')
                    ->put('images/product', $request->image)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'The image is invalid',
        ], 400);
    }


    public function allProducts(Request $request)
    {
        $queries = false;

        foreach ($this->searchQueries as $query => $_) {
            $value = $request->query($query);

            if ($value) {
                $this->searchQueries[$query] = $value;

                if (!$queries) $queries = true;
            }
        }

        foreach ($this->filterQueries as $query => $_) {
            $value = $request->query($query);

            if ($value) {
                if ($query === 'direction') {
                    $values = explode('-', $value);

                    if (count($values) === 1) {
                        $this->filterQueries[$query] = ['id', $values[0]];
                    } else {
                        $this->filterQueries[$query] = [$values[0], $values[1]];
                    }
                } else if ($query === 'price') {
                    $values = explode('-', $value);

                    if (count($values) === 1) {
                        $this->filterQueries[$query] = [0, (int)$values[0]];
                    } else {
                        $this->filterQueries[$query] = [(int)$values[0], (int)$values[1]];
                    }
                } else {
                    $this->filterQueries[$query] = $value;
                }

                if (!$queries) $queries = true;
            }
        }

        if ($queries) {
            $data = Product::where(function ($specialQuery) {
                foreach ($this->searchQueries as $query => $value) {
                    $specialQuery->orWhere($query, 'like', "%$value%");
                }
            })->whereBetween('price', $this->filterQueries['price'])->orderBy(
                $this->filterQueries['direction'][0],
                $this->filterQueries['direction'][1]
            )->offset($this->filterQueries['offset'])
                ->limit($this->filterQueries['limit'])
                ->get();

            return response()->json($data, 200);
        }

        return response()->json(Product::all(), 200);
    }


    public function oneProduct(Request $request)
    {
        $product = Product::whereSlug(strtolower($request->slug))
            ->first();

        if ($product) {
            return response()->json($product, 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Product not found'
        ], 404);
    }


    public function updateProduct(Request $request, $id)
    {
        if ($request->category_id && !Category::find($request->category_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validation = Validator::make($request->all(), [
            'category_id' => 'nullable | numeric',
            'name' => 'nullable | string',
            'price' => 'nullable | numeric',
            'description' => 'nullable | string',
            'image' => 'nullable | image'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validation->errors(),
            ], 400);
        }

        $product = Product::find($id);

        if ($product) {
            $updatingInfo = [];

            foreach (['category_id', 'name', 'price', 'description', 'image'] as $column) {
                if ($request[$column]) {
                    if ($column === 'image') {
                        Storage::disk('public')->delete($product->image);
                        $updatingInfo[$column] = Storage::disk('public')
                            ->put('images/product', $request->image);
                    } else if ($column === 'name') {
                        $updatingInfo[$column] = $request[$column];
                        $updatingInfo['slug'] = str_replace(' ', '-', strtolower($request[$column]));
                    } else {
                        $updatingInfo[$column] = $request[$column];
                    }
                }
            }

            $product->update($updatingInfo);

            return response()->json([
                'status' => true,
                'message' => 'The product has been updated',
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Product not found',
        ], 404);
    }


    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'The product has been deleted',
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Product not found',
        ], 404);
    }
}
