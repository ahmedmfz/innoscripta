<?php

namespace App\Http\Controllers\Api;


use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Article;
use App\Domain\NewsHub\Repositories\ArticleRepositoryInterface;
use App\Helper\ApiResponseHelper;
use App\Http\Resources\GeneralCollection;
use App\Http\Resources\ArticleResource;


class ArticleController extends Controller
{
    public function __construct(private readonly ArticleRepositoryInterface $articleRepository) {}

    public function index(Request $httpRequest)
    {
        $filters = [
            'q'          => $httpRequest->query('q'),
            'sources'    => (array)$httpRequest->query('sources', []),
            'categories' => (array)$httpRequest->query('categories', []),
            'authors'    => (array)$httpRequest->query('authors', []),
            'date_from'  => $httpRequest->query('date_from'),
            'date_to'    => $httpRequest->query('date_to'),
        ];
        $perPage = (int)$httpRequest->query('per_page', 15);
        $articles = $this->articleRepository->paginate($filters, $perPage);

        return ApiResponseHelper::returnJSON(new GeneralCollection($articles ,ArticleResource::class));
    }

    public function show(Article $article)
    {
        $article = $this->articleRepository->show($article);
        return ApiResponseHelper::returnJSON(new ArticleResource($article));
    }
}
