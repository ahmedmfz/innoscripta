<?php

namespace App\Domain\NewsHub\Actions;

use Illuminate\Support\Str;
use App\Models\Article;

final class UpsertArticleAction
{
    /**
     * @return array{model: Article, was_created: bool}
     */
    public function __invoke(?Article $existingArticle, array $databasePayload): array
    {
        if (!$existingArticle) {
            $databasePayload['id'] = (string) Str::uuid();
            $articleModel = Article::create($databasePayload);
            return ['model' => $articleModel, 'was_created' => true];
        }

        $existingArticle->update($databasePayload);
        return ['model' => $existingArticle, 'was_created' => false];
    }
}
