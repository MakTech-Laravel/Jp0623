<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->pages() as $slug => $page) {
            LegalPage::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $page['title'],
                    'last_updated_on' => '2026-11-01',
                    'sections' => $page['sections'],
                ],
            );
        }
    }

    /**
     * @return array<string, array{title: string, sections: list<array{heading: string|null, body: string}>}>
     */
    private function pages(): array
    {
        return [
            'privacy' => [
                'title' => 'Privacy policy',
                'sections' => $this->placeholderSections([
                    'Information we collect',
                    'How we use information',
                    'Sharing with vending operators',
                    'Cookies and tracking',
                    'Your choices and rights',
                    'Contact us',
                ]),
            ],
            'terms' => [
                'title' => 'Terms & conditions',
                'sections' => $this->termsSections(),
            ],
            'operator-terms' => [
                'title' => 'Operator terms',
                'sections' => $this->placeholderSections([
                    'Membership and lead purchases',
                    'Lead purchases',
                    'Lead issue reports',
                    'Routes and third-party brokers',
                    'Account termination',
                ]),
            ],
        ];
    }

    /**
     * @param  list<string>  $headings
     * @return list<array{heading: string|null, body: string}>
     */
    private function placeholderSections(array $headings): array
    {
        return array_map(
            fn (string $heading): array => [
                'heading' => $heading,
                'body' => 'This section will explain '.strtolower($heading).' in plain language for businesses and operators using the site.',
            ],
            $headings,
        );
    }

    /**
     * @return list<array{heading: string|null, body: string}>
     */
    private function termsSections(): array
    {
        return [
            [
                'heading' => null,
                'body' => <<<'TXT'
These terms and conditions (“Agreement”) set forth the general terms and conditions of your use of the vendingmachinelocationsforsale.com website (“Website” or “Service”) and any of its related products and services (collectively, “Services”). This Agreement is legally binding between you (“User”, “you” or “your”) and Vending Machine Location Services (doing business as “Vending Machine Location Services”, “VMLS”  “we”, “us” or “our”). If you are entering into this Agreement on behalf of a business or other legal entity, you represent that you have the authority to bind such entity to this Agreement, in which case the terms “User”, “you” or “your” shall refer to such entity. If you do not have such authority, or if you do not agree with the terms of this Agreement, you must not accept this Agreement and may not access and use the Website and Services. By accessing and using the Website and Services, you acknowledge that you have read, understood, and agree to be bound by the terms of this Agreement. You acknowledge that this Agreement is a contract between you and Vending Machine Location Services, even though it is electronic and is not physically signed by you, and it governs your use of the Website and Services.
TXT,
            ],
            [
                'heading' => 'Contact by VMLS and Operators',
                'body' => <<<'TXT'
You request and authorize VMLS to contact you with the provided information and to disclose location data so an Operator may contact you about vending and related business services. You authorize calls, texts, emails and or other communications to the business and/or personal contact information submitted, subject to applicable law. You shall ensure that submitted telephone numbers and email addresses belong to persons authorized to receive communications concerning the request. VMLS does not control the Operator's communications. The Location shall communicate opt-outs, contact restrictions, scheduling limits, and complaints directly to the Operator and may also notify VMLS. An Operator is solely responsible for its compliance. For each telephone number and email address submitted, the Location represents that it is the subscriber or customary user or has obtained the subscriber's or customary user's authorization to provide the contact point and to consent to communications from VMLS and from an Operator.
TXT,
            ],
            [
                'heading' => 'Accounts and membership',
                'body' => <<<'TXT'
You must be at least 18 years of age to use the Website and Services. By using the Website and Services and by agreeing to this Agreement you warrant and represent that you are at least 18 years of age. If you create an account on the Website, you are responsible for maintaining the security of your account and you are fully responsible for all activities that occur under the account and any other actions taken in connection with it. We may monitor and review new accounts before you may sign in and start using the Services. Providing false contact information of any kind may result in the termination of your account. You must immediately notify us of any unauthorized uses of your account or any other breaches of security. We will not be liable for any acts or omissions by you, including any damages of any kind incurred as a result of such acts or omissions. We may suspend, disable, or delete your account (or any part thereof) if we determine that you have violated any provision of this Agreement or that your conduct or content would tend to damage our reputation and goodwill. If we delete your account for the foregoing reasons, you may not re-register for our Services. We may block your email address and Internet protocol address to prevent further registration.
TXT,
            ],
            [
                'heading' => 'Intellectual property rights',
                'body' => <<<'TXT'
“Intellectual Property Rights” means all present and future rights conferred by statute, common law or equity in or in relation to any copyright and related rights, trademarks, designs, patents, inventions, goodwill and the right to sue for passing off, rights to inventions, rights to use, and all other intellectual property rights, in each case whether registered or unregistered and including all applications and rights to apply for and be granted, rights to claim priority from, such rights and all similar or equivalent rights or forms of protection and any other results of intellectual activity which subsist or will subsist now or in the future in any part of the world. This Agreement does not transfer to you any intellectual property owned by Vending Machine Location Services or third parties, and all rights, titles, and interests in and to such property will remain (as between the parties) solely with Vending Machine Location Services. All trademarks, service marks, graphics and logos used in connection with the Website and Services, are trademarks or registered trademarks of Vending Machine Location Services or its licensors. Other trademarks, service marks, graphics and logos used in connection with the Website and Services may be the trademarks of other third parties. Your use of the Website and Services grants you no right or license to reproduce or otherwise use any of Vending Machine Location Services or third party trademarks.
TXT,
            ],
            [
                'heading' => 'Indemnification',
                'body' => <<<'TXT'
You agree to indemnify and hold Vending Machine Location Services and its affiliates, directors, officers, employees, agents, suppliers and licensors harmless from and against any liabilities, losses, damages or costs, including reasonable legal fees, incurred in connection with or arising from any third party allegations, claims, actions, disputes, or demands asserted against any of them as a result of or relating to your Content, your use of the Website and Services or any willful misconduct on your part.
TXT,
            ],
            [
                'heading' => 'Severability',
                'body' => <<<'TXT'
All rights and restrictions contained in this Agreement may be exercised and shall be applicable and binding only to the extent that they do not violate any applicable laws and are intended to be limited to the extent necessary so that they will not render this Agreement illegal, invalid or unenforceable. If any provision or portion of any provision of this Agreement shall be held to be illegal, invalid or unenforceable by a court of competent jurisdiction, it is the intention of the parties that the remaining provisions or portions thereof shall constitute their agreement with respect to the subject matter hereof, and all such remaining provisions or portions thereof shall remain in full force and effect.
TXT,
            ],
            [
                'heading' => 'Dispute resolution',
                'body' => <<<'TXT'
The formation, interpretation, and performance of this Agreement and any disputes arising out of or relating to it shall be governed by the laws of Texas, United States, without regard to its conflict of law rules and, to the extent applicable, the laws of United States. The exclusive jurisdiction and venue for actions related to the subject matter hereof shall be the courts located in Texas, United States, and you hereby submit to the personal jurisdiction of such courts. The United Nations Convention on Contracts for the International Sale of Goods does not apply to this Agreement.
TXT,
            ],
            [
                'heading' => 'Marketing Texts, and Emails',
                'body' => <<<'TXT'
By providing an email address or telephone number and electronically signing this Agreement, you authorize VMLS and persons acting on VMLS's behalf to send recurring service, transactional, informational, advertising, and marketing emails, and text messages to the contacts provided. Message frequency varies. Message and data rates may apply. Marketing consent is not a condition of receiving the Location Services. You may withhold marketing consent and still submit a request by emailing support@vendingmachinelocationsforsale.com before submission to obtain an alternate electronic acceptance method. You may revoke marketing consent at any time by replying STOP, QUIT, END, CANCEL, or UNSUBSCRIBE to a text, using an unsubscribe link in your profile settings, or communicating a clear request via email. VMLS will honor revocation within the period required by law. Service, security, legal, request-related, Operator-introduction, and transactional communications may continue.
TXT,
            ],
            [
                'heading' => 'Changes and amendments',
                'body' => <<<'TXT'
We reserve the right to modify this Agreement or its terms related to the Website and Services at any time at our discretion. When we do, we will revise the updated date at the bottom of this page. We may also provide notice to you in other ways at our discretion, such as through the contact information you have provided.

An updated version of this Agreement will be effective immediately upon the posting of the revised Agreement unless otherwise specified. Your continued use of the Website and Services after the effective date of the revised Agreement (or such other act specified at that time) will constitute your consent to those changes.
TXT,
            ],
            [
                'heading' => 'Acceptance of these terms',
                'body' => <<<'TXT'
You acknowledge that you have read this Agreement and agree to all its terms and conditions. By accessing and using the Website and Services you agree to be bound by this Agreement. If you do not agree to abide by the terms of this Agreement, you are not authorized to access or use the Website and Services. This policy has been created with the help of the terms and conditions generator (https://www.websitepolicies.com/terms-and-conditions-generator).
TXT,
            ],
            [
                'heading' => 'Contacting us',
                'body' => <<<'TXT'
If you have any questions, concerns, or complaints regarding this Agreement, we encourage you to contact us using the details below:

support@vendingmachinelocationsforsale.com

https://vendingmachinelocationsforsale.com/contact
TXT,
            ],
        ];
    }
}
