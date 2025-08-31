@extends('backEnd.master')
    @section('title') 
        {{@$title}}
    @endsection
    @push('css')
    <style>
        .input-right-icon {
            z-index: inherit !important;
        }
        .check_box_table table.dataTable.dtr-inline.collapsed > tbody > tr[role='row'] > td:first-child::before, 
        .check_box_table table.dataTable.dtr-inline.collapsed > tbody > tr[role='row'] > th:first-child::before {
            left: 10px;
            top: 55px;
            line-height: 18px;
        }
    </style>
@endpush
@section('mainContent')
<section class="sms-breadcrumb mb-20">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>{{@$title}}</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">{{@$title}}</a>
                @if(isset($editData))
                    <a href="#">@lang('admin.edit_shift')</a>
                @endif
            </div>
        </div>
    </div>
</section>
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        @if(isset($editData))
            @if(userPermission('shift.store'))
                <div class="row">
                    <div class="offset-lg-10 col-lg-2 text-right col-md-12 mb-20">
                        <a href="{{route('shift.store')}}" class="primary-btn small fix-gr-bg"><span class="ti-plus pr-2"></span>@lang('common.add')</a>
                    </div>
                </div>
            @endif
        @endif
        <div class="row">
            <div class="col-lg-3">
                <div class="row">
                    <div class="col-lg-12">
                        @if(isset($editData))
                            {{ Form::open(['class' => 'form-horizontal', 'route' => 'shift.update', 'method' => 'POST']) }}
                            <input type="hidden" name="id" value="{{isset($editData)? $editData->id: ''}}">
                        @else
                            {{ Form::open(['class' => 'form-horizontal', 'route' => 'shift.store', 'method' => 'POST']) }}
                        @endif
                        <div class="white-box">
                            <div class="main-title">
                                <h3 class="mb-15">
                                    @if(isset($editData))
                                        @lang('admin.edit_shift')
                                    @else
                                        @lang('admin.add_shift')
                                    @endif
                                     
                                </h3>
                            </div>
                            <div class="add-visitor">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('common.name') <span class="text-danger"> *</span></label>
                                            <input class="primary_input_field form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" type="text" name="name" autocomplete="off" value="{{isset($editData)? $editData->name: old('name')}}">
                                            
                                            
                                            @if ($errors->has('name'))
                                            <span class="text-danger" >
                                                {{ $errors->first('name') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-15">
                                    <div class="col-md-12">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('common.start_time')
                                                <span class="text-danger"> *</span></label>
                                            <div class="primary_datepicker_input">
                                                <div class="no-gutters input-right-icon">
                                                    <div class="col">
                                                        <div class="">
                                                            <input placeholder="-"
                                                                class="primary_input_field primary_input_field time"
                                                                type="text" name="start_time" id="start_time"
                                                                value="{{ isset($editData) ? date('H:i', strtotime($editData->start_time)) : (old('start_time') != '' ? old('start_time') : date('H:i')) }}">

                                                            @if ($errors->has('start_time'))
                                                                <span class="text-danger d-block">
                                                                    {{ $errors->first('start_time') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <button class="" type="button">
                                                        <label class="m-0 p-0" for="start_time">
                                                            <i class="ti-timer" id="admission-date-icon"></i>
                                                        </label>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row  mt-25">
                                    <div class="col-md-12">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('common.end_time')
                                                <span class="text-danger"> *</span></label>
                                            <div class="primary_datepicker_input">
                                                <div class="no-gutters input-right-icon">
                                                    <div class="col">
                                                        <div class="primary_input">
                                                            <input
                                                                class="primary_input_field primary_input_field time  form-control{{ $errors->has('end_time') ? ' is-invalid' : '' }}"
                                                                type="text" name="end_time" id="end_time"
                                                                value="{{ isset($editData) ? date('H:i', strtotime($editData->end_time)) : (old('end_date') != '' ? old('end_date') : date('H:i')) }}">
                                                            @if ($errors->has('end_time'))
                                                                <span class="text-danger">
                                                                    {{ $errors->first('end_time') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <button class="" type="button">
                                                        <label class="m-0 p-0" for="end_time">
                                                            <i class="ti-timer"></i>
                                                        </label>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-15">
                                    <div class="col-lg-12">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('common.description')</label>
                                            <textarea class="primary_input_field form-control" cols="0" rows="4"
                                                name="description">{{isset($editData)? $editData->description: old('description')}}</textarea>
                                             
                                            
                                        </div>
                                    </div>
                                </div>
                                @php
                                        $tooltip = "";
                                        if(userPermission("fees.fees-group-store")){
                                              $tooltip = "";
                                          }elseif(isset($editData) && userPermission('fees.fees-group-edit')){
                                            $tooltip = "";
                                          }else{
                                              $tooltip = "You have no permission to add";
                                          }
                                    @endphp
                                <div class="row mt-40">
                                    <div class="col-lg-12 text-center">
                                        @if(userPermission('fees.fees-group-store') || userPermission('fees.fees-group-edit'))
                                            <button class="primary-btn fix-gr-bg submit" data-toggle="tooltip"
                                            title="{{$tooltip}}">
                                                <span class="ti-check"></span>
                                                @if(isset($editData))
                                                    @lang('common.update')
                                                @else
                                                    @lang('common.save')
                                                @endif
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="white-box">
                    <div class="row">
                        <div class="col-lg-4 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-15"> @lang('admin.shift_list')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <x-table>
                                <table id="table_id" class="table" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th> @lang('common.name')</th>
                                            <th> @lang('common.start_time')</th>
                                            <th> @lang('common.end_time')</th>
                                            {{-- <th> @lang('common.status')</th> --}}
                                            <th> @lang('common.description')</th>
                                            <th> @lang('common.action')</th>
                                        </tr>
                                    </thead>
    
                                    <tbody>
                                        @foreach($shifts as $shift)
                                        <tr>
                                            <td>{{$shift->name}}</td>
                                            <td>{{$shift->start_time}}</td>
                                            <td>{{$shift->end_time}}</td>
                                            {{-- <td>
                                                <label class="switch_toggle" for="active_checkbox{{@$shift->id }}">
                                                    <input type="checkbox" class="status_enable_disable"
                                                        id="active_checkbox{{ @$shift->id }}"
                                                        {{ @$shift->active_status == 1 ? 'checked' : '' }}
                                                        value="{{ @$shift->id }}">
                                                    <i class="slider round"></i>
                                                </label>
                                            </td> --}}
                                            <td>{{$shift->description}}</td>
                                            <td>
                                                <x-drop-down>
                                                        @if(userPermission('shift.edit'))
                                                            <a class="dropdown-item" href="{{route('shift.edit', [$shift->id])}}"> @lang('common.edit')</a>
                                                        @endif
    
                                                        @if(userPermission('shift.delete'))
                                                            <a class="dropdown-item deleteFeesGroupModal" data-toggle="modal" data-target="#deleteFeesGroupModal{{$shift->id}}" href="#">@lang('common.delete')</a>
                                                        @endif
                                                </x-drop-down>
                                            </td>
                                            
                                        </tr>
    
                                        <div class="modal fade admin-query" id="deleteFeesGroupModal{{$shift->id}}">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"> @lang('fees.delete_fees_group')</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                        
                                                    <div class="modal-body">
                                                        <div class="text-center">
                                                            <h4> @lang('common.are_you_sure_to_delete')</h4>
                                                        </div>
                                        
                                                        <div class="mt-40 d-flex justify-content-between">
                                                            <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                                                            {{ Form::open(['route' => 'shift.delete', 'method' => 'POST',]) }}
                                                                <input type="hidden" name="id" value="{{$shift->id}}">
                                                                <button class="primary-btn fix-gr-bg" type="submit"> @lang('common.delete')</button>
                                                            {{ Form::close() }}
                                                        </div>
                                                    </div>
                                        
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@include('backEnd.partials.data_table_js')
@include('backEnd.partials.date_picker_css_js')

@push('scripts')
    <script>
        $('.status_enable_disable').on('click', function () {
            let id = $(this).val();
            $.ajax({
                url: "{{route('shift.status_change')}}",
                type: "POST",
                data: {
                    _token: "{{csrf_token()}}",
                    id: id
                },
                success: function (data) {
                    if (data.success == true) {
                        toastr.success(data.message);
                    } else {
                        toastr.warning(data.message);
                    }
                }
            });
        });
    </script>
    
@endpush