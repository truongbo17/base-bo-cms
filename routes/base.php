<?php

use Illuminate\Support\Facades\Route;

Route::group(
[
    'namespace'  => 'Bo\Base\Http\Controllers',
    'middleware' => config('bo.base.web_middleware', 'web'),
    'prefix'     => config('bo.base.route_prefix'),
],
function () {
    // if not otherwise configured, setup the auth routes
    if (config('bo.base.setup_auth_routes')) {
        // Authentication Routes...
        Route::get('login', 'Auth\LoginController@showLoginForm')->name('bo.auth.login');
        Route::post('login', 'Auth\LoginController@login')->name('bo.auth.login.post');
        Route::get('logout', 'Auth\LoginController@logout')->name('bo.auth.logout');
        Route::post('logout', 'Auth\LoginController@logout')->name('bo.auth.logout.post');

        // Registration Routes...
        Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('bo.auth.register');
        Route::post('register', 'Auth\RegisterController@register')->name('bo.auth.register.post');

        // if not otherwise configured, setup the password recovery routes
        if (config('bo.base.setup_password_recovery_routes', true)) {
            Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('bo.auth.password.reset');
            Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('bo.auth.password.reset.post');
            Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('bo.auth.password.reset.token');
            Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('bo.auth.password.email')->middleware('bo.throttle.password.recovery:'.config('bo.base.password_recovery_throttle_access'));
        }
    }

    // if not otherwise configured, setup the dashboard routes
    if (config('bo.base.setup_dashboard_routes')) {
        Route::get('dashboard', 'AdminController@dashboard')->name('bo.dashboard');
        Route::get('/', 'AdminController@redirect')->name('bo');
    }

    // if not otherwise configured, setup the "my account" routes
    if (config('bo.base.setup_my_account_routes')) {
        Route::get('edit-account-info', 'MyAccountController@getAccountInfoForm')->name('bo.account.info');
        Route::post('edit-account-info', 'MyAccountController@postAccountInfoForm')->name('bo.account.info.store');
        Route::post('change-password', 'MyAccountController@postChangePasswordForm')->name('bo.account.password');
    }
});
