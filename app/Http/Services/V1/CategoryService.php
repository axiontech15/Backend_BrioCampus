<?php
  namespace App\Http\Services\V1;
  use App\Models\Category;

  class CategoryService
  {
   
      public function getAllCategories()
      {
          return Category::all();
      }

      public function getCategoryById(int $id)
      {
          return Category::find($id);
      }

      public function createCategory(array $data)
      {
         Category::create($data);
      }

      public function updateCategory(int $id, array $data):Category|bool
      {
            $category = Category::find($id);
            if($category) {
                $category->update($data);
                return $category;
            }
            return false;

      }

      public function deleteCategory(int $id)
      {
          Category::destroy($id);
      }
  }
?>