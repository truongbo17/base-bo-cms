<!-- =================================================== -->
<!-- ========== Top menu items (ordered left) ========== -->
<!-- =================================================== -->
<ul class="nav navbar-nav d-md-down-none">

    @if (bo_auth()->check())
        <!-- Topbar. Contains the left part -->
        @include(bo_view('inc.topbar_left_content'))
    @endif

</ul>
<!-- ========== End of top menu left items ========== -->



<!-- ========================================================= -->
<!-- ========= Top menu right items (ordered right) ========== -->
<!-- ========================================================= -->
<ul class="nav navbar-nav ml-auto @if(config('bo.base.html_direction') == 'rtl') mr-0 @endif">
    @if (bo_auth()->guest())
        <li class="nav-item"><a class="nav-link" href="{{ route('bo.auth.login') }}">{{ trans('bo::base.login') }}</a>
        </li>
        @if (config('bo.base.registration_open'))
            <li class="nav-item"><a class="nav-link" href="{{ route('bo.auth.register') }}">{{ trans('bo::base.register') }}</a></li>
        @endif
    @else
        <!-- Topbar. Contains the right part -->
        @include(bo_view('inc.topbar_right_content'))
        @include(bo_view('inc.menu_user_dropdown'))
    @endif
</ul>
<!-- ========== End of top menu right items ========== -->
