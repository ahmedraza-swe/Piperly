<?php

namespace App\Dto;

class SubscriptionCheckoutDto
{
    public const MODE_TRIAL = 'trial';

    public const MODE_SUBSCRIBE = 'subscribe';

    public ?string $discountCode = null;

    public ?string $planSlug = null;

    public ?string $subscriptionId = null;

    public int $quantity = 1;

    public ?string $tenantUuid = null;

    /** @var self::MODE_TRIAL|self::MODE_SUBSCRIBE */
    public string $mode = self::MODE_SUBSCRIBE;

    public bool $skipPaymentProviderTrial = false;
}
