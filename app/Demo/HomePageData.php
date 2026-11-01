<?php

namespace App\Demo;

class HomePageData
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'site' => self::site(),
            'hero' => self::hero(),
            'howItWorks' => self::howItWorks(),
            'services' => self::services(),
            'featuredMachines' => self::featuredMachines(),
            'products' => self::products(),
            'requestForm' => self::requestForm(),
            'reviews' => self::reviews(),
            'industries' => self::industries(),
            'faqs' => self::faqs(),
            'articles' => self::articles(),
            'cities' => self::cities(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        return [
            'name' => 'VMLFS',
            'tagline' => 'Vending Machine Locations For Sale',
            'phone' => '(800) 000-0000',
            'footerBlurb' => 'Full-service vending, micro markets, office coffee and ATMs for businesses nationwide. One contact for every location.',
            'copyright' => '© 2026 vendingmachinelocationsforsale.com',
            'topNav' => [
                ['label' => 'About', 'href' => '#'],
                ['label' => 'Industries', 'href' => '#'],
                ['label' => 'ATM', 'href' => '#'],
                ['label' => 'Schools', 'href' => '#'],
                ['label' => 'Cities', 'href' => '#'],
                ['label' => 'Resources', 'href' => '#'],
            ],
            'operatorCta' => [
                'label' => 'Are you a vending operator? Click here →',
                'href' => '#',
            ],
            'mainNav' => [
                ['label' => 'Home', 'href' => '/', 'current' => true],
                ['label' => 'Machines', 'href' => '/machines'],
                ['label' => 'Products', 'href' => '#'],
                ['label' => 'Micro Markets', 'href' => '#'],
                ['label' => 'Office Coffee', 'href' => '#'],
                ['label' => 'Contact Us', 'href' => '#'],
            ],
            'headerCta' => [
                'label' => 'Get Vending Now',
                'href' => '/#request-sec',
            ],
            'footerColumns' => [
                [
                    'title' => 'Services',
                    'links' => [
                        ['label' => 'Vending machines', 'href' => '/machines'],
                        ['label' => 'Micro markets', 'href' => '#'],
                        ['label' => 'AI smart coolers', 'href' => '#'],
                        ['label' => 'Office coffee', 'href' => '#'],
                        ['label' => 'ATM machines', 'href' => '#'],
                        ['label' => 'School vending', 'href' => '#'],
                    ],
                ],
                [
                    'title' => 'Industries',
                    'links' => [
                        ['label' => 'Offices', 'href' => '#'],
                        ['label' => 'Warehouses', 'href' => '#'],
                        ['label' => 'Manufacturing', 'href' => '#'],
                        ['label' => 'Apartments', 'href' => '#'],
                        ['label' => 'Hospitals', 'href' => '#'],
                        ['label' => 'All industries', 'href' => '#'],
                    ],
                ],
                [
                    'title' => 'Company',
                    'links' => [
                        ['label' => 'About us', 'href' => '#'],
                        ['label' => 'Products', 'href' => '#'],
                        ['label' => 'Resources', 'href' => '#'],
                        ['label' => 'Cities we serve', 'href' => '#'],
                        ['label' => 'Contact us', 'href' => '#'],
                    ],
                ],
                [
                    'title' => 'Operators',
                    'links' => [
                        ['label' => 'Join the network', 'href' => '#'],
                        ['label' => 'Operator login', 'href' => '#'],
                        ['label' => 'Create account', 'href' => '#'],
                        ['label' => 'Routes for sale', 'href' => '#'],
                    ],
                ],
            ],
            'legalLinks' => [
                ['label' => 'Privacy policy', 'href' => '#'],
                ['label' => 'Terms & conditions', 'href' => '#'],
                ['label' => 'Operator terms', 'href' => '#'],
                ['label' => 'Cookie policy', 'href' => '#'],
                ['label' => 'Do not sell my info', 'href' => '#'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'eyebrow' => 'Vending management · Nationwide',
            'titleBefore' => 'Vending service for every location, ',
            'titleAccent' => 'one call away.',
            'lede' => 'We find the vendor, schedule the install and keep the machines stocked and running. You deal with one contact for every site you have.',
            'bullets' => [
                'No equipment fees, install charges or service costs',
                'Product mix chosen for each location',
                'One building or a 500-site rollout, in all 50 states',
            ],
            'cta' => [
                'label' => 'Request a free machine',
                'href' => '#request-sec',
            ],
            'phoneLabel' => 'or call',
            'trust' => [
                'stars' => '★★★★★',
                'text' => 'Used by thousands of businesses across the U.S.',
                'reviewsLabel' => 'Read reviews',
                'reviewsHref' => '#reviews-sec',
            ],
            'artKinds' => ['combo', 'cooler'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function howItWorks(): array
    {
        return [
            'eyebrow' => 'How it works',
            'title' => 'Three steps to a stocked break room.',
            'text' => 'Tell us what you need once. We handle the vendors, the install and the follow-up.',
            'steps' => [
                [
                    'title' => 'Share your locations & goals',
                    'text' => 'Send your addresses, headcount and what you want in the machines.',
                ],
                [
                    'title' => 'Get a custom vending plan',
                    'text' => 'We match each site with a vetted local operator and the right equipment.',
                ],
                [
                    'title' => 'Sit back during the rollout',
                    'text' => 'Install dates, restocking and repairs go through us.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function services(): array
    {
        return [
            'eyebrow' => 'Services we offer',
            'title' => 'Pick the setup that fits your space.',
            'items' => [
                [
                    'title' => 'Vending machines',
                    'text' => 'Snack, drink and combo machines with card and phone pay.',
                    'href' => '/machines',
                    'art' => 'combo',
                ],
                [
                    'title' => 'Micro markets',
                    'text' => 'A self-checkout store for fresh food, snacks and drinks.',
                    'href' => '#',
                    'art' => 'market',
                ],
                [
                    'title' => 'AI smart coolers',
                    'text' => 'Tap, open the door, take what you want.',
                    'href' => '#',
                    'art' => 'cooler',
                ],
                [
                    'title' => 'Office coffee',
                    'text' => 'Brewers and supplies delivered on schedule.',
                    'href' => '#',
                    'art' => 'coffee',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function featuredMachines(): array
    {
        $ids = ['combo', 'healthy', 'ai-cooler'];

        return [
            'eyebrow' => 'Featured machines',
            'title' => 'Popular machines for new placements.',
            'link' => [
                'label' => 'See all 9 machines →',
                'href' => '/machines',
            ],
            'items' => array_values(array_filter(
                self::machines(),
                fn (array $machine): bool => in_array($machine['id'], $ids, true),
            )),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function machines(): array
    {
        return [
            [
                'id' => 'combo',
                'name' => 'Snack & Drink Combo',
                'kind' => 'combo',
                'type' => 'Combo',
                'tags' => ['Combo', 'Card + phone'],
                'desc' => 'One machine for snacks and cold drinks. The most common choice for a first placement.',
                'href' => '#',
            ],
            [
                'id' => 'healthy',
                'name' => 'Healthy Choice Combo',
                'kind' => 'healthy',
                'type' => 'Healthy',
                'tags' => ['Healthy', 'Combo'],
                'desc' => 'Stocked with better-for-you snacks, waters and low-sugar drinks, with nutrition labels.',
                'href' => '#',
            ],
            [
                'id' => 'snack',
                'name' => 'Glass-Front Snack',
                'kind' => 'snack',
                'type' => 'Snack',
                'tags' => ['Snack', 'High capacity'],
                'desc' => 'A large snack selection that holds more product between service visits.',
                'href' => '#',
            ],
            [
                'id' => 'drink',
                'name' => 'Cold Drink Machine',
                'kind' => 'drink',
                'type' => 'Drink',
                'tags' => ['Drinks'],
                'desc' => 'Water, soda, juice, tea and energy drinks kept cold all day.',
                'href' => '#',
            ],
            [
                'id' => 'touch',
                'name' => 'Touchscreen Combo',
                'kind' => 'touch',
                'type' => 'Combo',
                'tags' => ['Combo', 'Touchscreen'],
                'desc' => 'A large screen with product photos, nutrition info and promotions.',
                'href' => '#',
            ],
            [
                'id' => 'food',
                'name' => 'Refrigerated Fresh Food',
                'kind' => 'food',
                'type' => 'Fresh food',
                'tags' => ['Fresh food'],
                'desc' => 'Sandwiches, salads and meals held at safe temperatures with health controls.',
                'href' => '#',
            ],
            [
                'id' => 'slim',
                'name' => 'Space-Saver Combo',
                'kind' => 'slim',
                'type' => 'Combo',
                'tags' => ['Combo', 'Compact'],
                'desc' => 'A slim unit that fits where a full-size machine won\'t.',
                'href' => '#',
            ],
            [
                'id' => 'ai-cooler',
                'name' => 'AI Smart Cooler',
                'kind' => 'cooler',
                'type' => 'AI cooler',
                'tags' => ['AI cooler', 'Tap and go'],
                'desc' => 'Tap a card or phone, open the door and take what you want. Cameras and weight sensors charge only for what you took.',
                'href' => '#',
            ],
            [
                'id' => 'micro-market',
                'name' => 'Micro Market Kiosk',
                'kind' => 'market',
                'type' => 'Micro market',
                'tags' => ['Micro market', 'Self checkout'],
                'desc' => 'Open shelves, coolers and a self-checkout kiosk: a small store in your break room.',
                'href' => '#',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function products(): array
    {
        return [
            'eyebrow' => 'Products',
            'title' => 'Snacks and drinks people actually buy.',
            'link' => [
                'label' => 'Browse all products →',
                'href' => '#',
            ],
            'items' => [
                ['kind' => 'drink', 'name' => 'Beverages', 'href' => '#'],
                ['kind' => 'chips', 'name' => 'Chips & salty snacks', 'href' => '#'],
                ['kind' => 'sweets', 'name' => 'Candy & sweets', 'href' => '#'],
                ['kind' => 'nuts', 'name' => 'Nuts & fruit', 'href' => '#'],
                ['kind' => 'pastry', 'name' => 'Pastries', 'href' => '#'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestForm(): array
    {
        return [
            'eyebrow' => 'Free request',
            'title' => 'Start your free vending request.',
            'text' => 'Takes about a minute. Our team calls you to confirm the details, then a local operator gets in touch.',
            'successMessage' => 'Thanks! Our team will call you within one business day to confirm your request.',
            'submitLabel' => 'Get vending now',
            'locationTypes' => [
                'Business / office',
                'Warehouse / factory',
                'Apartment / hotel',
                'School / college',
                'Medical',
                'Gym',
                'Other',
            ],
            'machineInterests' => [
                'Snack',
                'Drinks',
                'Combo',
                'Healthy',
                'AI cooler',
                'Micro market',
                'Coffee',
                'ATM',
            ],
            'machineCounts' => ['1', '2', '3', '4+'],
            'existingVending' => ['No', 'Yes, want to switch'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reviews(): array
    {
        return [
            'eyebrow' => 'Reviews',
            'title' => 'What our customers are saying.',
            'items' => [
                [
                    'stars' => '★★★★★',
                    'quote' => 'We have four warehouses and now one person to call for all of them. Machines were in within two weeks.',
                    'role' => 'Facilities Manager',
                    'org' => 'Logistics company, TX',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'The AI cooler was a hit with our night shift. Restocking has been on time every week.',
                    'role' => 'Plant Supervisor',
                    'org' => 'Manufacturer, OH',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'Residents love the market in our lobby, and it didn\'t cost the property anything.',
                    'role' => 'Property Manager',
                    'org' => 'Apartment community, FL',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function industries(): array
    {
        return [
            'eyebrow' => 'Who we serve',
            'title' => 'Vending for every kind of workplace.',
            'text' => 'If people spend their day at your location, we can set up a machine that fits it.',
            'items' => [
                ['slug' => 'offices', 'label' => 'Offices', 'href' => '#'],
                ['slug' => 'warehouses', 'label' => 'Warehouses', 'href' => '#'],
                ['slug' => 'manufacturing', 'label' => 'Manufacturing', 'href' => '#'],
                ['slug' => 'schools', 'label' => 'Schools', 'href' => '#'],
                ['slug' => 'colleges', 'label' => 'Colleges & universities', 'href' => '#'],
                ['slug' => 'hospitals', 'label' => 'Hospitals & medical', 'href' => '#'],
                ['slug' => 'apartments', 'label' => 'Apartments', 'href' => '#'],
                ['slug' => 'hotels', 'label' => 'Hotels & motels', 'href' => '#'],
                ['slug' => 'gyms', 'label' => 'Gyms & fitness', 'href' => '#'],
                ['slug' => 'retail', 'label' => 'Retail & dealerships', 'href' => '#'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function faqs(): array
    {
        return [
            'eyebrow' => 'FAQ',
            'title' => 'Frequently asked questions.',
            'items' => [
                [
                    'q' => 'What does a vending management company do?',
                    'a' => 'It handles vending for you: finds a local operator, schedules the install and manages service and restocking.',
                ],
                [
                    'q' => 'How does it work?',
                    'a' => 'You send a request, we confirm the details by phone, and a vetted operator near you installs and runs the machine.',
                ],
                [
                    'q' => 'Do you offer service nationwide?',
                    'a' => 'Yes. We work with operators in all 50 states.',
                ],
                [
                    'q' => 'Is there any cost to my business?',
                    'a' => 'No. Equipment, installation and service are free for most locations.',
                ],
                [
                    'q' => 'What types of machines do you offer?',
                    'a' => 'Snack, drink and combo machines, AI smart coolers, micro markets, office coffee and ATMs.',
                ],
                [
                    'q' => 'Can you support multi-location businesses?',
                    'a' => 'Yes. One contact manages every site, from one building to hundreds.',
                ],
                [
                    'q' => 'How long does installation take?',
                    'a' => 'Usually two to four weeks after your request is confirmed.',
                ],
                [
                    'q' => 'Can I choose the products?',
                    'a' => 'Yes. Each location gets a product mix based on what your people want.',
                ],
                [
                    'q' => 'What if a machine breaks or runs empty?',
                    'a' => 'Contact us and we get the operator out to fix or refill it.',
                ],
                [
                    'q' => 'Can I switch providers if I\'m unhappy?',
                    'a' => 'Yes. We can replace the operator at your location.',
                ],
                [
                    'q' => 'Do you offer healthy options?',
                    'a' => 'Yes. Healthy machines and mixes are available everywhere we operate.',
                ],
                [
                    'q' => 'How do I get started?',
                    'a' => 'Fill out the free request form or call us.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function articles(): array
    {
        return [
            'eyebrow' => 'Resource hub',
            'title' => 'Expert guides and smart break room tips.',
            'link' => [
                'label' => 'View all resources →',
                'href' => '#',
            ],
            'items' => [
                [
                    'id' => 'multi-site',
                    'cat' => 'Vending management',
                    'title' => 'How to choose a vending company for multiple sites',
                    'excerpt' => 'What to ask before you sign, and why one contact beats ten vendors.',
                    'href' => '#',
                ],
                [
                    'id' => 'ai-vs-market',
                    'cat' => 'Smart & AI vending',
                    'title' => 'AI coolers vs. micro markets: which one fits?',
                    'excerpt' => 'A simple guide based on headcount, space and food needs.',
                    'href' => '#',
                ],
                [
                    'id' => 'healthy-mix',
                    'cat' => 'Healthy vending',
                    'title' => 'Building a healthier snack mix your team will buy',
                    'excerpt' => 'Swap in better options without killing sales.',
                    'href' => '#',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function cities(): array
    {
        $raw = [
            ['Houston', 'TX'],
            ['New York', 'NY'],
            ['Atlanta', 'GA'],
            ['Dallas', 'TX'],
            ['Chicago', 'IL'],
            ['Miami', 'FL'],
            ['Charlotte', 'NC'],
            ['Philadelphia', 'PA'],
            ['Los Angeles', 'CA'],
            ['Fort Worth', 'TX'],
            ['Orlando', 'FL'],
            ['Phoenix', 'AZ'],
            ['Tampa', 'FL'],
            ['Las Vegas', 'NV'],
            ['Indianapolis', 'IN'],
            ['Cleveland', 'OH'],
            ['Memphis', 'TN'],
            ['Fort Lauderdale', 'FL'],
            ['Sacramento', 'CA'],
            ['Detroit', 'MI'],
            ['Boston', 'MA'],
            ['Jacksonville', 'FL'],
            ['Columbus', 'OH'],
            ['Raleigh', 'NC'],
            ['Washington', 'DC'],
            ['Baltimore', 'MD'],
            ['Cincinnati', 'OH'],
            ['Greensboro', 'NC'],
            ['San Antonio', 'TX'],
            ['Nashville', 'TN'],
        ];

        return [
            'eyebrow' => 'Top cities',
            'title' => 'Cities where we manage vending.',
            'items' => array_map(fn (array $city): array => [
                'city' => $city[0],
                'state' => $city[1],
                'label' => $city[0].' vending',
                'href' => '#',
            ], $raw),
        ];
    }
}
