<?php

namespace App\Support;

use App\Enums\RoleEnum;
use App\Models\CoffeeServiceRequest;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\VendingRequest;

class AdminDashboardData
{
    /**
     * @return array{
     *     stats: array{
     *         operators: int,
     *         admins: int,
     *         machines: int,
     *         categories: int,
     *         products: int,
     *         vendingRequests: array{total: int, new: int},
     *         coffeeRequests: array{total: int, new: int}
     *     },
     *     recentVendingRequests: list<array{id: int, business_name: string, status: string, created_at: string}>,
     *     recentCoffeeRequests: list<array{id: int, business_name: string, status: string, created_at: string}>
     * }
     */
    public static function make(): array
    {
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
