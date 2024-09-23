@extends('application::connection.template')

@section('connection-status')<h2>@lang("application::site.already_connected", ['app_name' => $application->name])</h2>@endsection
@section('form-open')
    <form class="flow-block">
@endsection

@section('form-close')
    </form>
@endsection
