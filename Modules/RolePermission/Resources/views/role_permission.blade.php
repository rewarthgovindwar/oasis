@extends('backEnd.master')
@section('title') @lang('rolepermission::role.role_permission') @endsection
@section('mainContent')

    <link rel="stylesheet" href="{{ asset('/Modules/RolePermission/public/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/Modules/RolePermission/public/css/custom.css') }}">

    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('system_settings.role_permission') </h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('system_settings.system_settings')</a>
                    <a href="#">@lang('system_settings.role_permission')</a>
                </div>
            </div>
        </div>
    </section>

    <div class="role_permission_wrap">
        <div class="permission_title">
            <h4>Assign Permission ({{ @$role->name }})</h4>
        </div>
    </div>

    <div class="erp_role_permission_area ">


@php
    $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InAppLiveClass','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InfixBiometrics'];
@endphp
        <!-- single_permission  -->

        {{ Form::open([
            'class' => 'form-horizontal',
            'files' => true,
            'route' => 'rolepermission/role-permission-assign',
            'method' => 'POST',
        ]) }}

        <input type="hidden" name="role_id" value="{{ @$role->id }}">

        <div class="mesonary_role_header">


            @foreach ($all_permissions as $key => $permission)
                @if(!empty($permission->module) && in_array($permission->module, $paid_modules))
                    @if(moduleStatusCheck($permission->module))
                        @includeIf('rolepermission::inc.permission_list')
                    @endif
                @else    
                    @includeIf('rolepermission::inc.permission_list')
                @endif                
            @endforeach


        </div>


        <div class="row mt-40">
            <div class="col-lg-12 text-center">
                <button class="primary-btn fix-gr-bg">
                    <span class="ti-check"></span>
                    @lang('submit')
                </button>
            </div>
        </div>

        {{ Form::close() }}


    </div>
@endsection



@section('script')
    <script type="text/javascript">
        // Fees Assign
        $('.permission-checkAll').on('click', function() {

            //$('.module_id_'+$(this).val()).prop('checked', this.checked);


            if ($(this).is(":checked")) {
                $('.module_id_' + $(this).val()).each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.module_id_' + $(this).val()).each(function() {
                    $(this).prop('checked', false);
                });
            }
        });



        $('.module_link').on('click', function() {

            var module_id = $(this).parents('.single_permission').attr("id");
            var module_link_id = $(this).val();


            if ($(this).is(":checked")) {
                $(".module_option_" + module_id + '_' + module_link_id).prop('checked', true);
            } else {
                $(".module_option_" + module_id + '_' + module_link_id).prop('checked', false);
            }

            var checked = 0;
            $('.module_id_' + module_id).each(function() {
                if ($(this).is(":checked")) {
                    checked++;
                }
            });

            if (checked > 0) {
                $(".main_module_id_" + module_id).prop('checked', true);
            } else {
                $(".main_module_id_" + module_id).prop('checked', false);
            }
        });




        $('.module_link_option').on('click', function() {

            var module_id = $(this).parents('.single_permission').attr("id");
            var module_link = $(this).parents('.module_link_option_div').attr("id");




            // module link check

            var link_checked = 0;

            $('.module_option_' + module_id + '_' + module_link).each(function() {
                if ($(this).is(":checked")) {
                    link_checked++;
                }
            });

            if (link_checked > 0) {
                $("#Sub_Module_" + module_link).prop('checked', true);
            } else {
                $("#Sub_Module_" + module_link).prop('checked', false);
            }

            // module check
            var checked = 0;

            $('.module_id_' + module_id).each(function() {
                if ($(this).is(":checked")) {
                    checked++;
                }
            });


            if (checked > 0) {
                $(".main_module_id_" + module_id).prop('checked', true);
            } else {
                $(".main_module_id_" + module_id).prop('checked', false);
            }
        });
    </script>
    <!-- <script>
        // $(".arrow").on("click", function(){
        //     $(this).find($("i")).toggleClass('ti-plus').toggleClass('ti-minus');
        // });
    </script> -->
@endsection
