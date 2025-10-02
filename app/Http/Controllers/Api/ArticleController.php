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

     /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="articles api",
     *     tags={"articles"},
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
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Software",
     *         required=false,
     *      @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="sources",
     *         in="query",
     *         description="newsdata",
     *         required=false,
     *      @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         description="technology",
     *         required=false,
     *      @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="authors",
     *         in="query",
     *         description="Youmni",
     *         required=false,
     *      @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="from",
     *          in="query",
     *          description="2025-05-25",
     *          required=false,
     *       @OA\Schema(type="date")
     *       ),
     *       @OA\Parameter(
     *           name="to",
     *           in="query",
     *           description="2025-05-30",
     *           required=false,
     *        @OA\Schema(type="date")
     *        ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="422", description="Invalid Request Validation"),
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Show an article",
     *     tags={"articles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="article UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Showed"),
     *     @OA\Response(response=404, description="Article not found")
     * )
     */
    public function show(Article $article)
    {
        $article = $this->articleRepository->show($article);
        return ApiResponseHelper::returnJSON(new ArticleResource($article));
    }
}
