<?php

namespace App\Demo;

class ResourcesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $articles = HomePageData::articles();

        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Vending & office coffee resource hub',
                'lead' => 'Guides for businesses choosing vending and for operators growing their routes.',
            ],
            'articles' => $articles,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        return HomePageData::site();
    }
}
