<div class="row">
    <input type="hidden" id="classToSectionRoute" value="{{route('fees.ajax-get-all-section')}}">
    <input type="hidden" id="sectionToStudentRoute" value="{{route('fees.ajax-section-all-student')}}">
    <input type="hidden" id="classToStudentRoute" value="{{route('fees.ajax-get-all-student')}}">
    <div class="col-lg-3 mt-30-md">
        <label class="primary_input_label" for="">
            {{ __('common.date_range') }}
                <span class="text-danger"></span>
        </label>
        <input class="primary_input_field primary_input_field form-control" type="text" name="date_range" value="">
    </div>

    {{-- @include('backEnd.shift.shift_class_section_include', [
            'div' => 'col-lg-3',
            'visiable' => ['class', 'section'],
            'required' => ['class', 'section'],
            'title' => ['class', 'section', 'shift'],
            'selected' => [
                'class_id' => @$class_id,
                'section_id' => @$section_id,
            ],
        ]) --}}
    @include('backEnd.common.search_criteria', [
        'mt' => 'mt-30-md',
        'div' => 'col-lg-3',
        'required' => ['class', 'section'],
        'visiable' => ['shift', 'class', 'section', 'student'],
        'selected' => [
            'shift_id' => @$shift,
            'section_id' => @$section,
            'class_id' => @$class,
            'student_id' => @$student
        ],
    ])
    {{-- <div class="col-lg-3 mt-30-md" id="selectStudentDiv">
        <label class="primary_input_label" for="">
            {{ __('common.student') }}
                <span class="text-danger"> </span>
        </label>
        <select class="primary_select form-control{{ $errors->has('student') ? ' is-invalid' : '' }}" id="selectStudent" name="student">
            <option data-display="@lang('common.select_student')" value="">@lang('common.select_student')</option>
        </select>
        <div class="pull-right loader loader_style" id="student_section_loader">
            <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
        </div>
        @if ($errors->has('student'))
            <span class="text-danger invalid-select" role="alert">
                {{ $errors->first('student') }}
            </span>
        @endif
    </div> --}}

    <div class="col-lg-12 mt-20 text-right">
        <button type="submit" class="primary-btn small fix-gr-bg">
            <span class="ti-search pr-2"></span>
            @lang('common.search')
        </button>
    </div>
</div>