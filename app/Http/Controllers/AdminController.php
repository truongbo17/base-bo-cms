<?php

namespace Bo\Base\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class AdminController extends Controller
{
    protected array $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(bo_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return Application|Factory|View
     */
    public function dashboard()
    {
        $this->data['title'] = trans('bo::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('bo::crud.admin')     => bo_url('dashboard'),
            trans('bo::base.dashboard') => false,
        ];

        return view(bo_view('dashboard'), $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return Redirector|RedirectResponse
     */
    public function redirect()
    {
        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(bo_url('dashboard'));
    }
}
