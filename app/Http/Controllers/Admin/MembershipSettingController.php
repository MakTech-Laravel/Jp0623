<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MembershipSetting\UpdateMembershipSettingRequest;
use App\Models\OperatorMembership;
use App\Services\MembershipSettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MembershipSettingController extends Controller
{
    public function __construct(private MembershipSettingsService $settings) {}

    public function edit(): Response
    {
        $current = $this->settings->current();

        return Inertia::render('admin/membership-settings/edit', [
            'settings' => [
                'mode' => $current->mode,
                'monthly_price' => (int) $current->monthly_price,
                'grace_days' => (int) $current->grace_days,
            ],
            'counts' => [
                'required' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_REQUIRED)
                    ->count(),
                'past_due' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_PAST_DUE)
                    ->count(),
                'active' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_ACTIVE)
                    ->count(),
                'active_free' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_ACTIVE_FREE)
                    ->count(),
            ],
        ]);
    }

    public function update(UpdateMembershipSettingRequest $request): RedirectResponse
    {
        $this->settings->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Membership settings saved.',
        ]);

        return redirect()->back();
    }
}
