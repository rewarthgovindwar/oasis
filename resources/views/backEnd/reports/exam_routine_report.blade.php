@extends('backEnd.master')
@section('title')
    @lang('reports.exam_routine_report')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('reports.exam_routine_report')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('reports.reports')</a>
                    <a href="#">@lang('reports.exam_routine_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">

                    <div class="white-box">
                    <div class="row">
                        <div class="col-lg-8 col-md-6">
                            <div class="main-title">
                                <h3 class="mb-15">@lang('common.select_criteria') </h3>
                            </div>
                        </div>
                    </div>
                    {{ html()->form('POST', route('exam_routine_reports'))->attributes([
                        'class' => 'form-horizontal',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                    ])->open() }}                    
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                            @if(moduleStatusCheck('University'))
                                @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    ['mt' => 'mt-30', 'hide' => ['USUB'], 'required' => ['USL']]
                                )
                            <div class="col-lg-3 mt-30">
                                <label class="primary_input_label" for="">{{ __('exam.exam') }}<span
                                        class="text-danger">
                                        *</span></label>
                                <select class="primary_select form-control{{ $errors->has('exam') ? ' is-invalid' : '' }}"
                                    name="exam">
                                    <option data-display="@lang('reports.select_exam') *" value="">
                                        @lang('reports.select_exam') *</option>
                                    @foreach ($exam_types as $exam)
                                        <option value="{{ $exam->id }}"
                                            {{ isset($exam_term_id) ? ($exam->id == $exam_term_id ? 'selected' : '') : '' }}>
                                            {{ $exam->title }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('exam'))
                                    <span class="text-danger invalid-select" role="alert">
                                        {{ $errors->first('exam') }}
                                    </span>
                                @endif
                            </div>
                            @else
                                <div class="@if(shiftEnable()) col-lg-3 @else col-lg-4 @endif mt-30-md">
                                    <label class="primary_input_label" for="">{{ __('exam.exam') }}<span
                                            class="text-danger">
                                            *</span></label>
                                    <select class="primary_select form-control{{ $errors->has('exam') ? ' is-invalid' : '' }}"
                                        name="exam">
                                        <option data-display="@lang('reports.select_exam') *" value="">
                                            @lang('reports.select_exam') *</option>
                                        @foreach ($exam_types as $exam)
                                            <option value="{{ $exam->id }}"
                                                {{ isset($exam_term_id) ? ($exam->id == $exam_term_id ? 'selected' : '') : '' }}>
                                                {{ $exam->title }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('exam'))
                                        <span class="text-danger invalid-select" role="alert">
                                            {{ $errors->first('exam') }}
                                        </span>
                                    @endif
                                </div>
                                @include('backEnd.shift.shift_class_section_include', [
                                    'div' => shiftEnable() ? 'col-lg-3' : 'col-lg-4',
                                    'visiable' => ['shift', 'class', 'section'],
                                    'required' => ['class', 'section'],
                                    'title' => ['shift', 'class', 'section'],
                                    'class_name' => 'class',
                                    'section_name' => 'section',
                                    'selected' => [
                                        'shift_id' => @$shift_id,
                                        'class_id' => @$class_id,
                                        'section_id' => @$section_id,
                                    ],
                                ])
                           
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
            </div>
        </div>
    </section>
    @if (isset($exam_schedules))
        <section class="mt-20">
            <div class="container-fluid p-0">
                <div class="white-box mt-40">
                <div class="row justify-content-end mb-3">
                    <div class="col-lg-6 col-md-6">
                        @if(moduleStatusCheck('University'))
                            {{-- //$un_semester_label_id --}}
                            {{-- $un_section_id --}}
                            {{-- $exam_term_id --}}
                            <a href="{{ route('exam-routine-print', [$class_id, $section_id, $exam_type_id]) }}"
                               class="primary-btn small fix-gr-bg pull-right" target="_blank"><i class="ti-printer"> </i>
                                @lang('common.print')</a>
                        @else
                            
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="main-title">
                            <h3 class="mb-15">@lang('reports.exam_routine')</h3>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-12">
                        <x-table>
                            <table id="table_id" class="table Crm_table_active3 no-footer dtr-inline collapsed"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>
                                            @lang('reports.date_&_day')
                                        </th>
                                        <th>@lang('common.subject')</th>
                                        @if(moduleStatusCheck('University'))
                                            <th> @lang('university::un.semester_label') (@lang('common.section'))</th>
                                        @else
                                            <th>@if(shiftEnable()) @lang('admin.class_Sec_shift') @else @lang('admin.class_Sec') @endif</th>
                                        @endif
                                        <th>@lang('common.teacher')</th>
                                        <th>@lang('common.time')</th>
                                        <th>@lang('common.duration')</th>
                                        <th>@lang('common.room')</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($exam_schedules as $date => $exam_routine)
                                        <tr>
                                            <td>{{ dateConvert($exam_routine->date) }}
                                                <br>{{ Carbon::createFromFormat('Y-m-d', $exam_routine->date)->format('l') }}
                                            </td>
                                            <td>
                                                <strong>
                                                    {{ $exam_routine->subject ? $exam_routine->subject->subject_name : '' }}
                                                </strong>
                                                {{ $exam_routine->subject ? '(' . $exam_routine->subject->subject_code . ')' : '' }}
                                            </td>

                                            @if(moduleStatusCheck('University'))
                                                <td>{{ $exam_routine->unSemesterLabel ? $exam_routine->unSemesterLabel->name : '' }} {{ $exam_routine->section ? '(' . $exam_routine->section->section_name . ')' : '' }}</td>
                                            @else
                                                <td>
                                                    {{ $exam_routine->class ? $exam_routine->class->class_name : '' }} {{ $exam_routine->section ? '(' . $exam_routine->section->section_name . ')' : '' }} @if(shiftEnable()) {{ $exam_routine->shift ? '[' . $exam_routine->shift->shift_name . ']' : '' }} @endif
                                                </td>
                                            @endif
                                            
                                            <td>{{ $exam_routine->teacher ? $exam_routine->teacher->full_name : '' }}</td>

                                            <td> {{ date('h:i A', strtotime(@$exam_routine->start_time)) }} -
                                                {{ date('h:i A', strtotime(@$exam_routine->end_time)) }} </td>
                                            <td>
                                                @php
                                                    $duration = strtotime($exam_routine->end_time) - strtotime($exam_routine->start_time);
                                                @endphp

                                                {{ timeCalculation($duration) }}
                                            </td>

                                            <td>{{ $exam_routine->classRoom ? $exam_routine->classRoom->room_no : '' }}</td>

                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </x-table>
                    </div>
                </div>
                </div>
            </div>
        </section>
    @endif



@endsection
@include('backEnd.partials.data_table_js', ['i' => true])
