<?php


namespace App\Domain\NewsHub\Repositories;


use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Domain\NewsHub\Repositories\ArticleRepositoryInterface;


class EloquentArticleRepository  implements ArticleRepositoryInterface{

   public function paginate(array $filters = [], ?int $perPage = 15): ?LengthAwarePaginator
   {
       $searchQuery       = trim((string)($filters['q'] ?? ''));
       $sourceSlugs       = (array)($filters['sources'] ?? []);
       $categorySlugs     = (array)($filters['categories'] ?? []);
       $authorNames       = (array)($filters['authors'] ?? []);
       $publishedFromDate = $filters['date_from'] ?? null;
       $publishedToDate   = $filters['date_to'] ?? null;

       $articleQuery = Article::query()
           ->with(['source:id,slug,name','authors:id,name','categories:id,slug,label'])
           ->when($searchQuery !== '', function (Builder $builder) use ($searchQuery) {
               $builder->where(function (Builder $searchBuilder) use ($searchQuery) {
                   $like = '%' . str_replace('%', '\%', $searchQuery) . '%';
                   $searchBuilder->where('title', 'like', $like)
                       ->orWhere('summary', 'like', $like)
                       ->orWhere('content', 'like', $like);
               });
           })
           ->when(!empty($sourceSlugs), function (Builder $builder) use ($sourceSlugs) {
               $builder->whereHas('source', function (Builder $sourceBuilder) use ($sourceSlugs) {
                   $sourceBuilder->whereIn('slug', $sourceSlugs);
               });
           })
           ->when(!empty($categorySlugs), function (Builder $builder) use ($categorySlugs) {
               $builder->whereHas('categories', function (Builder $categoryBuilder) use ($categorySlugs) {
                   $categoryBuilder->whereIn('slug', $categorySlugs);
               });
           })
           ->when(!empty($authorNames), function (Builder $builder) use ($authorNames) {
               $builder->whereHas('authors', function (Builder $authorBuilder) use ($authorNames) {
                   $authorBuilder->whereIn('name', $authorNames);
               });
           })
           ->when($publishedFromDate, fn(Builder $builder) => $builder->where('published_at', '>=', $publishedFromDate))
           ->when($publishedToDate,   fn(Builder $builder) => $builder->where('published_at', '<=', $publishedToDate))
           ->orderByDesc('published_at');

       return $articleQuery->paginate($perPage ?? 15);
   }

   public function show(Article $article): ?Article
   {
        $article->load(['source:id,slug,name','authors:id,name','categories:id,slug,label']);
        return $article;
   }

}
