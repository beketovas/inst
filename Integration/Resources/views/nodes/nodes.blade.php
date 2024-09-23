@extends(config('app.theme').'.front.integration')

@section('content')


        <div class="fix">

            @if($aDataTransfers['show'] == 'yes')
                <div class="ways_wrap">
                    <div class="ways_line" >
                        <div class="ways__block" >
                            @if($aDataTransfers['subscribe_status_id'] == '2')
                                Trial ends at {{ $aDataTransfers['trialEndsAt'] }}
                            @else
                                {{ $aDataTransfers['current'] }} / {{ $aDataTransfers['all'] }} Data Transfer
                            @endif
                            @if ($aDataTransfers['bShowButton'] === 1)
                                <a href="{{ $aDataTransfers['billingPortalUrl'] }}">Upgrade</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

    <section>
        @admin
        <div class="admin-mode">@lang('site.you_are_in_admin_mode_and_can_not_modify')</div>
        @endadmin

        <integration
            integration-id="{{ $integration->code }}"
            is-admin="{{ Auth::guard('manager')->user() }}"
            integration-prop="{{$integration}}"
            inline-template>
            <div class="applications-integration-wrap" v-cloak>
                <h2 class="indent">
                    <a class="change-name-btn active" @click.prevent="changeName" href="">@{{ integrationName }}</a>
                    <input type="text" v-on:blur="saveIntegrationName" class="change-name-input" v-model="integrationName" />
                </h2>
                <div class="message_wrap">
                    <switch-button :integration.sync="integration" :integration-error.sync="integrationError"></switch-button>
                    <div class="messages" v-if="integration && integration.active">
                        <div class="alert alert-warning">
                            @lang('integration::site.you_are_not_allowed_to_make_changes_while_integration_is_activated')
                        </div>
                    </div>
                </div>
                <div class="messages" v-if="integrationError">
                    <div class="alert alert-danger" >
                        @{{ integrationError }}
                    </div>
                </div>
                <div class="applications-integration-block">
                    <trigger-node v-if="triggerNode" :node="triggerNode" :errors="triggerNodeErrors" :clear-node="ClearNode" :integration="integration"></trigger-node>
                    <action-node v-if="actionNode && triggerNode.application" :node="actionNode" :errors="actionNodeErrors" :clear-node="ClearNode" :integration="integration"></action-node>
                </div>
                <preloader :loading="loading"></preloader>
            </div>
        </integration>
    </section>

        </div>
        <!-- /.fix -->


@endsection

@section('trial_line')
    @if(($aDataTransfers['show'] == 'yes' && $aDataTransfers['subscribe_status_id'] == '2') || ( $aDataTransfers['show'] == 'free' && $aDataTransfers['subscribe_status_id'] == '1' ))
        <div class="fix no_mt">
            <div class="trial_wrap">
                <div class="trial_line" >
                    <div class="trial_block" >
                        @if($aDataTransfers['subscribe_status_id'] == '2')
                            Your trial ends at {{ $aDataTransfers['trialEndsAt'] }}. During the trial plan you have unlimited data transfers. After the trial you will have only the 100 data transfers per month.
                            <a href="{{ $aDataTransfers['billingPortalUrl'] }}">Upgrade</a>
                        @else
                            {{ $aDataTransfers['text'] }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($aDataTransfers['forAll'] != '')
        <div class="fix">
            <div class="trial_wrap">
                <div class="trial_line" >
                    <div class="trial_block" >
                        {{ $aDataTransfers['forAll'] }}
                        @if($aDataTransfers['forAllHrefLink'] != '')
                            <a href="{{ $aDataTransfers['forAllHrefLink'] }}">{{ $aDataTransfers['forAllHrefText'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('js')

    <script src="{{ asset(config('app.theme').'/front/js/modules/modules.'.$suffix.'.js?v='.config('app.js_v')) }}"></script>
@endsection
