@extends('backEnd.master')
@section('title')
    @lang('exam.all_exam_position')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('exam.all_exam_position') </h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('exam.exam')</a>
                    <a href="#">@lang('reports.settings')</a>
                    <a href="#">@lang('exam.all_exam_position')</a>
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
                    {{ html()->form('POST', route('all-exam-report-position-store'))->attribute('class', 'form-horizontal')->open() }}
                    <div class="row">
                        <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                        @if (moduleStatusCheck('University'))
                            @includeIf('university::common.session_faculty_depart_academic_semester_level', [
                                'mt' => 'mt-30',
                                'hide' => ['USUB'],
                                'required' => ['USL', 'USEC'],
                            ])
                        @else
                            @include('backEnd.shift.shift_class_section_include', [
                                'div' => shiftEnable() ? 'col-lg-4' : 'col-lg-6',
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
