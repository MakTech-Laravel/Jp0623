<?php

namespace Database\Seeders;

use App\Models\HomeFaq;
use Illuminate\Database\Seeder;

class HomeFaqSeeder extends Seeder
{
    /**
     * Seed the current home-page FAQ items.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $item) {
            HomeFaq::query()->updateOrCreate(
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
                'question' => 'What does a vending management company do?',
                'answer' => 'It handles vending for you: finds a local operator, schedules the install and manages service and restocking.',
            ],
            [
                'question' => 'How does it work?',
                'answer' => 'You send a request, we confirm the details by email, and a vetted operator near you installs and runs the machine.',
            ],
            [
                'question' => 'Do you offer service nationwide?',
                'answer' => 'Yes. We work with operators in all 50 states.',
            ],
            [
                'question' => 'Is there any cost to my business?',
                'answer' => 'No. Equipment, installation and service are free for most locations.',
            ],
            [
                'question' => 'What types of machines do you offer?',
                'answer' => 'Snack, drink and combo machines, Vendera smart coolers, micro markets and office coffee.',
            ],
            [
                'question' => 'Can you support multi-location businesses?',
                'answer' => 'Yes. One contact manages every site, from one building to hundreds.',
            ],
            [
                'question' => 'How long does installation take?',
                'answer' => 'Usually one to two weeks after your request is confirmed.',
            ],
            [
                'question' => 'Can I choose the products?',
                'answer' => 'Yes. Each location gets a product mix based on what your people want.',
            ],
            [
                'question' => 'What if a machine breaks or runs empty?',
                'answer' => 'Contact your provider and they will arrange service, repairs and refills',
            ],
            [
                'question' => 'Can I switch providers if I\'m unhappy?',
                'answer' => 'Yes. We can replace the operator at your location.',
            ],
            [
                'question' => 'Do you offer healthy options?',
                'answer' => 'Yes. Healthy machines and mixes are available everywhere we operate.',
            ],
            [
                'question' => 'How do I get started?',
                'answer' => 'Fill out the free request form or use the contact page.',
            ],
        ];
    }
}
