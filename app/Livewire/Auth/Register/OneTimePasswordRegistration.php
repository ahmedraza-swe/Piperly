<?php

namespace App\Livewire\Auth\Register;

use App\Services\OneTimePasswordService;
use App\Services\TenantCreationService;
use App\Services\UserService;
use App\Validator\RegisterValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class OneTimePasswordRegistration extends Component
{
    public string $email;

    public string $name;
    public string $company_name;

    private RegisterValidator $registerValidator;

    private UserService $userService;

    private OneTimePasswordService $oneTimePasswordService;

    private TenantCreationService $tenantCreationService;

    public function boot(
        RegisterValidator $registerValidator,
        UserService $userService,
        OneTimePasswordService $oneTimePasswordService,
        TenantCreationService $tenantCreationService,
    ) {
        $this->registerValidator = $registerValidator;
        $this->userService = $userService;
        $this->oneTimePasswordService = $oneTimePasswordService;
        $this->tenantCreationService = $tenantCreationService;
    }

    public function render(): View
    {
        return view('livewire.auth.register.registration-form');
    }

    public function register(): void
    {
        if (trim($this->company_name) === '') {
            $this->addError('company_name', __('The company / workspace name field is required.'));

            return;
        }

        $userFields = [
            'email' => $this->email,
            'name' => $this->name,
            'company_name' => $this->company_name,
        ];

        $validator = $this->registerValidator->validate($userFields);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = $this->userService->findByEmail($this->email);

        if ($user) {
            $this->addError('email', __('This email is already registered. Please log in instead.'));

            return;
        }

        $user = $this->userService->createUser($userFields);
        $this->tenantCreationService->createTenantWithName($user, $this->company_name);

        if (! $this->oneTimePasswordService->sendCode($user)) {
            $this->addError('email', __('Failed to send one-time password. Please try again later.'));

            return;
        }

        $this->redirect(route('login', ['email' => $this->email]));
    }
}
