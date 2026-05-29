<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Trait\RedirectAwareTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantCreationService;
use App\Services\UserService;
use App\Validator\RegisterValidator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;
    use RedirectAwareTrait;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    //    protected $redirectTo = '/email/verify';

    public function __construct(
        private RegisterValidator $registerValidator,
        private UserService $userService,
        private TenantCreationService $tenantCreationService,
    ) {
        $this->middleware('guest');
    }

    public function redirectPath()
    {
        return $this->getRedirectUrl(auth()->user());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = $this->registerValidator->validate($data);
        $validator->addRules([
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data)
    {
        return $this->userService->createUser($data);
    }

    protected function registered(Request $request, User $user): void
    {
        $companyName = (string) $request->input('company_name', '');
        $this->tenantCreationService->createTenantWithName($user, $companyName);
    }

    /**
     * Show the application registration form.
     *
     * @return View
     */
    public function showRegistrationForm()
    {
        if (url()->previous() != route('login') && Redirect::getIntendedUrl() === null) {
            Redirect::setIntendedUrl(url()->previous()); // make sure we redirect back to the page we came from
        }

        return view('auth.register', [
            'isOtpLoginEnabled' => config('app.otp_login_enabled'),
        ]);
    }
}
