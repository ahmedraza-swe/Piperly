<?php

namespace App\Http\Controllers;

use App\Constants\SubscriptionType;
use App\Dto\SubscriptionCheckoutDto;
use App\Models\Plan;
use App\Services\CalculationService;
use App\Services\DiscountService;
use App\Services\SessionService;
use App\Services\SubscriptionService;
use App\Services\TenantSubscriptionService;
use App\Services\UserDashboardService;

class SubscriptionCheckoutController extends Controller
{
    public function __construct(
        private DiscountService $discountService,
        private CalculationService $calculationService,
        private SubscriptionService $subscriptionService,
        private SessionService $sessionService,
        private TenantSubscriptionService $tenantSubscriptionService,
        private UserDashboardService $userDashboardService,
    ) {}

    public function trialCheckout(string $planSlug)
    {
        if (! config('app.trial_without_payment.enabled')) {
            return redirect()->route('checkout.subscription', ['planSlug' => $planSlug]);
        }

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        if (! $plan->has_trial) {
            return redirect()->route('checkout.subscription', ['planSlug' => $planSlug]);
        }

        $checkoutDto = $this->prepareCheckoutDto($planSlug, SubscriptionCheckoutDto::MODE_TRIAL);

        $this->sessionService->saveSubscriptionCheckoutDto($checkoutDto);

        return view('checkout.local-subscription');
    }

    public function subscriptionCheckout(string $planSlug)
    {
        Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $checkoutDto = $this->prepareCheckoutDto($planSlug, SubscriptionCheckoutDto::MODE_SUBSCRIBE);
        $checkoutDto->skipPaymentProviderTrial = true;

        $this->sessionService->saveSubscriptionCheckoutDto($checkoutDto);

        return view('checkout.subscription');
    }

    public function convertLocalSubscriptionCheckout(?string $subscriptionUuid = null)
    {
        $subscription = $this->subscriptionService->findByUuidOrFail($subscriptionUuid);

        if (! $this->subscriptionService->isLocalSubscription($subscription)) {
            return redirect()->route('home');
        }

        $planSlug = $subscription->plan->slug;
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        if ($checkoutDto->planSlug !== $planSlug) {
            $checkoutDto = $this->sessionService->resetSubscriptionCheckoutDto();
        }

        $checkoutDto->mode = SubscriptionCheckoutDto::MODE_SUBSCRIBE;
        $checkoutDto->skipPaymentProviderTrial = true;
        $checkoutDto->quantity = max($checkoutDto->quantity, $this->tenantSubscriptionService->calculateCurrentSubscriptionQuantity($subscription));
        $checkoutDto->planSlug = $planSlug;
        $checkoutDto->subscriptionId = $subscription->id;

        $this->sessionService->saveSubscriptionCheckoutDto($checkoutDto);

        $totals = $this->calculationService->calculatePlanTotals(
            auth()->user(),
            $planSlug,
            $checkoutDto?->discountCode,
            $checkoutDto->quantity,
        );

        return view('checkout.convert-local-subscription', [
            'plan' => $plan,
            'totals' => $totals,
            'checkoutDto' => $checkoutDto,
        ]);
    }

    public function subscriptionCheckoutSuccess()
    {
        $result = $this->handleSubscriptionSuccess();

        if (! $result) {
            return redirect()->route('home');
        }

        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();
        $subscription = $this->subscriptionService->findById($checkoutDto->subscriptionId);
        $isLocalTrial = $subscription && $subscription->type === SubscriptionType::LOCALLY_MANAGED;

        $this->sessionService->resetSubscriptionCheckoutDto();

        return $this->redirectToWorkspace(
            $isLocalTrial
                ? __('Your trial workspace is ready.')
                : __('Thank you! Your subscription is being activated.')
        );
    }

    public function convertLocalSubscriptionCheckoutSuccess()
    {
        $result = $this->handleSubscriptionSuccess();

        if (! $result) {
            return redirect()->route('home');
        }

        $this->sessionService->resetSubscriptionCheckoutDto();

        return $this->redirectToWorkspace(__('Your subscription is being activated.'));
    }

    private function prepareCheckoutDto(string $planSlug, string $mode): SubscriptionCheckoutDto
    {
        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        if ($checkoutDto->planSlug !== $planSlug || $checkoutDto->mode !== $mode) {
            $checkoutDto = $this->sessionService->resetSubscriptionCheckoutDto();
        }

        $checkoutDto->planSlug = $planSlug;
        $checkoutDto->mode = $mode;

        return $checkoutDto;
    }

    private function redirectToWorkspace(string $message)
    {
        $user = auth()->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        return redirect($this->userDashboardService->getUserDashboardUrl($user))
            ->with('success', $message);
    }

    private function handleSubscriptionSuccess(): bool
    {
        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        if ($checkoutDto->subscriptionId === null) {
            return false;
        }

        $this->subscriptionService->setAsPending($checkoutDto->subscriptionId);
        $this->subscriptionService->updateUserSubscriptionTrials($checkoutDto->subscriptionId);

        if ($checkoutDto->discountCode !== null) {
            $this->discountService->redeemCodeForSubscription($checkoutDto->discountCode, auth()->user(), $checkoutDto->subscriptionId);
        }

        return true;
    }
}
