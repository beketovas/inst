<div class="field-row">
    <div class="field-container">
        @php $name = $setting['name'] @endphp

        <select id="{{ $name }}" name="fields[{{ $name }}]"
               placeholder="{{ $setting['label'] }}"
               class="{{ $errors->has($setting) ? ' invalid' : '' }}">
            @foreach($setting['dropdown_options'] as $value)
                <option class="select_list__item"  {{ isset($account) && $account->$name == $value ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
        </select>
        @if(isset($setting['description']))
            <label class="label_description" for="{{ $name }}">{!! $setting['description'] !!}</label>
        @endif
    </div>
    @if ($errors->has($setting))
        <span class="field-error-text">{{ $errors->first($setting) }}</span>
    @endif
</div>
