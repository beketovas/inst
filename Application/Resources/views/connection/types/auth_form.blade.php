@extends('application::connection.template')

@section('connection-status')
    <h2>
        @if(isset($account))
            @lang("application::site.already_connected", ['app_name' => $application->name])
        @else
            @lang("application::site.let_it_be_connected", ['app_name' => $application->name])
        @endif
    </h2>
@endsection

@section('form-open')
    @if(!isset($account))
        <form class="flow-block" method="post" action="{{ route("application.selectAction", ['slug' => $slug]) }}" >
    @else
        <form class="flow-block" method="post" action="{{ route("application.selectAction", ['slug' => $slug, 'id' => $account->id]) }}" >
    @endif
@endsection

@section('form-close')
    <div class="button-wrap">
        <div class="button-wrap-line">
            @if(!isset($account))
                <button name="action_type" value="connect" class="but_auth_{{ $slug }} main_button small blue_theme">@lang('applications.attach')</button>
            @else
                <button name="action_type" value="reconnect" class="main_button small blue_theme">@lang('applications.reconnect')</button>
                <a href="{{ route('application.test', ['slug' => $slug, 'id' => $account->id]) }}"
                   class="main_button small blue_theme">@lang('applications.test_application_connection')</a>
                <button name="action_type" value="disconnect" class="main_button small blue_theme">@lang('applications.disconnect')</button>
            @endif
        </div>
    </div>
    </form>
@endsection
