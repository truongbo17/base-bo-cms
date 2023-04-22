<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" dir="{{ config('bo.base.html_direction') }}">

<head>
  @include(bo_view('inc.head'))

</head>

<body class="{{ config('bo.base.body_class') }}">

  @include(bo_view('inc.main_header'))

  <div class="app-body">

    @include(bo_view('inc.sidebar'))

    <main class="main pt-2">

       @yield('before_breadcrumbs_widgets')

       @includeWhen(isset($breadcrumbs), bo_view('inc.breadcrumbs'))

       @yield('after_breadcrumbs_widgets')

       @yield('header')

        <div class="container-fluid animated fadeIn">

          @yield('before_content_widgets')

          @yield('content')

          @yield('after_content_widgets')

        </div>

    </main>

  </div><!-- ./app-body -->

  <footer class="{{ config('bo.base.footer_class') }}">
    @include(bo_view('inc.footer'))
  </footer>

  @yield('before_scripts')
  @stack('before_scripts')

  @include(bo_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')
</body>
</html>
