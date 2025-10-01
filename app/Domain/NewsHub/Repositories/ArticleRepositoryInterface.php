<?php


namespace App\Domain\NewsHub\Repositories;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
     public function paginate(array $filters = [], ?int $perPage = 155): ?LengthAwarePaginator;
     public function show(Article $article): ?Article;
}
