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
    public function register(Request $request, AuthyApi $authy_api)
    {
        $this->validator($request->all())->validate();

        DB::beginTransaction();
        event(new Registered($new_user = $this->create($request->all())));
        
        $authy_user = $authy_api->registerUser(
            $new_user->email,
            $new_user->phone_number,
            $new_user->country_code
        );

        if ($authy_user->ok()) {
            $new_user->authy_id = $authy_user->id();
            $new_user->save();
            $request->session()->flash(
                'status',
                "User created successfully"
            );

            DB::commit();

            $this->guard()->login($new_user);

            return $this->registered($request, $new_user)
                ?: redirect($this->redirectPath());
        } else {
            $errors = $this->getAuthyErrors($authy_user->errors());
            DB::rollback();

            return view('auth.register', ['errors' => new MessageBag($errors)]);
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

    public function showProviderUserRegistrationForm(Request $request, string $provider)
    {
        $token = $request->token;

        switch ($provider) {
            case "google":
                $providerUser = Socialite::driver($provider)->userFromToken($token);
                break;

            case "twitter":
                $providerUser = Socialite::driver($provider)->userFromTokenAndSecret($token, env('TWITTER_ACCESS_TOKEN_SECRET'));
                break;
        }

        return view('auth.social_register', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $token,
        ]);
    }

    public function registerProviderUser(Request $request, string $provider, AuthyApi $authy_api)
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'country_code' => 'required',
            'phone_number' => 'required|numeric',
            'token' => ['required', 'string']
        ]);

        $token = $request->token;

        switch ($provider) {
            case "google":
                $providerUser = Socialite::driver($provider)->userFromToken($token);
                break;

            case "twitter":
                $providerUser = Socialite::driver($provider)->userFromTokenAndSecret($token, env('TWITTER_ACCESS_TOKEN_SECRET'));
                break;
        }

        DB::beginTransaction();

        $new_user = User::create([
            'name' => $request->name,
            'email' => $providerUser->getEmail(),
            'password' => null,
            'country_code' => $request->country_code,
            'phone_number' => $request->phone_number
        ]);

        $authy_user = $authy_api->registerUser(
            $new_user->email,
            $new_user->phone_number,
            $new_user->country_code
        );

        if ($authy_user->ok()) {
            $new_user->authy_id = $authy_user->id();
            $new_user->save();

            DB::commit();

            $this->guard()->login($new_user);

            return $this->registered($request, $new_user)
                ?: redirect($this->redirectPath());
        } else {
            $errors = $this->getAuthyErrors($authy_user->errors());
            DB::rollback();

            return view('auth.register', ['errors' => new MessageBag($errors)]);
        }
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
