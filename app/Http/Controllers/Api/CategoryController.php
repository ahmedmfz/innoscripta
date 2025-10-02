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
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="categories api",
     *     tags={"categories"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="count of rows in a single page",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Invalid Request Validation"),
     * )
     */
    public function index(Request $request)
    {
        $categories = Category::select('id','slug','label')->orderBy('label')->paginate($request->per_page ?? 15);
         return ApiResponseHelper::returnJSON(new GeneralCollection($categories ,CategoryResource::class));
    }
}
