@extends(bo_view('blank'))

@php
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('bo::base.welcome'),
        'content'     => trans('bo::base.use_sidebar'),
        'button_link' => bo_url('logout'),
        'button_text' => trans('bo::base.logout'),
    ];
@endphp

@section('content')
@endsection
