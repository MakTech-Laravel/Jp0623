<?php

namespace App\Demo;

class CitiesPageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => [
                'title' => 'Cities we serve',
                'lead' => 'Local vending service in every state. These cities have dedicated local coverage.',
            ],
            'cities' => HomePageData::cities(),
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
