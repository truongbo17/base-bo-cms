<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => config('bo.base.route_prefix', 'prefix_plugin'),
    'middleware' => array_merge(
        (array) config('bo.base.web_middleware', 'web'),
        (array) config('bo.base.middleware_key', 'admin')
    ),
    'namespace'  => 'namespace_plugin_controller',
], function () { // custom admin routes
}); // this should be the absolute last line of this file
