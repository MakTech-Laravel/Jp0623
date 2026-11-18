<?php

namespace Database\Seeders;

use App\Models\AboutFaq;
use Illuminate\Database\Seeder;

class AboutFaqSeeder extends Seeder
{
    /**
     * Seed the starter About page FAQ items.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $item) {
            AboutFaq::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function items(): array
    {
        return [
            [
                'question' => 'What does Vending Machine Location Services do?',
                'answer' => 'We take your vending request, check the details of your location, and match you with a vetted local operator who installs and services the machine. On the other side, we give independent operators verified locations and tools to grow their business.',
            ],
            [
                'question' => 'Does it cost my business anything to host a machine?',
                'answer' => 'No. The operator owns and stocks the machine and earns from sales, so there is no cost to the location.',
            ],
            [
                'question' => 'How do you choose the operator for my location?',
                'answer' => 'We review your request, including the type of location, foot traffic and space, and match it with a local operator who responds quickly and fits your needs. Our team follows up by email to confirm the details before the operator gets in touch.',
            ],
            [
                'question' => 'Where do you operate?',
                'answer' => 'We cover all 50 states, with local pages for 30+ cities and a network of more than 1,000 operators.',
            ],
            [
                'question' => 'I am a vending operator. How do I get locations?',
                'answer' => 'Join our operator network to receive a steady flow of verified location leads, browse routes for sale, and use our tools to grow your business. Visit the operators page to see membership options.',
            ],
        ];
    }
}
