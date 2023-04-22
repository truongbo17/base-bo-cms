@if (!empty($widgets))
    @foreach ($widgets as $currentWidget)

        @if (is_array($currentWidget))
            @php
                $currentWidget = \Bo\Base\Library\Widget::add($currentWidget);
            @endphp
        @endif

        @include($currentWidget->getFinalViewPath(), ['widget' => $currentWidget->toArray()])

    @endforeach
@endif
