<?php

namespace App\Support;

use App\Enums\RoleEnum;
use App\Models\CoffeeServiceRequest;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\OperatorMembership;
use App\Models\Product;
use App\Models\User;
use App\Models\VendingRequest;
use App\Services\MembershipSettingsService;
use App\Services\PaymentHistoryService;

class AdminDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        $settings = app(MembershipSettingsService::class)->current();
        $revenue = app(PaymentHistoryService::class)->revenueSummary();

        return [
            'stats' => [
                'operators' => User::role(RoleEnum::USER->value)->count(),
                'admins' => User::role(RoleEnum::ADMIN->value)->count(),
                'machines' => Machine::query()->count(),
                'categories' => MachineCategory::query()->count(),
                'products' => Product::query()->count(),
                'vendingRequests' => [
                    'total' => VendingRequest::query()->count(),
                    'new' => VendingRequest::query()
                        ->where('status', VendingRequest::STATUS_NEW)
                        ->count(),
                ],
                'coffeeRequests' => [
                    'total' => CoffeeServiceRequest::query()->count(),
                    'new' => CoffeeServiceRequest::query()
                        ->where('status', CoffeeServiceRequest::STATUS_NEW)
                        ->count(),
                ],
            ],
            'revenue' => $revenue,
            'membership' => [
                'mode' => $settings->mode,
                'monthly_price' => (int) $settings->monthly_price,
                'required' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_REQUIRED)
                    ->count(),
                'past_due' => OperatorMembership::query()
                    ->where('status', OperatorMembership::STATUS_PAST_DUE)
                    ->count(),
            ],
            'recentVendingRequests' => VendingRequest::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'business_name', 'status', 'created_at'])
                ->map(fn (VendingRequest $request): array => [
                    'id' => $request->id,
                    'business_name' => $request->business_name,
                    'status' => $request->status,
                    'created_at' => $request->created_at->toIso8601String(),
                ])
                ->all(),
            'recentCoffeeRequests' => CoffeeServiceRequest::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'business_name', 'status', 'created_at'])
                ->map(fn (CoffeeServiceRequest $request): array => [
                    'id' => $request->id,
                    'business_name' => $request->business_name,
                    'status' => $request->status,
                    'created_at' => $request->created_at->toIso8601String(),
                ])
                ->all(),
        ];
    }
}
