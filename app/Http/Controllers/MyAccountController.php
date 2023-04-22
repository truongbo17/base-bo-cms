<?php

namespace Bo\Base\Http\Controllers;

use Alert;
use Bo\Base\Http\Requests\AccountInfoRequest;
use Bo\Base\Http\Requests\ChangePasswordRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class MyAccountController extends Controller
{
    protected array $data = [];

    public function __construct()
    {
        $this->middleware(bo_middleware());
    }

    /**
     * Show the user a form to change their personal information & password.
     */
    public function getAccountInfoForm()
    {
        $this->data['title'] = trans('bo::base.my_account');
        $this->data['user'] = $this->guard()->user();

        return view(bo_view('my_account'), $this->data);
    }

    /**
     * Save the modified personal information for a user.
     */
    public function postAccountInfoForm(AccountInfoRequest $request)
    {
        $result = $this->guard()->user()->update($request->except(['_token']));

        if ($result) {
            Alert::success(trans('bo::base.account_updated'))->flash();
        } else {
            Alert::error(trans('bo::base.error_saving'))->flash();
        }

        return redirect()->back();
    }

    /**
     * Save the new password for a user.
     */
    public function postChangePasswordForm(ChangePasswordRequest $request)
    {
        $user = $this->guard()->user();
        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            Alert::success(trans('bo::base.account_updated'))->flash();
        } else {
            Alert::error(trans('bo::base.error_saving'))->flash();
        }

        return redirect()->back();
    }

    /**
     * Get the guard to be used for account manipulation.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return bo_auth();
    }
}
