<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::where('parent_id', 0)->get();
        foreach ($categories as $key => $category) {
            $category['thumbnail'] = get_photo('category_thumbnail', $category['thumbnail']);
            $category['number_of_courses'] = get_category_wise_courses($category['id'])->count();
            $category['number_of_sub_categories'] = $category->childs->count();
        }

        return $categories;
    }

    public function all_categories()
    {
        $categories = Category::where('parent_id', 0)->get();
        foreach ($categories as $key => $category) {
            $category['thumbnail'] = get_photo('category_thumbnail', $category['thumbnail']);
            $category['number_of_courses'] = get_category_wise_courses($category['id'])->count();
            $category['number_of_sub_categories'] = $category->childs->count();
        }

        return $categories;
    }

    public function category_details(Request $request)
    {
        $response = [];

        $response[0]['sub_categories'] = sub_categories($request->category_id);
        $response[0]['courses'] = course_data(get_category_wise_courses($request->category_id));

        return $response;
    }

    public function sub_categories($id)
    {
        return sub_categories($id);
    }
}
