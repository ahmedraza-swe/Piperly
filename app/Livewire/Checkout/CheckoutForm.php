<?php

namespace App\Livewire\Checkout;

use App\Exceptions\LoginException;
use App\Exceptions\NoPaymentProvidersAvailableException;
use App\Models\User;
use App\Services\LoginService;
use App\Services\OneTimePasswordService;
use App\Services\PaymentProviders\PaymentService;
use App\Services\UserService;
use App\Validator\LoginValidator;
use App\Validator\RegisterValidator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\OneTimePasswords\Rules\OneTimePasswordRule;
use Throwable;

class CheckoutForm extends Component
{
    public $intro;

    public $name;

    public $email;

    public $password;

    public bool $minimalSignup = false;

    public $paymentProvider;

    public $oneTimePassword;

    public bool $showOtpForm = false;

    protected bool $otpVerified = false;

    protected $paymentProviders = [];

    public function mount(string $intro = '')
    {
        $this->intro = $intro;
    }

    public function render(PaymentService $paymentService)
    {
        return view('livewire.checkout.checkout-form', [
            'userExists' => $this->userExists($this->email),
            'paymentProviders' => $this->getPaymentProviders($paymentService),
            'otpEnabled' => config('app.otp_login_enabled'),
            'otpVerified' => $this->otpVerified,
        ]);
    }

    public function handleLoginOrRegistration(
        LoginValidator $loginValidator,
        RegisterValidator $registerValidator,
        UserService $userService,
        LoginService $loginService,
    ) {
        if (! auth()->check()) {
            if (config('app.otp_login_enabled')) {
                $this->verifyOtpAndProceed($userService, $loginService);
            } else {
                if ($this->userExists($this->email)) {
                    $this->loginUser($loginValidator, $loginService);
                } else {
                    $this->registerUser($registerValidator, $userService);
                }
            }
        }

        $user = auth()->user();
        if (! $user) {
            $this->redirect(route('login'));

            return;
        }

        $this->handleBlockedUser($user);
    }

    protected function handleBlockedUser(User $user)
    {
        if ($user->is_blocked) {
            auth()->logout();
            throw ValidationException::withMessages([
                'email' => __('Your account is blocked, please contact support.'),
            ]);
        }
    }

    protected function loginUser(LoginValidator $loginValidator, LoginService $loginService)
    {
        $fields = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        $validator = $loginValidator->validate($fields);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $result = $loginService->attempt([
                'email' => $this->email,
                'password' => $this->password,
            ], true);
        } catch (Throwable $e) {  // usually thrown when 2FA is enabled so user need to be redirected to login page to enter 2FA code
            throw new LoginException;
        }

        if (! $result) {
            $message = $this->minimalSignup
                ? __('Incorrect password. Use "Email me a sign-in link" below if you started a trial without choosing a password.')
                : __('Wrong email or password');

            throw ValidationException::withMessages([
                'password' => $message,
            ]);
        }
    }

    public function requestSignInLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink(['email' => strtolower(trim($this->email))]);

        $hint = config('mail.default') === 'log'
            ? ' '.__('With mail logging enabled, open storage/logs/laravel.log to find the reset link.')
            : '';

        session()->flash(
            'sign_in_link_sent',
            __('If this email is registered, we sent a sign-in link to your inbox.').$hint
        );
    }

    protected function registerUser(RegisterValidator $registerValidator, UserService $userService)
    {
        $fields = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];

        $validator = $registerValidator->validate(
            $fields,
            passwordConfirmed: false,
            passwordOptional: $this->minimalSignup,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (! $this->minimalSignup) {
            $userData['password'] = $this->password;
        }

        $user = $userService->createUser($userData);

        auth()->login($user);

        $user->sendEmailVerificationNotification();

        if ($this->minimalSignup) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return $user;
    }

    protected function userExists(?string $email): bool
    {
        if ($email === null) {
            return false;
        }

        return User::where('email', $email)->exists();
    }

    protected function getPaymentProviders(PaymentService $paymentService)
    {
        if (count($this->paymentProviders) > 0) {
            return $this->paymentProviders;
        }

        $this->paymentProviders = $paymentService->getActivePaymentProviders(true);

        if (empty($this->paymentProviders)) {
            logger()->error('No payment providers available');

            throw new NoPaymentProvidersAvailableException('No payment providers available');
        }

        if ($this->paymentProvider === null) {
            $this->paymentProvider = $this->paymentProviders[0]->getSlug();
        }

        return $this->paymentProviders;
    }

    public function sendOtpCode(
        UserService $userService,
        LoginValidator $loginValidator,
        RegisterValidator $registerValidator,
        OneTimePasswordService $oneTimePasswordService,
    ) {
        if (! config('app.otp_login_enabled')) {
            return;
        }

        $user = $userService->findByEmail($this->email);

        if ($user) {
            $fields = [
                'email' => $this->email,
            ];

            $validator = $loginValidator->validate($fields);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        } else {

            $fields = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            $validator = $registerValidator->validate($fields, passwordConfirmed: false);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = $userService->createUser([
                'name' => $this->name,
                'email' => $this->email,
            ]);
        }

        if (! $oneTimePasswordService->sendCode($user)) {
            $this->addError('email', __('Failed to send one-time password. Please try again later.'));

            return;
        }

        $this->showOtpForm = true;
    }

    public function verifyOtpAndProceed(
        UserService $userService,
        LoginService $loginService,
    ) {
        if (! config('app.otp_login_enabled')) {
            return;
        }

        $user = $userService->findByEmail($this->email);

        if (! $user) {
            $this->addError('oneTimePassword', __('User not found.'));

            return;
        }

        $this->validate([
            'oneTimePassword' => ['required', new OneTimePasswordRule($user)],
        ]);

        $loginService->authenticateUser($user, true);

        $this->handleBlockedUser($user);

        $this->otpVerified = true;

        // Force re-render to update button state
        $this->dispatch('$refresh');
    }

    public function resendOtpCode(
        UserService $userService,
        LoginValidator $loginValidator,
        RegisterValidator $registerValidator,
        OneTimePasswordService $oneTimePasswordService,
    ) {
        $this->sendOtpCode($userService, $loginValidator, $registerValidator, $oneTimePasswordService);
    }

    public function isCheckoutButtonEnabled(): bool
    {
        $otpEnabled = config('app.otp_login_enabled');
        $isAuthenticated = auth()->check();

        if (! $otpEnabled || $isAuthenticated || $this->otpVerified) {
            return true;
        }

        if ($this->showOtpForm) {
            return ! empty(trim($this->email)) && ! empty(trim($this->oneTimePassword));
        }

        return false;
    }
}
