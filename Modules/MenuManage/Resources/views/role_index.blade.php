@extends('backEnd.master')
@section('title')
    @lang('menumanage::menuManage.manage_position')
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/MenuManage/Resources/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('Modules/MenuManage/Resources/assets/css/icon-picker.css') }}" />
@endpush
@section('mainContent')
    <div class="role_permission_wrap">
        <div class="permission_title d-flex flex-wrap justify-content-between mb_10">
            <h4>{{ trans('menumanage::menuManage.menu_manage') }}</h4>
            <div class="">

                <a href="{{ route('menumanage.reset-with-section', ['role_id' => @$role->id]) }}"
                    class="primary-btn radius_30px  fix-gr-bg">{{ __('menumanage::menuManage.Reset to  with Section') }}</a>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb_20">
            <div class="white-box available_box  student-details ">
                <div class="add-visitor">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header pt-0 pb-0" id="headingOne">
                                <h5 class="mb-0 create-title" data-toggle="collapse" data-target="#collapseOne"
                                    aria-expanded="false" aria-controls="collapseOne">
                                    <button class="btn btn-link add_btn_link">
                                        @isset($editPermissionSection)
                                        {{ __('menumanage::menuManage.Edit Section') }}
                                        @else 
                                        {{ __('menumanage::menuManage.Add Section') }}
                                        @endisset
                                    </button>
                                </h5>
                            </div>

                            <div id="collapseOne" class="collapse {{ isset($editPermissionSection) ? 'show' : '' }}"
                                aria-labelledby="headingOne" data-parent="#accordion">
                                <div class="card-body">
                                    @isset($editPermissionSection)
                                        {{ html()->form('POST', route('sidebar-manager.section-update'))->open() }}
                                        <input type="hidden" name="id" value="{{ $editPermissionSection->id }}">
                                    @else
                                        {{ html()->form('POST', route('sidebar-manager.section.store', ['role_id' => @$role->id]))->open() }}

                                    @endif
                                        <div class="row pt-0">

                                        </div>
                                        <div id="row_element_div">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="primary_input mb-25">
                                                        <input type='hidden' name='role_name' value="{{isset($role_name) ? $role_name:''}}">
                                                        <label class="primary_input_label"
                                                            for="name">{{ __('common.name') }} <span
                                                                class="textdanger">*</span>
                                                        </label>
                                                        <input class="primary_input_field" type="text" name="name"
                                                            autocomplete="off"
                                                            value="{{ isset($editPermissionSection) ? $editPermissionSection->name : null }}"
                                                            placeholder="{{ __('common.name') }}">
                                                        @if ($errors->has('name'))
                                                            <span class="text-danger">{{ @$errors->first('name') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 text-center">
                                                <button type="submit" class="primary-btn fix-gr-bg">
                                                    <span class="ti-check"></span>
                                                    {{ __('common.save') }} </button>
                                            </div>
                                        </div>
                                        {!! html()->form()->close() !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="mt_20" id="available_menu_div">
                            @include('menumanage::components.available_list')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb_20">
                <div class="white-box">
                    <input type="hidden" name="data" id="items-data" value="">
                    <div class="add-visitor" id="menu_idv">

                        @include('menumanage::components.components')
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="white-box">
                    <div class="add-visitor" id="live_preview_div">
                        @include('menumanage::components.live_preview')
                    </div>
                </div>
            </div>
        </div>





            <input type="hidden" id="order_change_url" value="{{ route('sidebar-manager.menu-update') }}">
            <input type="hidden" id="section_store_url" value="{{ route('sidebar-manager.section.store') }}">
            <input type="hidden" id="section_delete_url" value="{{ route('sidebar-manager.remvoeSection') }}">
            <input type="hidden" id="menu_delete_url" value="{{ route('sidebar-manager.menu-store') }}">
            <input type="hidden" id="menu_remove_url" value="{{ route('sidebar-manager.removeMenu') }}">
            <input type="hidden" id="section_sort_url" value="{{ route('sidebar-manager.sort-section') }}">
            <input type="hidden" id="role_name" value="{{ $role_name }}">

    @endsection
