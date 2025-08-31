@extends('backEnd.master')
@section('title')
    @lang('homework.evaluation_report')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('homework.evaluation_report')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('homework.home_work')</a>
                    <a href="#">@lang('homework.evaluation_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
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
                        {{ html()->form('POST', route('search-evaluation'))->attributes([
                            'class' => 'form-horizontal',
                            'files' => true,
                            'enctype' => 'multipart/form-data',
                        ])->open() }}
                        <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                        @if (moduleStatusCheck('University'))
                            <div class="row">
                                @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    [
                                        'required' => ['USN', 'UD', 'UA', 'US', 'USL', 'USEC', 'USUB'],
                                        'subject' => true,
                                    ]
                                )
                            </div>
                        @else
                            <div class="row">
                                
                                @include('backEnd.common.search_criteria', [
                                    'mt' => ' mt-30-md',
                                    'div' => shiftEnable() ? 'col-lg-3' : 'col-lg-4',
                                    'required' => ['class','subject'],
                                    'visiable' => ['subject', 'shift', 'class', 'section'],
                                    'subject' => true,
                                    'class_name' => 'class_id',
                                    'section_name' => 'section_id',
                                    'selected' => [
                                        'shift_id' => @$shift_id,
                                        'section_id' => @$section_id,
                                        'class_id' => @$class_id,
                                        'subject_id' => @$subject_id
                                    ],
                                ])
                        @endif

                        <div class="col-lg-12 mt-20 text-right">
                            <button type="submit" class="primary-btn small fix-gr-bg">
                                <span class="ti-search pr-2"></span>
                                @lang('common.search')
                            </button>
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
        @if (@$homeworkLists)
            <div class="row mt-40">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-4 no-gutters">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('homework.evaluation_report')</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <x-table>
                                    <table id="table_id" class="table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @if (moduleStatusCheck('University'))
                                                    <th>@lang('homework.home_work_date')</th>
                                                    <th>@lang('homework.submission_date')</th>
                                                    <th>@lang('common.action')</th>
                                                @else
                                                    <th>@lang('common.subject')</th>
                                                    <th>@lang('homework.home_work_date')</th>
                                                    <th>@lang('homework.submission_date')</th>
                                                    <th>@lang('homework.complete/pending')</th>
                                                    <th>@lang('homework.complete')(%)</th>
                                                    <th>@lang('common.action')</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (moduleStatusCheck('University'))
                                                @foreach ($homeworkLists as $value)
                                                    <tr>
                                                        <td>{{ $value->homework_date != '' ? dateConvert($value->homework_date) : '' }}</td>
                                                        <td>{{ $value->submission_date != '' ? dateConvert($value->submission_date) : '' }}</td>
                                                        <td>
                                                            <x-drop-down>
                                                                @if (userPermission('view-evaluation-report'))
                                                                    <a class="dropdown-item modalLink"
                                                                        title="View Evaluation Report"
                                                                        data-modal-size="full-width-modal"
                                                                        href="{{ route('view-evaluation-report', @$value->id) }}">
                                                                        @lang('common.view')
                                                                    </a>
                                                                @endif
                                                            </x-drop-down>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                @foreach ($homeworkLists as $value)
                                                    <tr>
                                                        <td>{{ $value->subjects ? $value->subjects->subject_name : '' }}</td>
                                                        <td>{{ $value->homework_date != '' ? dateConvert($value->homework_date) : '' }}</td>
                                                        <td>{{ $value->submission_date != '' ? dateConvert($value->submission_date) : '' }}</td>
                                                        @php
                                                            $homeworkPercentage = $homeworkPercentageData[$value->id] ?? null;
                                                        @endphp
                                                        <td>
                                                            @if (isset($homeworkPercentage))
                                                                {{ $homeworkPercentage['totalHomeworkCompleted'] . '/' . ($homeworkPercentage['totalStudents'] - $homeworkPercentage['totalHomeworkCompleted']) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($homeworkPercentage))
                                                                {{ number_format(($homeworkPercentage['totalHomeworkCompleted'] * 100) / $homeworkPercentage['totalStudents'], 2) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <x-drop-down>
                                                                @if (userPermission('view-evaluation-report'))
                                                                    <a class="dropdown-item modalLink"
                                                                        title="View Evaluation Report"
                                                                        data-modal-size="full-width-modal"
                                                                        href="{{ route('view-evaluation-report', @$value->id) }}">
                                                                        @lang('common.view')
                                                                    </a>
                                                                @endif
                                                            </x-drop-down>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    
                                    
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
