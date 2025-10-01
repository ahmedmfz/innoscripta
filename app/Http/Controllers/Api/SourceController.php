<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Source;
use App\Helper\ApiResponseHelper;
use App\Http\Resources\GeneralCollection;
use App\Http\Resources\SourceResource;


class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::select('id','slug','name')->orderBy('name')->paginate();
        return ApiResponseHelper::returnJSON(new GeneralCollection($sources ,SourceResource::class));
    }
}
