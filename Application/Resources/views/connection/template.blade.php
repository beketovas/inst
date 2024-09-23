@extends(config('app.theme').'.front.integration')

@section('content')

        <div class="fix">
            <section class="application-connection-block">
                    @yield('connection-status')
                    @yield('form-open')
                    {{ csrf_field() }}

                    @if(session()->has('form-status'))
                        @include(config('app.theme').'/front/partials/message', ['type' => 'success', 'message' => session('form-status')])
                    @endif
                    @if(session()->has('form-error'))
                        @include(config('app.theme').'/front/partials/message', ['type' => 'danger', 'message' => session('form-error')])
                    @endif
                    @if(isset($settings))
                        @foreach($settings as $setting)
                            @include('application::connection.fieldTypes.'.$setting['type'])
                        @endforeach
                    @endif
                    <div class="field-row">
                        <div class="instructions">
                            <a href="https://apiway.crunch.help/">@lang('applications.service_connection_instruction')</a>
                        </div>
                    </div>

                    @yield('form-close')

                </div>
            </section>
        </div>
        <!-- /.fix -->

@endsection
