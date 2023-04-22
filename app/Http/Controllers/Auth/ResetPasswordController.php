<?php

namespace Bo\Base\Http\Controllers\Auth;

use Bo\Base\Library\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    protected array $data = []; // the information we send to the view

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Get the path the user should be redirected to after password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function redirectTo()
    {
        return bo_url();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $guard = bo_guard_name();

        $this->middleware("guest:$guard");

        if (! bo_users_have_email()) {
            abort(501, trans('bo::base.no_email_column'));
        }

        // where to redirect after password was reset
        $this->redirectTo = property_exists($this, 'redirectTo') ? $this->redirectTo : bo_url('dashboard');
    }

    // -------------------------------------------------------
    // Laravel overwrites for loading bocms views
    // -------------------------------------------------------

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Http\Response
     */
    public function showResetForm(Request $request, $token = null)
    {
        $this->data['title'] = trans('bo::base.reset_password'); // set the page title

        return view(bo_view('auth.passwords.reset'), $this->data)->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        $passwords = config('bo.base.passwords', config('auth.defaults.passwords'));

        return Password::broker($passwords);
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return bo_auth();
    }
}
