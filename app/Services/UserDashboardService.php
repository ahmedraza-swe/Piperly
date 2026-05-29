<?php

namespace App\Services;

use App\Filament\Admin\Pages\PlatformHub;
use App\Filament\Dashboard\Pages\TenantDashboard;
use App\Models\User;

class UserDashboardService
{
    public function getUserDashboardUrl(User $user): string
    {
        if ($user->isAdmin() && ! $user->tenants()->exists()) {
            return PlatformHub::getUrl();
        }

        $tenant = $user->tenants()->orderByPivot('is_default', 'desc')->first();

        if ($tenant !== null) {
            return TenantDashboard::getUrl(['tenant' => $tenant], panel: 'dashboard');
        }

        return route('app.landing');
    }
}
