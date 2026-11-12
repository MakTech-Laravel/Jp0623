<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendOperatorSmsRequest;
use App\Services\AdminOperatorSmsService;
use App\Services\SmsTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperatorSmsController extends Controller
{
    public function __construct(
        private AdminOperatorSmsService $operatorSms,
        private SmsTemplateService $templates,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $range = (string) $request->query('range', 'all');
        $minMiles = $request->query('min_miles');
        $maxMiles = $request->query('max_miles');

        [$resolvedMin, $resolvedMax] = $this->resolveRange(
            $range,
            is_numeric($minMiles) ? (float) $minMiles : null,
            is_numeric($maxMiles) ? (float) $maxMiles : null,
        );

        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $hasCenter = is_numeric($latitude) && is_numeric($longitude);

        $result = $this->operatorSms->listOperators(
            $hasCenter ? (float) $latitude : null,
            $hasCenter ? (float) $longitude : null,
            $resolvedMin,
            $resolvedMax,
            $search !== '' ? $search : null,
        );

        return Inertia::render('admin/operator-sms/index', [
            'operators' => $result['operators'],
            'total' => $result['total'],
            'truncated' => $result['truncated'],
            'referenceOptions' => $this->operatorSms->referenceOptions(),
            'rangeOptions' => $this->rangeOptions(),
            'manualTemplates' => $this->templates->manualActive()->map(fn ($template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'body' => $template->body,
            ])->values()->all(),
            'twilioEnabled' => (bool) config('services.twilio.enabled')
                && filled(config('services.twilio.sid'))
                && filled(config('services.twilio.token'))
                && filled(config('services.twilio.from')),
            'filters' => [
                'search' => $search,
                'range' => $range,
                'min_miles' => is_numeric($minMiles) ? (float) $minMiles : null,
                'max_miles' => is_numeric($maxMiles) ? (float) $maxMiles : null,
                'latitude' => $hasCenter ? (float) $latitude : null,
                'longitude' => $hasCenter ? (float) $longitude : null,
                'reference_key' => (string) $request->query('reference_key', ''),
            ],
        ]);
    }

    public function send(SendOperatorSmsRequest $request): RedirectResponse
    {
        $count = $this->operatorSms->queueSend(
            array_map('intval', $request->validated('operator_ids')),
            $request->validated('message'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "SMS queued for {$count} operator".($count === 1 ? '' : 's').'.',
        ]);

        return redirect()->back();
    }

    /**
     * @return array{0: float, 1: float|null}
     */
    private function resolveRange(string $range, ?float $minMiles, ?float $maxMiles): array
    {
        return match ($range) {
            '0-100' => [0.0, 100.0],
            '100-200' => [100.0, 200.0],
            '200-300' => [200.0, 300.0],
            '300-plus' => [300.0, null],
            'custom' => [max(0.0, $minMiles ?? 0.0), $maxMiles],
            default => [0.0, null],
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function rangeOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'All distances'],
            ['value' => '0-100', 'label' => 'Under 100 miles'],
            ['value' => '100-200', 'label' => '100 – 200 miles'],
            ['value' => '200-300', 'label' => '200 – 300 miles'],
            ['value' => '300-plus', 'label' => '300+ miles'],
            ['value' => 'custom', 'label' => 'Custom range'],
        ];
    }
}
