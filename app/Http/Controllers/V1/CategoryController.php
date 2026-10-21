<?php

namespace App\Http\Controllers\V1;

use App\Http\Services\V1\CategoryService;
use App\Http\Requests\V1\CategoryRequest;

class CategoryController
{
    private $categoryService;
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
  
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return response()->json($categories, 200);
    }

 
    public function store(CategoryRequest $request)
    {
        return $this->categoryService->createCategory($request->validated());
    }

 
    public function show(int $id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if ($category) {
            return response()->json($category, 200);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function update(CategoryRequest $request, int $id)
    {
        return $this->categoryService->updateCategory($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->categoryService->deleteCategory($id);
    }
}
