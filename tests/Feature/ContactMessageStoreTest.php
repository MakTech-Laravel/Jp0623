<?php

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validContactMessagePayload(array $overrides = []): array
{
    return [
        'name' => 'Jane Doe',
        'company' => 'Riverside Logistics',
        'email' => 'jane@example.com',
        'phone' => '(555) 555-5555',
        'topic' => ContactMessage::TOPICS[0],
        'message' => 'We need vending for our warehouse break room.',
        'terms_accepted' => true,
        ...$overrides,
    ];
}

test('a guest can submit a contact message', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactMessagePayload())
        ->assertRedirect(route('contact', ['sent' => 1]));

    $message = ContactMessage::query()->sole();

    expect($message->name)->toBe('Jane Doe')
        ->and($message->company)->toBe('Riverside Logistics')
        ->and($message->email)->toBe('jane@example.com')
        ->and($message->phone)->toBe('(555) 555-5555')
        ->and($message->topic)->toBe(ContactMessage::TOPICS[0])
        ->and($message->message)->toBe('We need vending for our warehouse break room.')
        ->and($message->terms_accepted)->toBeTrue()
        ->and($message->status)->toBe(ContactMessage::STATUS_NEW);
});

test('contact message validation requires core fields', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), [
            'name' => '',
            'company' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'topic' => '',
            'message' => '',
            'terms_accepted' => false,
        ])
        ->assertSessionHasErrors([
            'name',
            'email',
            'topic',
            'message',
            'terms_accepted',
        ]);

    expect(ContactMessage::query()->count())->toBe(0);
});

test('contact message rejects an invalid topic', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactMessagePayload([
            'topic' => 'Not a real topic',
        ]))
        ->assertSessionHasErrors(['topic']);

    expect(ContactMessage::query()->count())->toBe(0);
});
