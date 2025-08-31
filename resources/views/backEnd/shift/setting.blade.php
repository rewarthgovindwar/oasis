@extends('backEnd.master')
@section('title')
    @lang('admin.shift_setting')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('admin.shift_setting')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('admin.shift')</a>
                    <a href="{{ route('shift.setting') }}">@lang('admin.shift_setting')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            @if (userPermission('shift.setting'))
                {{ html()->form('POST', route('shift.setting'))->attributes([
                        'class' => 'form-horizontal',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                    ])->open() }}
            @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="main-title">
                                    <h3 class="mb-15">
                                        @lang('admin.shift_setting')
                                    </h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-30 mt-20">
                            <div class="col-lg-12 d-flex flex-column flex-sm-row relation-button gap-20">
                                <p class="text-uppercase mb-0">@lang('common.shift')</p>
                                <div class="d-flex radio-btn-flex ml-30 gap-20">
                                    <div>
                                        <input type="radio" name="shift_enable" id="shift_enable" value="1"
                                            class="common-radio relationButton"
                                            {{ generalSetting()->shift_enable ? 'checked' : '' }}>
                                        <label for="shift_enable">@lang('system_settings.enable')</label>
                                    </div>
                                    <div class="ml-2">
                                        <input type="radio" name="shift_enable" id="shift_disable" value="0"
                                            class="common-radio relationButton"
                                            {{ !generalSetting()->shift_enable ? 'checked' : '' }}>
                                        <label for="shift_disable">@lang('common.disable')</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row mt-40">
                            <div class="col-lg-12 text-center">

                                @if (env('APP_SYNC') == true)
                                    <span class="d-inline-block" tabindex="0" data-toggle="tooltip"
                                        title="Disabled For Demo "> <button class="primary-btn small fix-gr-bg  demo_view"
                                            style="pointer-events: none;" type="button"> @lang('common.update')</button></span>
                                @else
                                    @if (userPermission('shift.setting'))
                                        <button type="submit" class="primary-btn fix-gr-bg submit">
                                            <span class="ti-check"></span>
                                            @lang('common.update')
                                        </button>
                                    @endif
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ html()->form()->close() }}
        </div>

        </div>
    </section>

@endsection
