<?php

namespace App\Services\RickAndMorty\Helpers;

class UrlHelper
{
    public static function extractIdFromUrl(string $url): ?int
    {
        if (empty($url)) {
            return null;
        }

        $segments = explode('/', rtrim($url, '/'));
        $lastSegment = end($segments);

        if (is_numeric($lastSegment) && (int) $lastSegment > 0) {
            return (int) $lastSegment;
        }

        return null;
    }
}
