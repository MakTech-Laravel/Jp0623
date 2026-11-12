<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SmsTemplatePurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSmsTemplateRequest;
use App\Http\Requests\Admin\UpdateSmsTemplateRequest;
use App\Models\SmsTemplate;
use App\Services\SmsTemplateService;
use App\Support\SmsTemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SmsTemplateController extends Controller
{
    public function __construct(private SmsTemplateService $templates) {}

    public function index(): Response
    {
        return Inertia::render('admin/operator-sms/templates', [
            'templates' => $this->templates->all(),
            'purposes' => collect(SmsTemplatePurpose::cases())
                ->map(fn (SmsTemplatePurpose $purpose): array => [
                    'value' => $purpose->value,
                    'label' => $purpose->label(),
                    'description' => $purpose->description(),
                ])
                ->values()
                ->all(),
            'variables' => SmsTemplateRenderer::availableVariables(),
            'defaultSortOrder' => $this->templates->nextSortOrder(),
            'instructions' => $this->instructions(),
        ]);
    }

    public function store(StoreSmsTemplateRequest $request): RedirectResponse
    {
        $this->templates->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'SMS template saved.',
        ]);

        return redirect()->route('admin.operator-sms.templates.index');
    }

    public function update(UpdateSmsTemplateRequest $request, SmsTemplate $smsTemplate): RedirectResponse
    {
        $this->templates->update($smsTemplate, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'SMS template updated.',
        ]);

        return redirect()->route('admin.operator-sms.templates.index');
    }

    public function destroy(SmsTemplate $smsTemplate): RedirectResponse
    {
        $this->templates->delete($smsTemplate);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'SMS template deleted.',
        ]);

        return redirect()->back();
    }

    /**
     * @return list<string>
     */
    private function instructions(): array
    {
        return [
            'When a public vending or coffee form is submitted, operators within 100 miles receive an SMS.',
            'Create one active Auto — Vending template and one active Auto — Coffee template. Activating a new auto template turns the previous one off.',
            'Create Manual templates for ready-made messages on the Send SMS tab. Admins can pick a template or type a custom message.',
            'Use placeholders like {{business_name}}, {{contact_name}}, {{phone}}, {{email}}, {{location}}, and {{lead_type}}.',
            'Keep messages short. Twilio SMS works best under about 160 characters per segment.',
        ];
    }
}
