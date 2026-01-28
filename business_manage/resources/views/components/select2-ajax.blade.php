@props([
'name',
'id' => null,
'label' => null,
'url',
'placeholder' => '-- Chọn --',
'value' => null,
'text' => null,
'required' => false
])

<div class="w-100">
    @if($label)
    <label for="{{ $id ?? $name }}" class="form-label small fw-bold">{{ $label }} {!! $required ? '<span class="text-danger">*</span>' : '' !!}</label>
    @endif

    <div class="select2-bootstrap-5-wrapper">
        <select
            name="{{ $name }}"
            id="{{ $id ?? $name }}"
            class="form-select select2-ajax-component"
            data-url="{{ $url }}"
            data-placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            style="width: 100%;">
            @if($value && $text)
            <option value="{{ $value }}" selected>{{ $text }}</option>
            @endif
        </select>
    </div>
</div>

@once
@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-ajax-component').each(function() {
            let $this = $(this);
            $this.select2({
                theme: 'bootstrap-5', // Đổi từ bootstrap4 sang bootstrap-5
                selectionCssClass: 'select2--small', // Ép về kích thước nhỏ cho đồng bộ form-control-sm
                dropdownCssClass: 'select2--small',
                placeholder: $this.data('placeholder'),
                allowClear: true,
                ajax: {
                    url: $this.data('url'),
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return "Không tìm thấy kết quả";
                    },
                    searching: function() {
                        return "Đang tìm kiếm...";
                    }
                }
            });
        });
    });
</script>
@endpush

{{-- CSS bổ sung để căn chỉnh độ cao Select2 khớp 100% với form-control-sm của BS5 --}}
@push('styles')
<style>
    /* Ép Select2 có độ cao và font-size bằng đúng với .form-control-sm của Bootstrap 5 */
    .select2-container--bootstrap-5 .select2-selection--single.select2--small {
        height: calc(1.5em + 0.5rem + 2px) !important;
        min-height: calc(1.5em + 0.5rem + 2px) !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5;
        border-radius: 0.2rem !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single.select2--small .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.75 !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single.select2--small .select2-selection__clear {
        width: 0.7em !important;
        height: 0.7em !important;
        margin-top: 0.35rem !important;
    }
</style>
@endpush
@endonce