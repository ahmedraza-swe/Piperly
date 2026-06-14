<?php

namespace Tests\Feature\Livewire\Auth\Register;

use App\Livewire\Auth\Register\OneTimePasswordRegistration;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OneTimePasswordService;
use App\Services\TenantCreationService;
use App\Services\TrialProvisioningService;
use App\Services\UserService;
use App\Validator\RegisterValidator;
use Illuminate\Contracts\Validation\Validator;
use Livewire\Livewire;
use Mockery;
use Tests\Feature\FeatureTest;

class OneTimePasswordRegistrationTest extends FeatureTest
{
    private RegisterValidator $mockRegisterValidator;

    private UserService $mockUserService;

    private OneTimePasswordService $mockOtpService;

    private TenantCreationService $mockTenantCreationService;

    private TrialProvisioningService $mockTrialProvisioningService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRegisterValidator = Mockery::mock(RegisterValidator::class);
        $this->mockUserService = Mockery::mock(UserService::class);
        $this->mockOtpService = Mockery::mock(OneTimePasswordService::class);
        $this->mockTenantCreationService = Mockery::mock(TenantCreationService::class);
        $this->mockTrialProvisioningService = Mockery::mock(TrialProvisioningService::class);

        $this->app->instance(RegisterValidator::class, $this->mockRegisterValidator);
        $this->app->instance(UserService::class, $this->mockUserService);
        $this->app->instance(OneTimePasswordService::class, $this->mockOtpService);
        $this->app->instance(TenantCreationService::class, $this->mockTenantCreationService);
        $this->app->instance(TrialProvisioningService::class, $this->mockTrialProvisioningService);
    }

    public function test_renders_registration_form_view()
    {
        Livewire::test(OneTimePasswordRegistration::class)
            ->assertViewIs('livewire.auth.register.registration-form');
    }

    public function test_successful_registration_with_valid_data()
    {
        $email = 'newuser'.rand(1, 10000).'@example.com';
        $name = 'New User';
        $companyName = 'Acme Inc';
        $userFields = [
            'email' => $email,
            'name' => $name,
            'company_name' => $companyName,
        ];

        $user = User::factory()->create(['email' => $email, 'name' => $name]);
        $tenant = Tenant::factory()->create();

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->andReturn(false);

        $this->mockRegisterValidator
            ->shouldReceive('validate')
            ->once()
            ->with($userFields)
            ->andReturn($validator);

        $this->mockUserService
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        $this->mockUserService
            ->shouldReceive('createUser')
            ->once()
            ->with($userFields)
            ->andReturn($user);

        $this->mockTenantCreationService
            ->shouldReceive('createTenantWithName')
            ->once()
            ->with($user, $companyName)
            ->andReturn($tenant);

        $this->mockTrialProvisioningService
            ->shouldReceive('provisionForNewWorkspace')
            ->once()
            ->with($user, $tenant);

        $this->mockOtpService
            ->shouldReceive('sendCode')
            ->once()
            ->with($user)
            ->andReturn(true);

        Livewire::test(OneTimePasswordRegistration::class)
            ->set('email', $email)
            ->set('name', $name)
            ->set('company_name', $companyName)
            ->call('register')
            ->assertRedirect(route('login', ['email' => $email]))
            ->assertHasNoErrors();
    }

    public function test_registration_with_existing_user_email()
    {
        $email = 'existing'.rand(1, 10000).'@example.com';
        $name = 'New User';
        $companyName = 'Acme Inc';
        $userFields = [
            'email' => $email,
            'name' => $name,
            'company_name' => $companyName,
        ];

        $existingUser = User::factory()->create(['email' => $email]);

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->andReturn(false);

        $this->mockRegisterValidator
            ->shouldReceive('validate')
            ->once()
            ->with($userFields)
            ->andReturn($validator);

        $this->mockUserService
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($existingUser);

        Livewire::test(OneTimePasswordRegistration::class)
            ->set('email', $email)
            ->set('name', $name)
            ->set('company_name', $companyName)
            ->call('register')
            ->assertHasErrors(['email' => 'This email is already registered. Please log in instead.']);
    }

    public function test_registration_when_otp_service_fails_to_send_code()
    {
        $email = 'newuser'.rand(1, 10000).'@example.com';
        $name = 'New User';
        $companyName = 'Acme Inc';
        $userFields = [
            'email' => $email,
            'name' => $name,
            'company_name' => $companyName,
        ];

        $user = User::factory()->create(['email' => $email, 'name' => $name]);
        $tenant = Tenant::factory()->create();

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->andReturn(false);

        $this->mockRegisterValidator
            ->shouldReceive('validate')
            ->once()
            ->with($userFields)
            ->andReturn($validator);

        $this->mockUserService
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        $this->mockUserService
            ->shouldReceive('createUser')
            ->once()
            ->with($userFields)
            ->andReturn($user);

        $this->mockTenantCreationService
            ->shouldReceive('createTenantWithName')
            ->once()
            ->with($user, $companyName)
            ->andReturn($tenant);

        $this->mockTrialProvisioningService
            ->shouldReceive('provisionForNewWorkspace')
            ->once()
            ->with($user, $tenant);

        $this->mockOtpService
            ->shouldReceive('sendCode')
            ->once()
            ->with($user)
            ->andReturn(false);

        Livewire::test(OneTimePasswordRegistration::class)
            ->set('email', $email)
            ->set('name', $name)
            ->set('company_name', $companyName)
            ->call('register')
            ->assertHasErrors(['email' => 'Failed to send one-time password. Please try again later.']);
    }

    public function test_registration_requires_company_name()
    {
        Livewire::test(OneTimePasswordRegistration::class)
            ->set('email', 'user@example.com')
            ->set('name', 'New User')
            ->set('company_name', '')
            ->call('register')
            ->assertHasErrors(['company_name']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
