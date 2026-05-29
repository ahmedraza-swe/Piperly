<x-layouts.app>
    <x-slot name="title">{{ __('Coming Soon - CRM Platform') }}</x-slot>

    <section class="relative min-h-[80vh] overflow-hidden bg-[#0A0A0F] text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(99,102,241,0.2),transparent_40%),radial-gradient(circle_at_80%_20%,rgba(16,185,129,0.14),transparent_35%)]"></div>
        <div class="relative mx-auto flex min-h-[80vh] max-w-5xl items-center justify-center px-4">
            <div class="w-full max-w-3xl rounded-2xl border border-white/15 bg-white/[0.04] p-8 text-center">
                <p class="inline-flex rounded-full border border-indigo-300/40 bg-indigo-500/15 px-3 py-1 text-xs text-indigo-200">
                    {{ __('AI-Powered Multi-Tenant CRM') }}
                </p>
                <h1 class="mt-5 text-4xl font-bold md:text-5xl">{{ __('Coming Soon') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-slate-300">
                    {{ __('We are building a premium CRM for growing teams. Sign up for early access or login to continue.') }}
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/30 px-6 py-3 text-sm font-semibold text-white hover:border-white/60">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-[0_0_24px_rgba(99,102,241,0.35)] hover:bg-indigo-400">{{ __('Sign Up') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
