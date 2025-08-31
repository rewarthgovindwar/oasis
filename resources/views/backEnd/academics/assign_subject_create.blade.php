@extends('backEnd.master')

@section('title')
    @lang('academics.assign_subject_create')
@endsection

@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('academics.assign_subject_create')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('academics.academics')</a>
                    <a href="{{ route('assign_subject') }}">@lang('academics.assign_subject')</a>
                    <a href="{{ route('assign_subject_create') }}">@lang('academics.assign_subject_create')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="main-title">
                        <h3 class="mb-30">@lang('common.select_criteria')</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">

                    <div class="white-box">
                        {{ html()->form('POST', route('assign_subject_search'))->attributes([
                                'class' => 'form-horizontal',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                                'id' => 'search_student',
                            ])->open() }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                            @if (moduleStatusCheck('University'))
                            @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    [
                                        'required' => ['USN', 'UD', 'UA', 'US', 'USL'],
                                        'div' => 'col-lg-3',
                                        'hide' => ['USUB'],
                                        'id_prefix' => 'assign'
                                    ]
                                )
                            @else  
                                @include('backEnd.common.search_criteria', [
                                    'div' => shiftEnable() ? 'col-lg-4' : 'col-lg-6',
                                    'visiable' => ['shift', 'class', 'section'],
                                    'required' => ['class', 'section'],
                                    'class_name' => 'class',
                                    'section_name' => 'section',
                                    'title' => [],
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

    @if (isset($assign_subjects) && $assign_subjects->count() > 0)
        <section class="admin-visitor-area">
            <div class="container-fluid p-0">
                <div class="row mt-40">
                    <div class="col-lg-6 col-md-6 col-9">
                        <div class="main-title">
                            <h3 class="mb-30">@lang('academics.assign_subject_create') </h3>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 text-right col-3">
                        <button class="primary-btn icon-only fix-gr-bg" id="addNewSubject" type="button">
                            <span class="ti-plus"></span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="white-box">
                            {{ html()->form('POST', route('assign-subject-store'))->attributes([
                                    'class' => 'form-horizontal',
                                    'files' => true,
                                    'enctype' => 'multipart/form-data',
                                    'id' => 'assign_subject',
                                ])->open() }}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="assign-subject" id="assign-subject">
                                        <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                                        <input type="hidden" name="update" value="1">
                                        @if (moduleStatusCheck('University'))
                                            <input type="hidden" name="un_department_id" id="un_department_id" value="{{ @$un_input['un_department_id'] }}">
                                            <input type="hidden" name="un_faculty_id" id="un_faculty_id" value="{{ @$un_input['un_faculty_id'] }}">
                                            <input type="hidden" name="un_session_id" id="un_session_id" value="{{ @$un_input['un_session_id'] }}">
                                            <input type="hidden" name="un_academic_id" id="un_academic_id" value="{{ @$un_input['un_academic_id'] }}">
                                            <input type="hidden" name="un_semester_id" id="un_semester_id" value="{{ @$un_input['un_semester_id'] }}">
                                            <input type="hidden" name="un_semester_label_id" id="un_semester_label_id" value="{{ @$un_input['un_semester_label_id'] }}">
                                            <input type="hidden" name="un_section_id" id="un_section_id" value="{{ @$un_input['un_section_id'] }}">
                                            @else    
                                            <input type="hidden" name="class" id="class_id" value="{{ @$class_id }}">
                                            <input type="hidden" name="section" id="section_id" value="{{ @$section_id }}">   
                                            @if(shiftEnable())
                                            <input type="hidden" name="shift" id="shift_id" value="{{@$shift_id}}">     
                                            @endif                                    
                                        @endif
                                        @php $i = 4; @endphp
                                        @foreach ($assign_subjects as $assign_subject)
                                            <div class="col-lg-12 mb-30" id="assign-subject-{{ $i }}">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-5 mb-3 mb-lg-0">
                                                        <select class="primary_select form-control subject"
                                                            name="subjects[]">
                                                            <option data-display="@lang('common.select_subjects')" value="">
                                                                @lang('common.select_subjects')</option>
                                                            @foreach ($subjects as $subject)
                                                                <option value="{{ $subject->id }}"
                                                                    {{ @$assign_subject->subject_id == $subject->id ? 'selected' : '' }}>
                                                                    {{ @$subject->subject_name }}({{ @$subject->subject_code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-5 mb-3 mb-lg-0">
                                                        <select class="primary_select form-control" name="teachers[]">
                                                            <option data-display="@lang('common.select_teacher')" value="">
                                                                @lang('common.select_teacher')</option>
                                                            @foreach ($teachers as $teacher)
                                                                <option value="{{ @$teacher->id }}"
                                                                    {{ @$assign_subject->teacher_id == @$teacher->id ? 'selected' : '' }}>
                                                                    {{ @$teacher->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-2 col-12 text-center text-lg-left">
                                                        <button class="primary-btn icon-only fix-gr-bg" id="removeSubject"
                                                            onclick="deleteSubject({{ $i }})" type="button">
                                                            <span class="ti-trash"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @php $i++; @endphp
                                        @endforeach

                                    </div>
                                </div>
                                @if (userPermission('assign-subject-store'))
                                    <div class="col-lg-12 mt-20 text-right">
                                        <button type="submit" class="primary-btn small fix-gr-bg submit">
                                            <span class="ti-save pr-2"></span>
                                            @lang('academics.save')
                                        </button>
                                    </div>
                                @endif
                            </div>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @elseif(isset($assign_subjects) && $assign_subjects->count() == 0)
        <section class="admin-visitor-area">
            <div class="container-fluid p-0">
                <div class="row mt-40">
                    <div class="col-lg-6 col-md-6 col-9">
                        <div class="main-title">
                            <h3 class="mb-30">@lang('academics.assign_subject')</h3>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 text-right col-3">
                        <button class="primary-btn icon-only fix-gr-bg" id="addNewSubject" type="button">
                            <span class="ti-plus"></span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="white-box">
                            {{ html()->form('POST', route('assign-subject-store'))->attributes([
                                    'class' => 'form-horizontal',
                                    'files' => true,
                                    'enctype' => 'multipart/form-data',
                                    'id' => 'assign_subject',
                                ])->open() }}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="assign-subject" id="assign-subject">
                                        <input type="hidden" name="url" id="url"
                                            value="{{ URL::to('/') }}">
                                        <input type="hidden" name="class" id="class_id"
                                            value="{{ @$class_id }}">
                                        <input type="hidden" name="section" id="section_id"
                                            value="{{ @$section_id }}">
                                        @if(shiftEnable())
                                        <input type="hidden" name="shift" id="shift_id"
                                            value="{{ @$shift_id }}">
                                        @endif
                                        <input type="hidden" name="update" value="0">
                                        <div class="col-lg-12 mb-30" id="assign-subject-4">
                                            <div class="row align-items-center">
                                                <div class="col-lg-5 mb-3 mb-lg-0">
                                                    <select class="primary_select form-control" name="subjects[]"
                                                        id="subjects">
                                                        <option data-display="@lang('common.select_subjects')" value="">
                                                            @lang('common.select_subjects')</option>
                                                        @foreach ($subjects as $subject)
                                                            <option value="{{ @$subject->id }}">
                                                                {{ @$subject->subject_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-5 mb-3 mb-lg-0">
                                                    <select class="primary_select form-control" name="teachers[]">
                                                        <option data-display="@lang('common.select_teacher')" value="">
                                                            @lang('common.select_teacher')</option>
                                                        @foreach ($teachers as $teacher)
                                                            <option value="{{ @$teacher->id }}">
                                                                {{ @$teacher->full_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-2 col-12 text-center text-lg-left">
                                                    <button class="primary-btn icon-only fix-gr-bg" type="button">
                                                        <span class="ti-trash" id="removeSubject"
                                                            onclick="deleteSubject(4)"></span>
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-lg-12 mt-20 text-right">
                                    <button type="submit" class="primary-btn small fix-gr-bg submit">
                                        <span class="ti-save pr-2"></span>
                                        @lang('academics.save')
                                    </button>
                                </div>
                            </div>
                            {{ html()->form()->close() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

@endsection
