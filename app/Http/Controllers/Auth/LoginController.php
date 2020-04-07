<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Authy\AuthyApi;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request, AuthyApi $authy_api)
    {
        $this->validateLogin($request);
        
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($user = $this->isExistUser($request->input('email'), $request->input('password'))) {
            $sms = $authy_api->requestSms($user->authy_id);

            if($sms->ok()) {
                return redirect()->route('verify.show', ['authy_id' => $user->authy_id]);
            }
            else {
                $errors = $this->getAuthyErrors($sms->errors());
                return redirect()->back()->withErrors(new MessageBag($errors))->withInput();
            }
        }
        
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function showVerificationForm(int $authy_id)
    {
        return view('auth.sms')->with('authy_id', $authy_id);
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
            return redirect()->back()->withErrors(new MessageBag($errors))->withInput();
        }
    }

    /**
     * @param string $email
     * @param string $password
     */
    private function isExistUser(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if(empty($user)) {
            return false;
        }

        if(Hash::check($password, $user->password))
        {
            return $user;
        } else {
            return false;
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

    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider, AuthyApi $authy_api)
    {
        $providerUser = Socialite::driver($provider)->stateless()->user();

        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            $sms = $authy_api->requestSms($user->authy_id);
            
            if($sms->ok()) {
                return redirect()->route('verify.show', ['authy_id' => $user->authy_id]);
            }
            else {
                $errors = $this->getAuthyErrors($sms->errors());
                return redirect()->route('login')->withErrors(new MessageBag($errors))->withInput();
            }
        }

        return redirect()->route('register.{provider}', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $providerUser->token,
        ]);
    }
}
