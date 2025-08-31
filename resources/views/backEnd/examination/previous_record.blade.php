@extends('backEnd.master')
@section('title')
    @lang('reports.previous_record')
@endsection
@section('mainContent')
    <input type="text" hidden value="{{ @$clas->class_name }}" id="cls">
    <input type="text" hidden value="{{ @$sec->section_name }}" id="sec">
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('reports.previous_record') </h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard') </a>
                    <a href="#">@lang('reports.reports')</a>
                    <a href="{{ route('previous-record') }}">@lang('reports.previous_record') </a>
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
                            <div class="col-lg-4 col-md-6">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('common.select_criteria') </h3>
                                </div>
                            </div>
                        </div>
                        {{ html()->form('POST', route('previous-records'))->attributes([
                                'class' => 'form-horizontal',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                                'id' => 'search_student',
                            ])->open() }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">

                            @include('backEnd.shift.shift_class_section_include', [
                                'div' => shiftEnable() ? 'col-lg-3 col-md-3' : 'col-lg-4 col-md-3',
                                'visiable' => ['academic_year', 'shift', 'class', 'section'],
                                'required' => ['academic_year', 'class', 'section'],
                                'title' => ['academic_year', 'shift', 'class', 'section'],
                                'academic_year_name' => 'promote_session',
                                'class_name' => 'promote_class',
                                'section_name' => 'promote_section',
                                'selected' => [
                                    'academic_year' => @$year,
                                    'shift_id' => @$shift_id,
                                    'class_id' => @$class_id,
                                    'section_id' => @$section_id,

                                ],
                            ])

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
            @if (isset($students))
                <div class="row mt-40">


                    <div class="col-lg-12">
                        <div class="white-box">
                            <div class="row">
                                <div class="col-lg-4 no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-15">@lang('common.student_list') (
                                            {{ isset($students) ? $students->count() : 0 }})</h3>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-lg-12">
                                    <x-table>
                                        <div class="table-responsive">
                                            <table id="table_id_tt" class="table" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('student.admission_no')</th>
                                                        <th>@lang('student.roll_no')</th>
                                                        <th>@lang('common.name')</th>
                                                        <th>@lang('common.class')</th>
                                                        <th>@lang('student.father_name')</th>
                                                        <th>@lang('common.date_of_birth')</th>
                                                        <th>@lang('common.gender')</th>
                                                        <th>@lang('common.type')</th>
                                                        <th>@lang('common.phone')</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($students as $data)
                                                        @php
                                                            $studentInfo = json_decode($data->student_info);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $data->admission_number }}</td>
                                                            <td>{{ $data->previous_roll_number }}</td>
                                                            <td>{{ $studentInfo->full_name }}</td>
                                                            <td>{{ $class->class_name }} ( {{ $section->section_name }} )
                                                            </td>
                                                            <td>{{ @$data->student->parents->fathers_name }}</td>
                                                            <td>{{ dateConvert(@$data->student->date_of_birth) }}</td>
                                                            <td>{{ @$data->student->gender->base_setup_name }}</td>
                                                            <td>{{ @$data->student->category->category_name }}</td>
                                                            <td>{{ @$data->student->mobile }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </x-table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

@endsection
@include('backEnd.partials.data_table_js')
