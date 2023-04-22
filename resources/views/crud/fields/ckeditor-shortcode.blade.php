<!-- CKeditor -->
@php
    $field['extra_plugins'] = isset($field['extra_plugins']) ? implode(',', $field['extra_plugins']) : "embed,widget";

    $defaultOptions = [
        "filebrowserBrowseUrl" => bo_url('elfinder/ckeditor'),
        "extraPlugins" => $field['extra_plugins'],
        "embed_provider" => "//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}",
    ];

    $field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
<div class="mb-2 d-flex">
    <label>{!! $field['label'] !!}</label>
    <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="add_shortcode" data-toggle="modal"
            data-target="#modal-add-shortcode"><i class="las la-plus-circle"></i> Shortcode
    </button>
</div>

@include('crud::fields.inc.translatable_icon')
<textarea
    name="{{ $field['name'] }}"
    data-init-function="bpFieldInitCKEditorElement"
    data-options="{{ trim(json_encode($field['options'])) }}"
        @include('crud::fields.inc.attributes', ['default_class' => 'form-control'])
    	>{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            $( document ).ready(function() {
                $(document.body).append(`<div class="modal fade fade" id="modal-add-shortcode" tabindex="0" role="dialog" aria-labelledby="modal-add-shortcode"
                                    aria-modal="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="category-inline-create-dialog-label">
                                                    {{trans('bo::crud.add')}} Shortcode
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="modal-body bg-light">
                                                <div class="card">
                                                    <div class="card-body row">
                                                        <div class="form-group col-sm-12">
                                                            <label>Shortcode</label>
                                                            <select id="shortcode_choose"
                                                                    style="width: 100%"
                                                                    data-init-function="bpFieldInitSelect2FromArrayElement"
                                                                    data-field-is-inline="{{var_export($inlineCreate ?? false)}}"
                                                                    data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
                                                                @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_from_array'])>
                                                                @foreach(get_all_short_codes() as $shortcode)
                                            <option value="{{$shortcode->key}}">{{$shortcode->name}}</option>
                                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal" id="save-add-shortcode">Save</button>
                                </div>
                                </div>
                                </div>
                                </div>
                                `);
            });
        </script>

        <script src="{{ asset('packages/ckeditor/ckeditor.js') }}"></script>
        <script src="{{ asset('packages/ckeditor/adapters/jquery.js') }}"></script>
        <script>

            function bpFieldInitCKEditorElement(element) {


                //when removing ckeditor field from page html the instance is not properly deleted.
                //this event is triggered in repeatable on deletion so this field can intercept it
                //and properly delete the instances so it don't throw errors of unexistent elements in page that has initialized ck instances.
                element.on('bo_field.deleted', function (e) {
                    $ck_instance_name = element.siblings("[id^='cke_editor']").attr('id');

                    //if the instance name starts with cke_ it was an auto-generated name from ckeditor
                    //that happens because in repeatable we stripe the field names used by ckeditor, so it renders a random name
                    //that starts with cke_
                    if ($ck_instance_name.startsWith('cke_')) {
                        $ck_instance_name = $ck_instance_name.substr(4);
                    }
                    //we fully destroy the instance when element is deleted from the page.
                    CKEDITOR.instances[$ck_instance_name].destroy(true);
                });
                // trigger a new CKEditor
                element.ckeditor(element.data('options'));

                $("#save-add-shortcode").click(function () {
                    insertContent(`[short-code]${$('#shortcode_choose').val()}[/short-code]`);
                });

                function insertContent(html) {
                    for (var i in CKEDITOR.instances) {
                        CKEDITOR.instances[i].insertHtml(html);
                    }
                    return true;
                }
            }
        </script>
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
