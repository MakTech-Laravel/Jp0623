<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessage\StoreContactMessageRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function __construct(private ContactMessageService $messages) {}

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $this->messages->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Message sent. We\'ll reply within one business day.',
        ]);

        return redirect()->route('contact', ['sent' => 1]);
    }
}
