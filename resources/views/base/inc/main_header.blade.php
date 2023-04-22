<header class="{{ config('bo.base.header_class') }}">
  <!-- Logo -->
  <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto ml-3" type="button" data-toggle="sidebar-show" aria-label="{{ trans('bo::base.toggle_navigation')}}">
    <span class="navbar-toggler-icon"></span>
  </button>
  <a class="navbar-brand" href="{{ url(config('bo.base.home_link')) }}" title="{{ config('bo.base.project_name') }}">
    {!! config('bo.base.project_logo') !!}
  </a>
  <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show" aria-label="{{ trans('bo::base.toggle_navigation')}}">
    <span class="navbar-toggler-icon"></span>
  </button>

  @include(bo_view('inc.menu'))
</header>
