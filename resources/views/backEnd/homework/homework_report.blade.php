@extends('backEnd.master')
@section('title')
    @lang('homework.homework_report')
@endsection

@push('css')
    <style>
        .check_box_table .dropdown-item{
            padding: 0 10px!important;
            text-align: left!important;
        }

        html[dir="rtl"] .check_box_table .dropdown-item{
            text-align: right!important;
        }

        .single-meta .name a.download_link{
            color: #212529;
    }
    </style>
@endpush
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('homework.homework_report')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('homework.home_work')</a>
                    <a href="#">@lang('homework.homework_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
        <div class="container-fluid p-0">
            @if ($errors->any())
                @foreach ($errors as $error)
                    <div class="alert alert-danger">
                        {{ $error }}
                    </div>
                @endforeach
            @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-12 col-md-6">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('common.select_criteria') </h3>
                                </div>
                            </div>
                        </div>
                        {{ html()->form('GET', route('homework-report-search'))->attributes([
                                'class' => 'form-horizontal',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                            ])->open() }}
                        <div class="row">
                            @if (moduleStatusCheck('University'))
                                <div class="row">
                                    @includeIf(
                                        'university::common.session_faculty_depart_academic_semester_level',
                                        ['subject' => true]
                                    )
                                    <div class="col-lg-3 mt-15">
                                        <div class="primary_input">
                                            <label class="primary_input_label"
                                                for="date">{{ __('homework.homework_date') }}</label>
                                            <div class="primary_datepicker_input">
                                                <div class="no-gutters input-right-icon">
                                                    <div class="col">
                                                        <div class="">
                                                            <input
                                                                class="primary_input_field primary_input_field date form-control"
                                                                id="date" type="text" name="date"
                                                                value="{{ old('date') != '' ? old('date') : date('m/d/Y') }}"
                                                                autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <button class="btn-date" style="top: 55% !important;" data-id="#date"
                                                        type="button">
                                                        <label class="m-0 p-0" for="date">
                                                            <i class="ti-calendar" id="start-date-icon"></i>
                                                        </label>
                                                    </button>
                                                </div>
                                            </div>
                                            <span class="text-danger">{{ $errors->first('date') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @include('backEnd.common.search_criteria', [
                                    'div' => shiftEnable() ? 'col-lg-3' : 'col-lg-3',
                                    'required' => ['class', 'section', 'subject'],
                                    'visiable' => ['subject', 'shift', 'class', 'section'],
                                    'subject' => true,
                                    'class_name' => 'class_id',
                                    'section_name' => 'section_id',
                                    'subject_name' => 'subject_id',
                                    'selected' => [
                                        'shift_id' => @$shift_id,
                                        'section_id' => @$section_id,
                                        'class_id' => @$class_id,
                                        'subject_id' => @$subject_id,
                                    ],
                                ])
                                <div class="col-lg-3">
                                    <div class="primary_input">
                                        <label class="primary_input_label"
                                            for="date">{{ __('homework.homework_date') }}</label>
                                        <div class="primary_datepicker_input">
                                            <div class="no-gutters input-right-icon">
                                                <div class="col">
                                                    <div class="">
                                                        <input
                                                            class="primary_input_field primary_input_field date form-control"
                                                            id="date" type="text" name="date"
                                                            value="{{ old('date') != '' ? old('date') : date('m/d/Y') }}"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <button class="btn-date" style="top: 55% !important;" data-id="#date"
                                                    type="button">
                                                    <label class="m-0 p-0" for="date">
                                                        <i class="ti-calendar" id="start-date-icon"></i>
                                                    </label>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="text-danger">{{ $errors->first('date') }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-12 mt-20 text-right">
                                <button type="submit" class="primary-btn small fix-gr-bg">
                                    <span class="ti-search pr-2"></span>
                                    @lang('common.search')
                                </button>
                            </div>
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>

                @isset($data)
                    <div class="col-lg-12 mt-40">
                        <div class="white-box">
                            <div class="row">
                                <div class="col-lg-4 no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-15">@lang('homework.homework_report')</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <x-table>
                                        <table id="table_id" class="table data-table" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>@lang('homework.student_name')</th>
                                                    @if (moduleStatusCheck('University'))
                                                        <th>@lang('university::un.semester_label') (@lang('homework.section'))</th>
                                                    @else
                                                        <th> @if(shiftEnable()) @lang('admin.class_Sec_shift') @else @lang('admin.class_Sec') @endif</th>
                                                    @endif
                                                    <th>@lang('homework.subject')</th>
                                                    <th>@lang('homework.marks')</th>
                                                    <th>@lang('homework.submission_date')</th>
                                                    <th>@lang('homework.evaluation_date')</th>
                                                    <th>@lang('homework.evaluated_by')</th>
                                                    <th>@lang('common.status')</th>
                                                    <th>@lang('homework.action')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data as $key => $report)
                                                    <tr>
                                                        <td>{{ $report['student'] }}</td>
                                                        <td>{{ $report['class'] }} ({{ $report['section'] }}) @if(shiftEnable()) [{{ $report['shift'] }}] @endif</td>
                                                        <td>{{ $report['subject'] }}</td>
                                                        <td>{{ $report['obtain_marks'] ? $report['obtain_marks'] : '-' }} /
                                                            {{ $report['total_marks'] }}</td>
                                                        <td>{{ $report['submission_date'] }}</td>
                                                        <td>{{ $report['evaluation_date'] }}</td>
                                                        <td>{{ $report['evaluated_by'] }}</td>
                                                        <td><button
                                                                class="primary-btn small {{ $report['status'] == 'Completed' ? 'bg-success' : 'bg-danger' }} text-white border-0">{{ $report['status'] }}</button>
                                                        </td>
                                                        <td>
                                                            <x-drop-down>
                                                                <a class="dropdown-item modalLink" title="Evaluation Report"
                                                                    data-modal-size="large-modal"
                                                                    href="{{ route('homework-report-view', [@$report['student_id'], @$report['class_id'], @$report['section_id'], @$report['homework_id']]) }}">
                                                                    @lang('common.view')
                                                                </a>
                                                            </x-drop-down>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </x-table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endisset
            </div>
        </div>
    </section>
@endsection
@include('backEnd.partials.data_table_js')
@include('backEnd.partials.date_picker_css_js')
