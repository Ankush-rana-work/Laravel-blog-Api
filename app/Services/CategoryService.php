<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * saveCategory
     *
     * @param  mixed $request
     * @return void
     */
    public function saveCategory($request)
    {

        $category = new Category();
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->parent_id = $request->parent_id?? null;

        // save category
        if ($category->save()) {
                
            if ($request->hasFile('media')) {
                $category->addMediaFromRequest('media')
                    ->toMediaCollection('category');
            }

            return $category->load('media');
        }   

        return [];
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $post_id
     * @return void
     */
    public function updateCategory($request, $cat_id)
    {
        $category       = Category::findOrFail($cat_id);
        $category->name = $request->name;
        $category->slug = $request->slug;

        // updateting post data 
        if ($category->save()) {

            if ($request->hasFile('media')) {
                $category->addMediaFromRequest('media')
                    ->toMediaCollection('category');
            }

            return $category;
        }

        return [];
    }

    public function deletePost($request, $cat_id)
    {
        $category = Category::findOrFail($cat_id);

        // updateting post data 
        if ($category->delete()) {
            // this will remove previous images
            $category->clearMediaCollection('category');
            // once everything goes perefect it will commit data to database
            return true;
        }

        return false;
    }

}
