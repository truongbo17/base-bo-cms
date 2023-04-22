<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('bo.base.html_direction') }}">
<head>
    @include(bo_view('inc.head'))
</head>
<body class="app flex-row align-items-center">

  @yield('header')

  <div class="container">
  @yield('content')
  </div>

  <footer class="app-footer sticky-footer">
    @include('bo::inc.footer')
  </footer>

  @yield('before_scripts')
  @stack('before_scripts')

  @include(bo_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')

</body>
</html>
