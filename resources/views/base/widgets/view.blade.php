<!-- view field -->
@includeWhen(!empty($widget['wrapper']), 'bo::widgets.inc.wrapper_start')

	@include($widget['view'], ['widget' => $widget])

@includeWhen(!empty($widget['wrapper']), 'bo::widgets.inc.wrapper_end')
