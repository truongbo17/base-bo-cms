@includeWhen(!empty($widget['wrapper']), 'bo::widgets.inc.wrapper_start')

<div
	@if (count($widget) > 2)
	    @foreach ($widget as $attribute => $value)
	        @if (is_string($attribute) && $attribute!='content' && $attribute!='type')
	            {{ $attribute }}="{{ $value }}"
	        @endif
	    @endforeach
	@endif
	>

	@if (isset($widget['content']))
		@include('bo::inc.widgets', [ 'widgets' => $widget['content'] ])
	@endif

</div>

@includeWhen(!empty($widget['wrapper']), 'bo::widgets.inc.wrapper_end')
