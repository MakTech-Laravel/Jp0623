<?php

namespace App\Demo;

class MachineDetailPageData
{
    /**
     * @return list<string>
     */
    public static function checks(): array
    {
        return [
            'Remote stock monitoring',
            'Product mix chosen for your location',
            'Local operator handles repairs',
            'Multiple machines can be placed at locations with more visitors/employees',
        ];
    }

    /**
     * @return array{label: string, href: string}
     */
    public static function cta(): array
    {
        return [
            'label' => 'Request this machine',
            'href' => '/#request-sec',
        ];
    }
}
