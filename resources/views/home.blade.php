<x-layouts.app>
    <x-slot name="title">{{ __('CRM for growing sales teams') }}</x-slot>

    <div class="crm-landing">
        {{-- Hero --}}
        <section class="hero-mesh relative overflow-hidden text-white">
            <div class="hero-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 md:pb-24 md:pt-20 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <div class="text-center lg:text-left">
                        <span class="hero-label">
                            {{ __('7-day free trial') }}
                        </span>
                        <h1 class="mt-6 text-4xl font-bold leading-[1.1] tracking-tight text-white md:text-5xl lg:text-[3.25rem]">
                            {{ __('The CRM your sales team actually uses') }}
                        </h1>
                        <p class="mt-6 max-w-xl text-base leading-relaxed text-primary-100 md:text-lg lg:mx-0">
                            <span class="font-semibold text-white">{{ config('app.name') }}</span>
                            {{ __(' brings leads, deals, contacts, and follow-ups into one workspace—so nothing slips through the cracks.') }}
                        </p>

                        <div class="mt-9 flex flex-col items-center gap-3 sm:flex-row lg:justify-start">
                            <a href="#pricing" class="btn-hero-primary">
                                {{ __('Start 7-day trial') }}
                            </a>
                            @guest
                                <a href="{{ route('register') }}" class="btn-hero-secondary">
                                    {{ __('Create account') }}
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn-hero-secondary">
                                    {{ __('Open workspace') }}
                                </a>
                            @endguest
                        </div>
                        <p class="mt-5 text-sm text-primary-200/90">
                            {{ __('No credit card · Cancel anytime · Secure workspaces') }}
                        </p>
                    </div>

                    {{-- Product preview (decorative) --}}
                    <div class="hero-preview mx-auto w-full max-w-lg rounded-2xl p-5 lg:max-w-none" aria-hidden="true">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="text-xs font-medium text-primary-100">{{ config('app.name') }} {{ __('workspace') }}</span>
                            <span class="rounded-full bg-emerald-400/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-200">{{ __('Live') }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach ([
                                ['$42k', __('Pipeline')],
                                ['24', __('Leads')],
                                ['8', __('Hot')],
                            ] as $kpi)
                                <div class="rounded-lg bg-white/10 px-2 py-3 text-center">
                                    <p class="text-lg font-bold text-white">{{ $kpi[0] }}</p>
                                    <p class="text-[10px] uppercase tracking-wide text-primary-200">{{ $kpi[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach ([
                                [__('Qualified lead'), 'Acme Corp', '85%'],
                                [__('Proposal sent'), 'Northwind', '$12k'],
                                [__('Follow-up today'), 'Fabrikam', '—'],
                            ] as $row)
                                <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 text-xs">
                                    <span class="font-medium text-white">{{ $row[0] }}</span>
                                    <span class="text-primary-200">{{ $row[1] }}</span>
                                    <span class="font-semibold text-primary-100">{{ $row[2] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mx-auto mt-14 grid max-w-4xl grid-cols-2 gap-3 sm:grid-cols-4 md:gap-4">
                    @foreach ([
                        ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'label' => __('Leads'), 'desc' => __('Capture & score')],
                        ['icon' => 'M9 17V7m0 10H5m4 0h6m4 0v-4m0 4h-4m-4-8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => __('Pipeline'), 'desc' => __('Kanban board')],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => __('Team'), 'desc' => __('Roles & access')],
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => __('Reports'), 'desc' => __('Real-time KPIs')],
                    ] as $stat)
                        <div class="hero-stat rounded-xl px-3 py-3.5 text-center sm:px-4">
                            <svg class="mx-auto h-5 w-5 text-primary-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                            </svg>
                            <p class="mt-2 text-[11px] font-semibold uppercase tracking-wider text-primary-200">{{ $stat['label'] }}</p>
                            <p class="mt-0.5 text-sm font-medium text-white">{{ $stat['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Trust strip --}}
        <section class="trust-strip border-b border-gray-100 bg-white py-8" aria-label="{{ __('Trust') }}">
            <div class="mx-auto max-w-6xl px-4 lg:px-8">
                <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">
                    {{ __('Everything you need to close more deals') }}
                </p>
                <ul class="trust-list mt-6">
                    @foreach ([
                        ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'text' => __('Secure workspaces')],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => __('Tenant isolation')],
                        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => __('7-day trial')],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => __('Team roles & invites')],
                    ] as $trust)
                        <li class="trust-item">
                            <span class="trust-icon" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trust['icon'] }}" />
                                </svg>
                            </span>
                            <span>{{ $trust['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="scroll-mt-28 bg-gray-50/80 py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="section-label">{{ __('Product') }}</span>
                    <h2 class="section-title">{{ __('Built for modern sales teams') }}</h2>
                    <p class="section-lead">
                        {{ __('Every feature connects to the pipeline—no bolted-on modules or duplicate data entry.') }}
                    </p>
                </div>

                <div class="mt-16 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['highlight' => false, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => __('Lead management'), 'body' => __('Sources, owners, AI scores, and one-click conversion to deals.')],
                        ['highlight' => false, 'icon' => 'M9 17V7m0 10H5m4 0h6m4 0v-4m0 4h-4m-4-8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => __('Visual pipeline'), 'body' => __('Drag deals across stages. See value and owners without leaving the board.')],
                        ['highlight' => false, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'title' => __('Contacts'), 'body' => __('One profile per relationship, linked to leads and deals automatically.')],
                        ['highlight' => false, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => __('Activities'), 'body' => __('Calls, tasks, and meetings with follow-up reminders on your dashboard.')],
                        ['highlight' => false, 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => __('Reports'), 'body' => __('Pipeline value, trends, and outcomes—no spreadsheet exports required.')],
                        ['highlight' => true, 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'title' => __('AI assistant'), 'body' => __('Score leads and draft follow-ups in seconds—built into your workflow.')],
                    ] as $feature)
                        <article @class([
                            'feature-card',
                            'feature-card--highlight' => $feature['highlight'],
                        ])>
                            <span class="feature-icon">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                </svg>
                            </span>
                            <h3 class="mt-5 text-lg font-semibold text-primary-900">{{ $feature['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $feature['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="border-y border-gray-100 bg-white py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 lg:px-8">
                <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-20">
                    <div>
                        <span class="section-label">{{ __('Onboarding') }}</span>
                        <h2 class="section-title">{{ __('Live in minutes, not months') }}</h2>
                        <p class="section-lead">
                            {{ __('Spin up a workspace, invite your team, and log your first lead the same day.') }}
                        </p>

                        <ol class="mt-10">
                            @foreach ([
                                __('Choose a plan and create your workspace'),
                                __('Invite teammates and set roles'),
                                __('Add leads and work the deal board'),
                                __('Track results in reports'),
                            ] as $index => $step)
                                <li class="step-item">
                                    <span class="step-num">{{ $index + 1 }}</span>
                                    <span class="pt-1.5 text-base text-gray-700">{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="info-panel">
                        <h3 class="text-xl font-bold text-primary-900">{{ __('Two sides, one platform') }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ __('Clear separation between you and your customers.') }}</p>
                        <div class="mt-8 space-y-4">
                            <div class="info-row">
                                <span class="shrink-0 rounded-lg bg-primary-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary-700">{{ __('Vendor') }}</span>
                                <p class="text-sm leading-relaxed text-gray-600">{{ __('You manage plans, tenants, subscriptions, and payments in the platform console.') }}</p>
                            </div>
                            <div class="info-row">
                                <span class="shrink-0 rounded-lg bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">{{ __('Customer') }}</span>
                                <p class="text-sm leading-relaxed text-gray-600">{{ __('Each buyer gets an isolated CRM workspace for leads, deals, and team settings.') }}</p>
                            </div>
                        </div>
                        @guest
                            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-8">
                                <x-button-link.primary href="{{ route('register') }}" class="!py-3 !px-6">
                                    {{ __('Get started free') }}
                                </x-button-link.primary>
                                <x-link href="{{ route('login') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-800">
                                    {{ __('Customer login') }} →
                                </x-link>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="pricing-section scroll-mt-28 py-20 md:py-28">
            <div class="mx-auto max-w-6xl px-4 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="section-label">{{ __('Pricing') }}</span>
                    <h2 class="section-title">{{ __('Pick a plan. Try it free for 7 days.') }}</h2>
                    <p class="section-lead">
                        {{ __('Starter for small teams. Growth for automation, AI, and advanced reporting.') }}
                    </p>
                </div>

                <div class="pricing mx-auto mt-14">
                    <x-plans.all
                        :products="config('platform.marketing_product_slugs')"
                        calculate-saving-rates="false"
                        :show-default-product="false"
                    />
                </div>

                <p class="mx-auto mt-12 max-w-lg text-center text-sm text-gray-500">
                    {{ __('Trial included on all plans.') }}
                    <a href="mailto:{{ config('platform.support_email') }}" class="font-medium text-primary-600 hover:text-primary-800 hover:underline">{{ __('Questions? Email us') }}</a>
                </p>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="scroll-mt-28 bg-gray-50 py-20 md:py-24">
            <div class="mx-auto max-w-3xl px-4 lg:px-8">
                <div class="text-center">
                    <span class="section-label">{{ __('FAQ') }}</span>
                    <h2 class="section-title">{{ __('Common questions') }}</h2>
                </div>

                <div class="faq-wrap mt-12">
                    <x-accordion>
                        <x-accordion.item active="true" name="faq">
                            <x-slot name="title">{{ __('What is :app?', ['app' => config('app.name')]) }}</x-slot>
                            <p>{{ __('A multi-tenant CRM: leads, contacts, pipeline, activities, reports, and team settings—sold per organization.') }}</p>
                        </x-accordion.item>
                        <x-accordion.item name="faq">
                            <x-slot name="title">{{ __('How does the free trial work?') }}</x-slot>
                            <p>{{ __('Select a plan to start a 7-day trial with full access. Subscribe before it ends to keep your workspace and data.') }}</p>
                        </x-accordion.item>
                        <x-accordion.item name="faq">
                            <x-slot name="title">{{ __('Can I invite my team?') }}</x-slot>
                            <p>{{ __('Yes. Admins invite by email, assign roles, and control who manages billing vs. day-to-day CRM.') }}</p>
                        </x-accordion.item>
                        <x-accordion.item name="faq">
                            <x-slot name="title">{{ __('Is our data private?') }}</x-slot>
                            <p>{{ __('Each company has its own tenant. Your records are never visible to other customers on the platform.') }}</p>
                        </x-accordion.item>
                        <x-accordion.item name="faq">
                            <x-slot name="title">{{ __('What payments do you accept?') }}</x-slot>
                            <p>{{ __('Card checkout via the payment providers enabled on your account (e.g. Stripe or Paddle).') }}</p>
                        </x-accordion.item>
                    </x-accordion>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="cta-band py-16 text-white md:py-24">
            <div class="mx-auto max-w-3xl px-4 text-center lg:px-8">
                <h2 class="text-3xl font-bold tracking-tight text-white md:text-4xl">
                    {{ __('Ready to run a clearer pipeline?') }}
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-primary-100">
                    {{ __('Join teams on :app. Start your trial in under a minute.', ['app' => config('app.name')]) }}
                </p>
                <div class="mt-9 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="#pricing" class="btn-cta-light">{{ __('View plans') }}</a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-cta-outline">{{ __('Sign up free') }}</a>
                    @endguest
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
