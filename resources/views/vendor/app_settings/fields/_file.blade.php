@component('app_settings::input_group', compact('field'))

    <br>
    <input type="file"
           name="{{ $field['name'] }}"
           @if( $placeholder = Arr::get($field, 'placeholder') )
           placeholder="{{ $placeholder }}"
           @endif
           class="{{ Arr::get( $field, 'class') }} {{ $errors->has($field['name']) ? config('app_settings.input_invalid_class', 'is-invalid') : '' }}"
           @if( $styleAttr = Arr::get($field, 'style')) style="{{ $styleAttr }}" @endif
           id="{{ Arr::get($field, 'name') }}"
    >

    @if( $filePath = \setting($field['name']))
        @php
            $disk = Arr::get($field, 'disk', 'public');
            $fileExists = \Storage::disk($disk)->exists($filePath);
            $fileUrl = '/storage/' . ltrim($filePath, '/');
            $previewUrl = $fileUrl . ($fileExists && file_exists(public_path('storage/' . $filePath)) ? '?v=' . filemtime(public_path('storage/' . $filePath)) : '');
        @endphp

        <label class="text-danger" style="float:right; font-size: 0.8rem">
            <input type="checkbox" value="1" name="remove_file_{{$field['name']}}">
            {{ Arr::get($field, 'remove_label', 'Remove') }}
        </label>

        @if($fileExists && in_array(pathinfo($filePath, PATHINFO_EXTENSION), ["gif", "jpg", "jpeg", "png", "tiff", "tif"]))
            <a href="{{ $fileUrl }}" target="_blank">
                <img src="{{ $previewUrl }}" alt="{{ $field['name'] }}" class="{{ Arr::get( $field, 'preview_class') }}" style="{{ Arr::get($field, 'preview_style') }}"/>
            </a>
        @elseif($fileExists)
            <a target="_blank" class="btn btn-light btn-sm" href="{{ $fileUrl }}">View {{ $field['label'] }}</a>
        @else
            <div class="text-warning">Previous file was removed or is missing. Upload a new one.</div>
        @endif
    @endif

@endcomponent
