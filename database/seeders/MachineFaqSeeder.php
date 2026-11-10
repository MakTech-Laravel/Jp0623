<?php

namespace Database\Seeders;

use App\Models\MachineFaq;
use Illuminate\Database\Seeder;

class MachineFaqSeeder extends Seeder
{
    /**
     * Seed the current machines-page FAQ items.
     */
    public function run(): void
    {
        foreach ($this->items() as $index => $item) {
            MachineFaq::query()->updateOrCreate(
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
                'question' => 'Are the machines really free?',
                'answer' => 'Yes. The operator owns the machine and earns from sales, so there is no cost to you.',
            ],
            [
                'question' => 'How much power do they need?',
                'answer' => 'A standard dedicated 115V outlet near the machine.',
            ],
            [
                'question' => 'Can I get a machine outside?',
                'answer' => 'Yes, in covered areas with an outdoor-rated machine.',
            ],
            [
                'question' => 'Which machine should I choose?',
                'answer' => 'Use the request form and we\'ll recommend one based on your size and space.',
            ],
        ];
    }
}
