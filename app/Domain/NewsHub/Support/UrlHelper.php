<?php

namespace App\Domain\NewsHub\Support;

final class UrlHelper
{
    public static function normalize(string $originalUrl): string
    {
        $urlParts = parse_url($originalUrl);
        if (!$urlParts) return $originalUrl;

        $scheme = $urlParts['scheme'] ?? 'https';
        $host   = $urlParts['host'] ?? '';
        $path   = $urlParts['path'] ?? '';
        $query  = $urlParts['query'] ?? '';

        parse_str($query, $queryParameters);
        foreach (['utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $trackingParam) {
            unset($queryParameters[$trackingParam]);
        }
        ksort($queryParameters);

        $normalizedQueryString = http_build_query($queryParameters);
        return strtolower($scheme.'://'.$host.$path.($normalizedQueryString ? '?'.$normalizedQueryString : ''));
    }

    public static function canonicalHash(string $originalUrl): string
    {
        return (string) sha1(self::normalize($originalUrl));
    }
}
