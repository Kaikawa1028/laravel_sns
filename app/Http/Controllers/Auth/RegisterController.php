<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Authy\AuthyApi;
use Laravel\Socialite\Facades\Socialite;

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

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country_code' => 'required',
            'phone_number' => 'required|numeric'
        ]);
    }

    /**
     * @param Request $request
     * @param AuthyApi $authyApi
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function register(Request $request, AuthyApi $authyApi)
    {
        $this->validator($request->all())->validate();

        DB::beginTransaction();
        event(new Registered($newUser = $this->create($request->all())));
        
        $authyUser = $authyApi->registerUser(
            $newUser->email,
            $newUser->phone_number,
            $newUser->country_code
        );

        if ($authyUser->ok()) {
            $newUser->authy_id = $authyUser->id();
            $newUser->save();
            $request->session()->flash(
                'status',
                "User created successfully"
            );

            $authyApi->requestSms($newUser->authy_id);
            DB::commit();

            return redirect()->route('verify.show', ["authy_id" => $newUser->authy_id]);
        } else {
            $errors = $this->getAuthyErrors($authyUser->errors());
            DB::rollback();

            return view('auth.register', ['errors' => new MessageBag($errors)]);
        }
    }

    /**
     * This controller function handles the submission form
     *
     * @param Request $request Current User Request
     * @param AuthyApi $authyApi Authy Client
     * @return mixed Response view
     */
    public function verify(Request $request, 
                           AuthyApi $authyApi)
    {
        $token = $request->input('token');
        $user = User::where("authy_id", $request->input("authy_id"))->first();
        $verification = $authyApi->verifyToken($user->authy_id, $token);

        if ($verification->ok()) {
            $user->sms_verified = true;
            $user->save();

            $this->guard()->login($user);
            return redirect($this->redirectPath());
        } else {
            $errors = $this->getAuthyErrors($verification->errors());
            return view('', ['errors' => new MessageBag($errors)]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country_code' => $data['country_code'],
            'phone_number' => $data['phone_number']
        ]);
    }

    /**
     * @param Request $request
     * @param int $authy_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showVerificationForm(Request $request, int $authy_id)
    {
        return view('auth.sms')->with('authy_id', $authy_id);
    }

    public function showProviderUserRegistrationForm(Request $request, string $provider)
    {
        $token = $request->token;

        $providerUser = Socialite::driver($provider)->userFromToken($token);

        return view('auth.social_register', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $token,
        ]);
    }

    public function registerProviderUser(Request $request, string $provider)
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'token' => ['required', 'string'],
        ]);

        $token = $request->token;

        $providerUser = Socialite::driver($provider)->userFromToken($token);

        $user = User::create([
            'name' => $request->name,
            'email' => $providerUser->getEmail(),
            'password' => null,
        ]);

        $this->guard()->login($user, true);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    private function getAuthyErrors($authyErrors)
    {
        $errors = [];
        foreach ($authyErrors as $field => $message) {
            array_push($errors, $field . ': ' . $message);
        }
        return $errors;
    }
}
