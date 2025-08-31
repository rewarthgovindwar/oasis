@extends('backEnd.master')
@section('title')
    @lang('behaviourRecords.student_incident_report')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('behaviourRecords.student_incident_report')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('behaviourRecords.dashboard')</a>
                    <a href="#">@lang('behaviourRecords.behaviour_records')</a>
                    <a href="#">@lang('behaviourRecords.student_incident_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row mt-20">
                <div class="col-lg-12 student-details up_admin_visitor">
                    <div class="row">
                        <div class="col-lg-12">
                            {{ html()->form('GET', route('behaviour_records.student_incident_report_search'))->attributes([
                                    'class' => 'form-horizontal',
                                    'enctype' => 'multipart/form-data',
                                    'files' => true,
                                ])->open() }}
                            <div class="white-box">
                                <div class="row">
                                    <div class="col-lg-8 col-md-6">
                                        <div class="main-title">
                                            <h3 class="mb-15">@lang('behaviourRecords.select_criteria') </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    @if (moduleStatusCheck('University'))
                                        @includeIf(
                                            'university::common.session_faculty_depart_academic_semester_level',
                                            ['mt' => 'mt-30', 'hide' => ['USUB'], 'required' => ['USEC']]
                                        )
                                    @else    
                                        @include('backEnd.common.search_criteria', [
                                            'div' => shiftEnable() ? 'col-lg-3' : 'col-lg-4',
                                            'required' => ['academic', 'class', 'section'],
                                            'visiable' => ['academic', 'shift', 'class', 'section'],
                                            'class_name' => 'class_id',
                                            'section_name' => 'section_id',
                                            'selected' => [
                                                'class_id' => @$class_id,
                                                'section_id' => @$section_id,
                                                'shift_id' => @$shift_id,
                                                'academic_year' => @$academic_year,
                                            ]
                                        ]) 
                                    @endif
                                    
                                    <div class="col-lg-12 mt-20 text-right">
                                        <button type="submit" class="primary-btn small fix-gr-bg">
                                            <span class="ti-search pr-2"></span>
                                            @lang('behaviourRecords.search')
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                    @isset($student_records)
                        <div class="row mt-40">
                            <div class="col-lg-12">
                                <div class="white-box">
                                    <div class="row">
                                        <div class="col-lg-4 no-gutters">
                                            <div class="main-title">
                                                <h3 class="mb-15">@lang('behaviourRecords.student_incident_list') </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <x-table>
                                                <table id="table_id" class="table" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>@lang('behaviourRecords.admission_no')</th>
                                                            <th>@lang('behaviourRecords.student_name')</th>
                                                            @if (moduleStatusCheck('University'))
                                                            <th>@lang('university::un.faculty')(@lang('university::un.department'))</th>
                                                            @else    
                                                            <th>@lang('behaviourRecords.class')(@lang('behaviourRecords.section')) @if(shiftEnable()) - @lang('common.shift') @endif</th>
                                                            @endif 
                                                            
                                                            
                                                            <th>@lang('behaviourRecords.gender')</th>
                                                            <th>@lang('behaviourRecords.phone')</th>
                                                            <th>@lang('behaviourRecords.total_incidents')</th>
                                                            <th>@lang('behaviourRecords.total_points')</th>
                                                            <th>@lang('behaviourRecords.actions')</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($student_records as $key => $data)
                                                            @php
                                                                $incident = 0;
                                                                foreach ($data->student->incidents as $student_point) {
                                                                    $incident += $student_point->incident->point;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $data->student->admission_no }}</td>
                                                                <td>
                                                                    <a target="_blank"
                                                                        href="{{ route('student_view', [$data->student->id]) }}">{{ $data->student->first_name }}
                                                                        {{ $data->student->last_name }}</a>
                                                                </td>
                                                                @if (moduleStatusCheck('University'))
                                                                <td>{{ $data->unFaculty->name }}({{ $data->unDepartment->name }}) </td>
                                                                @else                                                                    
                                                                <td>{{ $data->class->class_name }}({{ $data->section->section_name }}) @if(shiftEnable()) - {{ $data->shift->name }} @endif</td>
                                                                @endif
                                                                <td>{{ $data->student->gender->base_setup_name }}</td>
                                                                <td>{{ $data->student->mobile }}</td>
                                                                <td>{{ $data->student?->incidents->count() }}</td>
                                                                <td>{{ $data->student?->incidents->sum('point')}}
                                                                </td>
                                                                <td>
                                                                    <x-drop-down>
                                                                        <a class="dropdown-item modalLink"
                                                                            data-modal-size="large-modal"
                                                                            title="Student All Incident-{{ $data->student->full_name }}"
                                                                            href="{{ route('behaviour_records.view_student_all_incident_modal', [$data->student->id]) }}">@lang('common.view')</a>
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
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </section>
@endsection
@include('backEnd.partials.data_table_js')
