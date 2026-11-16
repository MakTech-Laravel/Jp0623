<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'slug' => 'multi-site',
                'category' => 'Vending management',
                'title' => 'How to choose a vending company for multiple sites',
                'excerpt' => 'What to ask before you sign, and why one contact beats ten vendors.',
                'body' => "Running vending across several locations is harder than running it at one. Every site has its own hours, headcount and product mix, and a different vendor at each one means a different contract, a different invoice and a different person to call when a machine goes down.\n\n"
                    ."Before you sign, ask how the company handles service at more than one site. Look for one point of contact, a single agreement that covers every location, and clear response times when something breaks. Ask who restocks, how often, and how they decide what goes in each machine.\n\n"
                    .'A good multi-site partner installs and services the equipment at no cost, adjusts the product mix per location, and reports back in one place. If a vendor cannot explain how they would manage your second and third site, they are not ready for them.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'ai-vs-market',
                'category' => 'Smart & AI vending',
                'title' => 'Vendera coolers vs. micro markets: which one fits?',
                'excerpt' => 'A simple guide based on headcount, space and food needs.',
                'body' => "Both options give people fresh food and drinks around the clock, but they suit different spaces. The right choice mostly comes down to headcount, floor space and how much fresh food you want to offer.\n\n"
                    ."A Vendera smart cooler is compact. Tap, open the door and take what you want. It works well for smaller teams and tight spaces where you still want chilled and fresh items.\n\n"
                    .'A micro market is a self-checkout store with open shelves. It needs more room and a larger group of regular users, and in return it carries a much wider range of food, snacks and drinks. If you have more than a couple of hundred people on site and a spare corner, a micro market is usually the better fit.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'healthy-mix',
                'category' => 'Healthy vending',
                'title' => 'Building a healthier snack mix your team will buy',
                'excerpt' => 'Swap in better options without killing sales.',
                'body' => "A healthier machine only helps if people actually buy from it. The goal is a mix that feels familiar but gives people better choices, not a machine full of products nobody wants.\n\n"
                    ."Start by keeping your best sellers and swapping a few slots at a time. Baked chips, protein bars, low-sugar drinks and nuts tend to sell well next to the classics. Put the better options at eye level and label them clearly.\n\n"
                    .'Then watch the sales. If an item sits, replace it. A good operator tunes the mix over time based on what your team really buys.',
                'sort_order' => 3,
            ],
        ];

        foreach ($articles as $article) {
            Article::query()->updateOrCreate(
                ['slug' => $article['slug']],
                $article + ['is_active' => true],
            );
        }
    }
}
