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
            'name' => 'Vending Machine Location Services',
            'tagline' => 'Nationwide',
            'footerBlurb' => 'Full-service vending, micro markets and office coffee for businesses nationwide. One contact for every location.',
            'copyright' => '© 2026 vendingmachinelocationsforsale.com',
            'topNav' => [
                ['label' => 'About', 'href' => '/about'],
                ['label' => 'Industries', 'href' => '/industries'],
                ['label' => 'Schools', 'href' => '/schools'],
                ['label' => 'Cities', 'href' => '/cities'],
                ['label' => 'Resources', 'href' => '/resources'],
            ],
            'operatorCta' => [
                'label' => 'Are you a vending operator? Click here →',
                'href' => '/operators',
            ],
            'mainNav' => [
                ['label' => 'Home', 'href' => '/', 'current' => true],
                ['label' => 'Machines', 'href' => '/machines'],
                ['label' => 'Products', 'href' => '/products'],
                ['label' => 'Micro Markets', 'href' => '/micro-markets'],
                ['label' => 'Office Coffee', 'href' => '/office-coffee'],
                ['label' => 'Contact Us', 'href' => '/contact'],
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
                        ['label' => 'Micro markets', 'href' => '/micro-markets'],
                        ['label' => 'Vendera smart coolers', 'href' => '/machines/ai-cooler'],
                        ['label' => 'Office coffee', 'href' => '/office-coffee'],
                        ['label' => 'School vending', 'href' => '/schools'],
                    ],
                ],
                [
                    'title' => 'Industries',
                    'links' => [
                        ['label' => 'Offices', 'href' => '/industries/offices'],
                        ['label' => 'Warehouses', 'href' => '/industries/warehouses'],
                        ['label' => 'Manufacturing', 'href' => '/industries/manufacturing'],
                        ['label' => 'Apartments', 'href' => '/industries/apartments'],
                        ['label' => 'Hospitals', 'href' => '/industries/hospitals'],
                        ['label' => 'All industries', 'href' => '/industries'],
                    ],
                ],
                [
                    'title' => 'Company',
                    'links' => [
                        ['label' => 'About us', 'href' => '/about'],
                        ['label' => 'Products', 'href' => '/products'],
                        ['label' => 'Resources', 'href' => '/resources'],
                        ['label' => 'Cities we serve', 'href' => '/cities'],
                        ['label' => 'Contact us', 'href' => '/contact'],
                    ],
                ],
                [
                    'title' => 'Operators',
                    'links' => [
                        ['label' => 'Join the network', 'href' => '/operators'],
                        ['label' => 'Operator login', 'href' => '/login'],
                        ['label' => 'Create account', 'href' => '/register'],
                        ['label' => 'Routes for sale', 'href' => '/login'],
                    ],
                ],
            ],
            'legalLinks' => [
                ['label' => 'Privacy policy', 'href' => '/legal/privacy'],
                ['label' => 'Terms & conditions', 'href' => '/legal/terms'],
                ['label' => 'Operator terms', 'href' => '/legal/operator-terms'],
                ['label' => 'Cookie policy', 'href' => '/legal/cookies'],
                ['label' => 'Do not sell my info', 'href' => '/legal/cookies'],
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
            'titleBefore' => 'Vending Machine Location Services, ',
            'titleAccent' => 'for every location.',
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
            'trust' => [
                'stars' => '★★★★★',
                'text' => 'Used by thousands of businesses across the U.S.',
                'reviewsLabel' => 'Read reviews',
                'reviewsHref' => '#reviews-sec',
            ],
            'images' => [
                [
                    'src' => '/images/hero/Vendera-Black-Glass-Display-Cooler.png',
                    'alt' => 'Snack and drink combo machine',
                ],
                [
                    'src' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
                    'alt' => 'Alpine snack and beverage machine',
                ],
                [
                    'src' => '/images/hero/tradational-vending.png',
                    'alt' => 'Traditional vending machine',
                ],
                [
                    'src' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
                    'alt' => 'Vendera smart cooler',
                ],
                [
                    'src' => '/images/machines/micro-market.jpg',
                    'alt' => 'Micro market setup',
                ],
            ],
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
                    'image' => '/images/machines/Alpine-ST5000.png',
                ],
                [
                    'title' => 'Micro markets',
                    'text' => 'A self-checkout store for fresh food, snacks and drinks.',
                    'href' => '/micro-markets',
                    'image' => '/images/machines/micro-market.jpg',
                ],
                [
                    'title' => 'Vendera smart coolers',
                    'text' => 'Tap, open the door, take what you want.',
                    'href' => '/machines/ai-cooler',
                    'image' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
                ],
                [
                    'title' => 'Office coffee',
                    'text' => 'Brewers and supplies delivered on schedule.',
                    'href' => '/office-coffee',
                    'image' => '/images/machines/coffee1.jpg',
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
                'label' => 'See all 7 machines →',
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
                'href' => '/machines/combo',
                'image' => '/images/machines/snack-drink-combo.png',
            ],
            [
                'id' => 'healthy',
                'name' => 'Healthy Choice Combo',
                'kind' => 'healthy',
                'type' => 'Healthy',
                'tags' => ['Healthy', 'Combo'],
                'desc' => 'Stocked with better-for-you snacks, waters and low-sugar drinks, with nutrition labels.',
                'href' => '/machines/healthy',
                'image' => '/images/machines/healthy-vending.png',
            ],
            [
                'id' => 'snack',
                'name' => 'Snack Machine',
                'kind' => 'snack',
                'type' => 'Snack',
                'tags' => ['Snack', 'High capacity'],
                'desc' => 'A large snack selection that holds more product between service visits.',
                'href' => '/machines/snack',
                'image' => '/images/machines/snack-machine.png',
            ],
            [
                'id' => 'drink',
                'name' => 'Cold Drink Machine',
                'kind' => 'drink',
                'type' => 'Drink',
                'tags' => ['Drinks'],
                'desc' => 'Water, soda, juice, tea and energy drinks kept cold all day.',
                'href' => '/machines/drink',
                'image' => '/images/machines/Cold-Drink-Machine.jpg',
            ],
            [
                'id' => 'slim',
                'name' => 'Space Saver Combo',
                'kind' => 'slim',
                'type' => 'Combo',
                'tags' => ['Combo', 'Compact'],
                'desc' => 'A slim unit that fits where a full-size machine won\'t.',
                'href' => '/machines/slim',
                'image' => '/images/machines/space-saver-combo.jpg',
            ],
            [
                'id' => 'ai-cooler',
                'name' => 'Vendera Smart Cooler',
                'kind' => 'cooler',
                'type' => 'Vendera cooler',
                'tags' => ['Vendera cooler', 'Tap and go'],
                'desc' => 'Tap a card or phone, open the door and take what you want. Cameras and weight sensors charge only for what you took.',
                'href' => '/machines/ai-cooler',
                'image' => '/images/machines/Vendera-Tap-Grab-Go-Cabinet.png',
            ],
            [
                'id' => 'micro-market',
                'name' => 'Micro Market Kiosk',
                'kind' => 'market',
                'type' => 'Micro market',
                'tags' => ['Micro market', 'Self checkout'],
                'desc' => 'Open shelves, coolers and a self-checkout kiosk: a small store in your break room.',
                'href' => '/machines/micro-market',
                'image' => '/images/machines/micro-market.jpg',
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
                'href' => '/products',
            ],
            'items' => array_map(
                fn (array $item): array => [
                    'kind' => $item['kind'],
                    'name' => $item['name'],
                    'href' => '/products',
                    'image' => $item['image'],
                ],
                array_slice(self::productCatalog(), 0, 5),
            ),
        ];
    }

    /**
     * @return list<array{kind: string, name: string, desc: string, tags: list<string>, image: string}>
     */
    public static function productCatalog(): array
    {
        return [
            [
                'kind' => 'drink',
                'name' => 'Beverages',
                'desc' => 'Water, soda, sparkling water, juice and iced tea',
                'tags' => ['Bottled water', 'Cola & diet cola', 'Sparkling water', 'Orange juice', 'Iced tea'],
                'image' => '/images/products/drinks.jpg',
            ],
            [
                'kind' => 'chips',
                'name' => 'Chips & salty snacks',
                'desc' => 'Classic chips, pretzels, popcorn and crackers',
                'tags' => ['Potato chips', 'Pretzels', 'Popcorn', 'Cheese crackers', 'Tortilla chips'],
                'image' => '/images/products/chips.jpg',
            ],
            [
                'kind' => 'sweets',
                'name' => 'Candy & sweets',
                'desc' => 'Chocolate, gummies, mints and cookies',
                'tags' => ['Chocolate bars', 'Gummies', 'Cookies', 'Mints', 'Peanut butter cups'],
                'image' => '/images/products/candy.jpg',
            ],
            [
                'kind' => 'nuts',
                'name' => 'Nuts & snack bars',
                'desc' => 'Trail mix, almonds, protein bars, granola bars and breakfast bars',
                'tags' => ['Trail mix', 'Almonds', 'Protein bar', 'Granola bar', 'Mixed nuts'],
                'image' => '/images/products/nuts-and-snack-bars.jpg',
            ],
            [
                'kind' => 'pastry',
                'name' => 'Pastries',
                'desc' => 'Muffins, donuts, honey buns and toaster pastries',
                'tags' => ['Blueberry muffin', 'Honey bun', 'Mini donuts', 'Croissant', 'Pop-tarts'],
                'image' => '/images/products/pastries.jpg',
            ],
            [
                'kind' => 'healthy',
                'name' => 'Healthy choices',
                'desc' => 'Baked snacks, low-sugar drinks and plant-based options',
                'tags' => ['Baked chips', 'Veggie straws', 'Kombucha', 'Seaweed snacks', 'Low-sugar tea'],
                'image' => '/images/products/Healthy-choices.jpg',
            ],
            [
                'kind' => 'energy',
                'name' => 'Energy and sports drinks',
                'desc' => 'Energy drinks, electrolyte drinks and protein shakes',
                'tags' => ['Energy drink', 'Sports drink', 'Protein shake', 'Cold brew can', 'Electrolyte water'],
                'image' => '/images/products/energy-and-sports-drinks.jpg',
            ],
            [
                'kind' => 'food',
                'name' => 'Fresh food',
                'desc' => 'Sandwiches, salads and ready meals for markets and coolers',
                'tags' => ['Turkey sandwich', 'Chicken salad', 'Pasta bowl', 'Fruit cup', 'Yogurt parfait'],
                'image' => '/images/products/fresh-food.jpg',
            ],
            [
                'kind' => 'coffee',
                'name' => 'Coffee & hot drinks',
                'desc' => 'Single-cup, bean-to-cup and tea',
                'tags' => ['Medium roast', 'Dark roast', 'Espresso', 'Green tea', 'Hot cocoa'],
                'image' => '/images/products/Baked-Doritos.jpg',
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
            'text' => 'Takes about a minute. Our team will email you to confirm the details, then a local operator gets in touch.',
            'successMessage' => 'Thanks! Our team will email you within one business day to confirm your request.',
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
                'Vendera cooler',
                'Micro market',
                'Coffee',
            ],
            'machineCounts' => ['1', '2', '3', '4+'],
            'existingVending' => ['No', 'Yes, want to switch'],
            'yesNoOptions' => ['Yes', 'No'],
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
                    'quote' => 'We have four warehouses and now one person to contact for all of them. Machines were in within two weeks.',
                    'role' => 'Facilities Manager',
                    'org' => 'Logistics company, TX',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'The Vendera cooler was a hit with our night shift. Restocking has been on time every week.',
                    'role' => 'Plant Supervisor',
                    'org' => 'Manufacturer, OH',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'Residents love the market in our lobby, and it didn\'t cost the property anything.',
                    'role' => 'Property Manager',
                    'org' => 'Apartment community, FL',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'Setup was simple and our staff actually uses the snack machine every day. Service has been reliable.',
                    'role' => 'Office Manager',
                    'org' => 'Accounting firm, GA',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'We needed healthy options for a hospital break room. They matched the mix and keep it stocked.',
                    'role' => 'Dietary Services Lead',
                    'org' => 'Regional hospital, NC',
                ],
                [
                    'stars' => '★★★★★',
                    'quote' => 'One request covered three campuses. Install dates were clear and the operators showed up as promised.',
                    'role' => 'Operations Director',
                    'org' => 'Private school network, CA',
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
                ['slug' => 'offices', 'label' => 'Offices', 'href' => '/industries/offices'],
                ['slug' => 'warehouses', 'label' => 'Warehouses', 'href' => '/industries/warehouses'],
                ['slug' => 'manufacturing', 'label' => 'Manufacturing', 'href' => '/industries/manufacturing'],
                ['slug' => 'schools', 'label' => 'Schools', 'href' => '/industries/schools'],
                ['slug' => 'colleges', 'label' => 'Colleges & universities', 'href' => '/industries/colleges'],
                ['slug' => 'hospitals', 'label' => 'Hospitals & medical', 'href' => '/industries/hospitals'],
                ['slug' => 'apartments', 'label' => 'Apartments', 'href' => '/industries/apartments'],
                ['slug' => 'hotels', 'label' => 'Hotels & motels', 'href' => '/industries/hotels'],
                ['slug' => 'gyms', 'label' => 'Gyms & fitness', 'href' => '/industries/gyms'],
                ['slug' => 'retail', 'label' => 'Retail & dealerships', 'href' => '/industries/retail'],
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
                    'a' => 'You send a request, we confirm the details by email, and a vetted operator near you installs and runs the machine.',
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
                    'a' => 'Snack, drink and combo machines, Vendera smart coolers, micro markets and office coffee.',
                ],
                [
                    'q' => 'Can you support multi-location businesses?',
                    'a' => 'Yes. One contact manages every site, from one building to hundreds.',
                ],
                [
                    'q' => 'How long does installation take?',
                    'a' => 'Usually one to two weeks after your request is confirmed.',
                ],
                [
                    'q' => 'Can I choose the products?',
                    'a' => 'Yes. Each location gets a product mix based on what your people want.',
                ],
                [
                    'q' => 'What if a machine breaks or runs empty?',
                    'a' => 'Contact your provider and they will arrange service, repairs and refills',
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
                    'a' => 'Fill out the free request form or use the contact page.',
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
                'href' => '/resources',
            ],
            'items' => [
                [
                    'id' => 'multi-site',
                    'cat' => 'Vending management',
                    'title' => 'How to choose a vending company for multiple sites',
                    'excerpt' => 'What to ask before you sign, and why one contact beats ten vendors.',
                    'href' => '/resources',
                ],
                [
                    'id' => 'ai-vs-market',
                    'cat' => 'Smart & AI vending',
                    'title' => 'Vendera coolers vs. micro markets: which one fits?',
                    'excerpt' => 'A simple guide based on headcount, space and food needs.',
                    'href' => '/resources',
                ],
                [
                    'id' => 'healthy-mix',
                    'cat' => 'Healthy vending',
                    'title' => 'Building a healthier snack mix your team will buy',
                    'excerpt' => 'Swap in better options without killing sales.',
                    'href' => '/resources',
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
                'href' => '/cities',
            ], $raw),
        ];
    }
}
