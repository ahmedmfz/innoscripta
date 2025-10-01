<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helper\ApiResponseHelper;
use App\Http\Resources\GeneralCollection;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select('id','slug','label')->orderBy('label')->paginate();

         return ApiResponseHelper::returnJSON(new GeneralCollection($categories ,CategoryResource::class));
    }
}
