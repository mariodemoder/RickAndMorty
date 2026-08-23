<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMorty\Helpers\UrlHelper;
use PHPUnit\Framework\TestCase;

class UrlHelperTest extends TestCase
{
    public function test_extract_id_from_valid_url(): void
    {
        $this->assertEquals(1, UrlHelper::extractIdFromUrl('https://rickandmortyapi.com/api/character/1'));
        $this->assertEquals(42, UrlHelper::extractIdFromUrl('https://rickandmortyapi.com/api/location/42'));
        $this->assertEquals(51, UrlHelper::extractIdFromUrl('https://rickandmortyapi.com/api/episode/51'));
    }

    public function test_extract_id_from_url_with_trailing_slash(): void
    {
        $this->assertEquals(1, UrlHelper::extractIdFromUrl('https://rickandmortyapi.com/api/character/1/'));
    }

    public function test_extract_id_from_empty_url(): void
    {
        $this->assertNull(UrlHelper::extractIdFromUrl(''));
    }

    public function test_extract_id_from_url_without_numeric_id(): void
    {
        $this->assertNull(UrlHelper::extractIdFromUrl('https://rickandmortyapi.com/api/character'));
    }

    public function test_extract_id_from_invalid_url(): void
    {
        $this->assertNull(UrlHelper::extractIdFromUrl('not-a-url'));
    }
}
