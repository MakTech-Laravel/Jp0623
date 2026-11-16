<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactMessage\UpdateContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function __construct(private ContactMessageService $messages) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $status = is_string($status) && $status !== '' && $status !== 'all'
            ? $status
            : null;

        return Inertia::render('admin/contact-messages/index', [
            'messages' => $this->messages->paginate($search, $status),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function show(ContactMessage $contactMessage): Response
    {
        return Inertia::render('admin/contact-messages/show', [
            'message' => $contactMessage,
        ]);
    }

    public function update(
        UpdateContactMessageRequest $request,
        ContactMessage $contactMessage,
    ): RedirectResponse {
        $this->messages->update($contactMessage, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Message updated.']);

        return redirect()->back();
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->messages->delete($contactMessage);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Message deleted.']);

        return redirect()->route('admin.contact-messages.index');
    }
}
