@if (config('bo.base.show_powered_by') || config('bo.base.developer_link'))
    <div class="text-muted ml-auto mr-auto">
      @if (config('bo.base.developer_link') && config('bo.base.developer_name'))
      {{ trans('bo::base.handcrafted_by') }} <a target="_blank" rel="noopener" href="{{ config('bo.base.developer_link') }}">{{ config('bo.base.developer_name') }}</a>
      @endif
        &  <a target="_blank" rel="noopener" href="https://github.com/truongbo17"> Build CMS By TruongBo.</a>
    </div>
@endif
