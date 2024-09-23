@extends(config('app.theme').'.front.cabinet')

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

        @if($sShowIntegration == 'yes')
        <section class="connected_apps_wrap">
            <div class="title_with_search">
                <h2>@lang('integration::site.my_integrations')</h2>
                <searchbar-ways></searchbar-ways>
            </div>
            <div class="connected_apps">
                @foreach($integrations as $integration)

                    <div class="connected_apps__item">
                        <div class="connected_apps__item__left">
                            <div class="connected_apps__item_image">
                                @if(isset($integration->nodes[0]->application) && $integration->nodes[0]->application->beta)
                                <div class="beta" >
                                   Beta
                                </div>
                                @endif
                                @if(Session::get('lastIntegration') !== null && Session::get('lastIntegration')->id == $integration->id)
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                         viewBox="0 0 40 40" fill="none">
                                        <circle cx="20" cy="20" r="20" fill="#FF604F"/>
                                        <path
                                            d="M22.7821 24.8214C22.7821 24.3136 22.3613 23.8929 21.8535 23.8929H18.1392C17.6314 23.8929 17.2107 24.3136 17.2107 24.8214V28.0714C17.2107 28.5792 17.6314 29 18.1392 29H21.8535C22.3613 29 22.7821 28.5792 22.7821 28.0714V24.8214ZM23.2174 9.5C23.2319 8.99219 22.8256 8.57143 22.3178 8.57143H17.6749C17.1671 8.57143 16.7609 8.99219 16.7754 9.5L17.1816 20.6429C17.1961 21.1507 17.6314 21.5714 18.1392 21.5714H21.8535C22.3613 21.5714 22.7966 21.1507 22.8111 20.6429L23.2174 9.5Z"
                                            fill="white"/>
                                    </svg>
                                @endif
                            <!--
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                     viewBox="0 0 40 40"
                                     fill="none">
                                    <circle cx="20" cy="20" r="20" fill="#71F0B3"/>
                                    <path
                                        d="M30.3795 16.0089C30.3795 15.6741 30.2455 15.3393 30.0045 15.0982L28.183 13.2768C27.942 13.0357 27.6071 12.9018 27.2723 12.9018C26.9375 12.9018 26.6027 13.0357 26.3616 13.2768L17.5759 22.0759L13.6384 18.125C13.3973 17.8839 13.0625 17.75 12.7277 17.75C12.3929 17.75 12.058 17.8839 11.817 18.125L9.99554 19.9464C9.75446 20.1875 9.62054 20.5223 9.62054 20.8571C9.62054 21.192 9.75446 21.5268 9.99554 21.7679L14.8438 26.6161L16.6652 28.4375C16.9063 28.6786 17.2411 28.8125 17.5759 28.8125C17.9107 28.8125 18.2455 28.6786 18.4866 28.4375L20.308 26.6161L30.0045 16.9196C30.2455 16.6786 30.3795 16.3438 30.3795 16.0089Z"
                                        fill="white"/>
                                </svg>
                                -->
                                @if(isset($integration->nodes[0]->application->iconUrl))
                                    <img src="{{ $integration->nodes[0]->application->iconUrl }}"
                                         alt="{{ $integration->nodes[0]->application->name }}">
                                @else
                                    <img src="/apiway/front/images/favicon.svg" alt="">
                                @endif
                            </div>
                            <div class="connected_apps__item_image">
                                @if(isset($integration->nodes[1]->application->iconUrl))
                                    <img src="{{ $integration->nodes[1]->application->iconUrl }}"
                                         alt="{{ $integration->nodes[1]->application->name }}">
                                @else
                                    <img src="/apiway/front/images/favicon.svg" alt="">
                                @endif
                            </div>
                            <div class="connected_apps__item_text">
                                <a href="{{ route('integrations.nodes', [$integration->code]) }}">{{ $integration->getTitle() }}</a>
                            </div>
                            <!-- /.connected_apps__item_text -->
                        </div>
                        <div class="connected_apps__item_btns">

                            <div class="switch_button" style="margin-right: 16px">
                                <input type="checkbox" class="ways_switch_input"
                                       onchange="this.parentNode.querySelector('.ways_switcher_form').submit();this.disabled=true"
                                       @if($integration->active) checked @endif
                                       id="switch_input{{$integration->code}}" autocomplete="off">
                                <label for="switch_input{{$integration->code}}">
                                    <i></i>
                                </label>
                                @if($integration->active)
                                    <form class="deactivate-form ways_switcher_form"
                                          action="{{route('integrations.deactivate', [$integration->code])}}" method="post">
                                        {{csrf_field()}}
                                    </form>
                                @else
                                    <form class="activate-form ways_switcher_form"
                                          action="{{route('integrations.activate', [$integration->code])}}" method="post">
                                        {{csrf_field()}}
                                    </form>
                                @endif
                            </div>

                            <button class="main_button small @if($integration->active) gray_theme disabled @else default_theme open_modal @endif"
                                    @if($integration->active) disabled  @else data-modal="{{$integration->code}}"@endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 0 365.696 365.696"
                                     width="12px">
                                    <path
                                        d="m243.1875 182.859375 113.132812-113.132813c12.5-12.5 12.5-32.765624 0-45.246093l-15.082031-15.082031c-12.503906-12.503907-32.769531-12.503907-45.25 0l-113.128906 113.128906-113.132813-113.152344c-12.5-12.5-32.765624-12.5-45.246093 0l-15.105469 15.082031c-12.5 12.503907-12.5 32.769531 0 45.25l113.152344 113.152344-113.128906 113.128906c-12.503907 12.503907-12.503907 32.769531 0 45.25l15.082031 15.082031c12.5 12.5 32.765625 12.5 45.246093 0l113.132813-113.132812 113.128906 113.132812c12.503907 12.5 32.769531 12.5 45.25 0l15.082031-15.082031c12.5-12.503906 12.5-32.769531 0-45.25zm0 0"/>
                                </svg>
                            </button>
                            <div class="modal modal_theme_normal " data-modal="{{$integration->code}}">
                                <div class="modal__content ">
                                    <form class="d-inline-block agreement_form "
                                          action="{{route('integrations.destroy', [$integration->code])}}"
                                          method="post">
                                        {{csrf_field()}}
                                        <input name="_method" type="hidden" value="DELETE">
                                        <h4>Are you sure?</h4>
                                        <div class="agreement_form__buttons">
                                            <button class="main_button small orange_border_theme" type="submit">
                                                Yes
                                            </button>
                                            <a class="main_button small blue_border_theme modal_close">
                                                No
                                            </a>
                                        </div>
                                        <!-- /.agreement_form__buttons -->
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.connected_apps__item -->
                @endforeach
            </div>
            <div class="pagination-block">
                {{ $integrations->links() }}
            </div>
            <!-- /.connected_apps -->

        </section>
        <!-- /.connected_apps -->
        @endif
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
    <script>
    </script>

@endsection
