<div class="field-row">
    <div class="field-container">
        @php $name = $setting['name'] @endphp

        @if(isset($account) && $account instanceof \Modules\Application\Entities\Account)
            @php $fieldName = 'account_data_json__'.$name @endphp
            <input type="text" id="{{ $name }}" name="fields[{{ $name }}]"
                   value="{{ isset($account) ? $account->$fieldName : old($name) }}"
                   placeholder="{{ $setting['label'] }}" required
                   class="{{ $errors->has($setting) ? ' invalid' : '' }} main_input"/>
        @else
            <input type="text" id="{{ $name }}" name="fields[{{ $name }}]"
                   value="{{ isset($account) ? $account->$name : old($name) }}"
                   placeholder="{{ $setting['label'] }}" required
                   class="{{ $errors->has($setting) ? ' invalid' : '' }} main_input"/>
        @endif
        @if(isset($setting['description']))
            <label class="label_description" for="{{ $name }}">{!! $setting['description'] !!}</label>
        @endif
    </div>
    @if ($errors->has($setting))
        <span class="field-error-text">{{ $errors->first($setting) }}</span>
    @endif
</div>
