@extends('backEnd.master')
@section('title')
    @lang('exam.position_setup')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('exam.position_setup') </h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('exam.exam')</a>
                    <a href="#">@lang('reports.settings')</a>
                    <a href="#">@lang('exam.position_setup')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="white-box">
                    <div class="row">
                        <div class="col-lg-8 col-md-6">
                            <div class="main-title">
                                <h3 class="mb-15">@lang('common.select_criteria')</h3>
                            </div>
                        </div>
                    </div>
                    {{ html()->form('POST', route('exam-report-position-store'))->attributes([
                            'class' => 'form-horizontal',
                            'id' => 'search_student',
                        ])->open() }}
                    <div class="row">
                        <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                        @if (moduleStatusCheck('University'))
                            @includeIf('university::common.session_faculty_depart_academic_semester_level', [
                                'mt' => 'mt-30',
                                'hide' => ['USUB'],
                                'required' => ['USL', 'USEC'],
                            ])
                            <div class="col-lg-3 mt-30">
                                <label for="">@lang('reports.select_exam') *</label>
                                <select class="primary_select form-control{{ $errors->has('exam') ? ' is-invalid' : '' }}"
                                    name="exam">
                                    <option data-display="@lang('reports.select_exam') *" value="">@lang('reports.select_exam')
                                        *
                                    </option>
                                    @foreach ($exams as $exam)
                                        <option value="{{ $exam->id }}"
                                            {{ isset($exam_id) ? ($exam_id == $exam->id ? 'selected' : '') : '' }}>
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
                            <div class="@if(shiftEnable()) col-lg-3 @else col-lg-4 @endif mt-30-md md_mb_20">
                                <select class="primary_select form-control{{ $errors->has('exam') ? ' is-invalid' : '' }}"
                                    name="exam">
                                    <option data-display="@lang('reports.select_exam') *" value="">@lang('reports.select_exam')
                                        *
                                    </option>
                                    @foreach ($exams as $exam)
                                        <option value="{{ $exam->id }}"
                                            {{ isset($exam_id) ? ($exam_id == $exam->id ? 'selected' : '') : '' }}>
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
                                'mt' => 'mt-30-md md_mb_20',
                                'visiable' => ['shift', 'class', 'section'],
                                'required' => ['class'],
                                'title' => [],
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
                                <span class="ti-pin2"></span>
                                @lang('common.generate')
                            </button>
                        </div>
                    </div>
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </section>
@endsection
