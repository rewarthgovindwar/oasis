@php
$div = isset($div) ? $div : 'col-lg-4';
$mt = isset($mt) ? $mt : 'mb-15';
$subject = isset($subject) ? true : false;
$required = $required ?? [];
$selected = isset($selected) ? $selected : null;

$academic_year = $selected && isset($selected['academic_year']) ? $selected['academic_year'] : null;
$shift_id = $selected && isset($selected['shift_id']) ? $selected['shift_id'] : null;
$class_id = $selected && isset($selected['class_id']) ? $selected['class_id'] : null;
$section_id = $selected && isset($selected['section_id']) ? $selected['section_id'] : null;
$subject_id = $selected && isset($selected['subject_id']) ? $selected['subject_id'] : null;
$student_id = $selected && isset($selected['student_id']) ? $selected['student_id'] : null;

if ($academic_year) {
$classes = classes($academic_year) ?? null;
$sections = $class_id ? sections($class_id, $academic_year) : null;
$subjects = $class_id && $section_id ? subjects($class_id, $section_id, $academic_year) : null;
$students = $class_id && $section_id ? students($class_id, $section_id, $academic_year) : null;
} else {
$sections = $class_id ? sections($class_id) : null;
$subjects = $class_id && $section_id ? subjects($class_id, $section_id) : null;
}
$visiable = $visiable ?? [];

$shift_name = isset($shift_name) ? $shift_name : 'shift';
$class_name = isset($class_name) ? $class_name : 'class';
$section_name = isset($section_name) ? $section_name : 'section';
$subject_name = isset($subject_name) ? $subject_name : 'subject_id';
$academic_name = isset($academic_name) ? $academic_name : 'academic_year';
@endphp

@if (in_array('academic', $visiable))
<div class="{{ $div . ' ' . $mt }}">
    <div class="primary_input ">
        <label class="primary_input_label" for="">{{ __('common.academic_year') }}
            <span class="text-danger">{{ in_array('academic', $required) ? '*' : '' }}</span>
        </label>
        <select
            class="primary_select  form-control{{ $errors->has('academic_year') ? ' is-invalid' : '' }} common_academic_years"
            name="{{ $academic_name }}" id="common_academic_years">
            <option data-display="@lang('common.academic_year') {{ in_array('academic', $required) ? '*' : '' }}"
                value="">
                @lang('common.academic_year') {{ in_array('academic', $required) ? '*' : '' }}
            </option>
            @isset($sessions)
            @foreach ($sessions as $session)
            <option value="{{ $session->id }}"
                {{ isset($academic_year) && $academic_year == $session->id ? 'selected' : (getAcademicId() == $session->id ? 'selected' : '') }}>
                {{ $session->year }}[{{ $session->title }}]</option>
            @endforeach
            @endisset

        </select>

        @if ($errors->has($academic_name))
            <span class="text-danger" role="alert">
                {{ $errors->first($academic_name) }}
            </span>
        @endif
    </div>
</div>
@endif

@if(shiftEnable())
    @if (in_array('shift', $visiable))
        <div class="{{ $div . ' ' . $mt }}">
            <div class="primary_input " id="common_select_shifts_div">
                <label class="primary_input_label" for="">{{ __('common.shift') }}
                    <span class="text-danger">{{ in_array('shift', $required) ? '*' : '' }}</span>
                </label>
                <select class="primary_select form-control{{ $errors->has('shift') ? ' is-invalid' : '' }}" name="{{ $shift_name }}"
                    id="common_select_shifts">
                    <option data-display="@lang('common.select_shift') {{ in_array('shift', $required) ? '*' : '' }}" value="">
                        {{ __('common.select_shift') }} {{ in_array('shift', $required) ? '*' : '' }}</option>
                    @foreach (shifts() as $shift)
                        <option value="{{ $shift->id }}" {{ isset($shift_id) ? ($shift_id == $shift->id ? 'selected' : '') : '' }}>
                        {{ $shift->name }}</option>
                    @endforeach
                </select>

                <div class="pull-right loader loader_style" id="common_select_shifts_loader">
                    <img class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="loader">
                </div>
                <span class="text-danger">{{ $errors->first('shift') }}</span>
            </div>
        </div>
    @endif
@endif
@if (in_array('class', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_classes_div">
    <div class="primary_input mb-25">
        <label class="primary_input_label" for="">{{ __('common.class') }}
            <span class="text-danger">{{ in_array('class', $required) ? '*' : '' }}</span>
        </label>
        <select class="primary_select form-control{{ $errors->has('class_id') ? ' is-invalid' : '' }}" name="{{ $class_name }}"
            id="common_select_classes">
            <option data-display="@lang('common.select_class') {{ in_array('class', $required) ? '*' : '' }}" value="">
                {{ __('common.select_class') }} {{ in_array('class', $required) ? '*' : '' }}</option>
            @if (isset($classes))
            @foreach ($classes as $class)
            <option value="{{ $class->id }}" {{ isset($class_id) ? ($class_id == $class->id ? 'selected' : '') : '' }}>
                {{ $class->class_name }}</option>
            @endforeach
            @endif
        </select>
        <div class="pull-right loader loader_style" id="common_select_classes_loader">
            <img class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="loader">
        </div>
        <span class="text-danger">{{ $errors->first($class_name) }}</span>
    </div>
</div>

@endif
@if (in_array('section', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_sections_div">
    <label class="primary_input_label" for="">{{ __('common.section') }}
        <span class="text-danger">{{ in_array('section', $required) ? '*' : '' }}</span>
    </label>
    <select class="primary_select form-control{{ $errors->has('section_id') ? ' is-invalid' : '' }} select_section"
        id="common_select_sections" name="{{ $section_name }}">
        <option data-display="@lang('common.select_section') {{ in_array('section', $required) ? '*' : '' }}" value="">
            @lang('common.select_section') {{ in_array('section', $required) ? '*' : '' }}</option>
        @isset($sections)
        @foreach ($sections as $section)
        <option value="{{ $section->id }}"
            {{ isset($section_id) ? ($section_id == $section->section_id ? 'selected' : '') : '' }}>
            {{ $section->sectionName->section_name }}
        </option>
        @endforeach
        @endisset
    </select>
    <div class="pull-right loader loader_style" id="common_select_sections_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>


    @if ($errors->has($section_name))
    <span class="text-danger">
        {{ $errors->first($section_name) }}
    </span>
    @endif
</div>
@endif
@if (in_array('subject', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_subject_div">
    <label class="primary_input_label" for="">{{ __('common.subject') }}
        <span class="text-danger">{{ in_array('subject', $required) ? '*' : '' }}</span>
    </label>
    <select class="primary_select form-control{{ $errors->has('subject_id') ? ' is-invalid' : '' }} select_subject"
        id="common_select_subject" name="{{ $subject_name }}">
        <option data-display="@lang('common.select_subject') {{ in_array('subject', $required) ? ' *' : '' }}" value="">
            @lang('common.select_subject') {{ in_array('subject', $required) ? ' *' : '' }}</option>
        @isset($subjects)
        @foreach ($subjects as $subject)
        <option value="{{ $subject->subject_id }}"
            {{ isset($subject_id) ? ($subject_id == $subject->subject_id ? 'selected' : '') : '' }}>
            {{ $subject->subject->subject_name }}</option>
        @endforeach
        @endisset
    </select>
    <div class="pull-right loader loader_style" id="common_select_subject_loader" style="margin-top: -30px;padding-right: 21px;">
        <img src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="" style="width: 28px;height:28px;">
    </div>

    @if ($errors->has($subject_name))
    <span class="text-danger">
        {{ $errors->first($subject_name) }}
    </span>
    @endif
</div>
@endif
@if (in_array('student', $visiable))
<div class="{{ $div . ' ' . $mt }}" id="common_select_student_div">
    <label class="primary_input_label" for="">{{ __('common.student') }}
        <span class="text-danger">{{ in_array('student', $required) ? '*' : '' }}</span>
    </label>
    <select class="primary_select form-control{{ $errors->has('student') ? ' is-invalid' : '' }}"
        id="common_select_student" name="student">
        <option data-display="@lang('reports.select_student') {{ in_array('student', $required) ? '*' : '' }}" value="">
            @lang('reports.select_student') <span>{{ in_array('student', $required) ? '*' : '' }}</span>
        </option>
        @isset($students)
        @foreach ($students as $student)
        <option value="{{ $student->id }}"
            {{ isset($student_id) ? ($student_id == $student->id ? 'selected' : '') : '' }}>
            {{ $student->full_name }}
        </option>
        @endforeach
        @endisset
    </select>

    <div class="pull-right loader loader_style" id="common_select_student_loader">
        <img class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="loader">
    </div>

    @if ($errors->has('student'))
    <span class="text-danger">
        {{ $errors->first('student') }}
    </span>
    @endif
</div>
@endif

@push('script')
<script>
    $(document).ready(function () {
        let class_required = "{{ in_array('class', $required) ? ' *' : '' }}";
        let shift_required = "{{ in_array('shift', $required) ? ' *' : '' }}";
        let section_required = "{{ in_array('section', $required) ? ' *' : '' }}";
        let subject_required = "{{ in_array('subject', $required) ? ' *' : '' }}";
        let student_required = "{{ in_array('student', $required) ? ' *' : '' }}";
        $("#common_academic_years").on(
            "change",
            function () {
                var url = $("#url").val();
                var i = 0;
                var formData = {
                    id: $(this).val(),
                };

                // get class
                $.ajax({
                    type: "GET",
                    data: formData,
                    dataType: "json",
                    url: url + "/" + "academic-year-get-class",

                    beforeSend: function () {
                        $('#common_select_classes_loader').addClass('pre_loader').removeClass(
                            'loader');
                    },

                    success: function (data) {
                        $("#common_select_classes").empty().append(
                            $("<option>", {
                                value: '',
                                text: window.jsLang('select_class') + class_required,
                            })
                        );

                        if (data[0].length) {
                            $.each(data[0], function (i, className) {
                                $("#common_select_classes").append(
                                    $("<option>", {
                                        value: className.id,
                                        text: className.class_name,
                                    })
                                );
                            });
                        }
                        $('#common_select_classes').niceSelect('update');
                        $('#common_select_classes').trigger('change');
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    },
                    complete: function () {
                        i--;
                        if (i <= 0) {
                            $('#common_select_classes_loader').removeClass('pre_loader').addClass(
                                'loader');
                        }
                    }
                });
            }
        );

        @if(shiftEnable())
        $("#common_academic_years").on("change", function () {
            var url = $("#url").val();
            var i = 0;
            var selectedAcademicId = $("#common_academic_years").val();

            $.ajax({
                type: "GET",
                data: { id: selectedAcademicId },
                dataType: "json",
                url: url + "/" + "academic-year-get-shift",

                beforeSend: function () {
                    $('#common_select_shifts_loader').addClass('pre_loader').removeClass('loader');
                },

                success: function (data) {
                    $("#common_select_shifts").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_shift') + shift_required,
                        })
                    );

                    if (data.length && Array.isArray(data[0])) {
                        const shifts = data[0];
                        
                        $.each(shifts, function (i, shift) {
                            $("#common_select_shifts").append(
                                $("<option>", {
                                    text: shift.name,
                                    value: shift.id,
                                })
                            );
                        });
                    }

                    $('#common_select_shifts').niceSelect('update');
                    // auto select off, so no trigger('change')
                },

                error: function (data) {
                    console.log('Error:', data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_shifts_loader').removeClass('pre_loader').addClass('loader');
                    }
                }
            });
        });
        $("#common_select_shifts").on("change", function () {
            var url = $("#url").val();
            var i = 0;
            var selectedShiftId = $(this).val();
            if ($('#common_academic_years_div').length) {
                academic_id = $('#common_academic_years_div').find(":selected").val();
            }else{
                academic_id = '';
            }

            $.ajax({
                type: "GET",
                data: { 
                    id: selectedShiftId,
                    academic_id: academic_id ? academic_id : '',
                },
                dataType: "json",
                url: url + "/" + "shift-get-class",

                beforeSend: function () {
                    $('#common_select_classes_loader').addClass('pre_loader').removeClass('loader');
                },

                success: function (data) {
                    $("#common_select_classes").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_class') + class_required,
                        })
                    );

                    if (data.length) {
                        $.each(data, function (i, className) {
                            $("#common_select_classes").append(
                                $("<option>", {
                                    value: className.id,
                                    text: className.class_name,
                                })
                            );
                        });
                    }

                    $('#common_select_classes').niceSelect('update');
                    // auto select off, so no trigger('change')
                },

                error: function (data) {
                    console.log('Error:', data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_classes_loader').removeClass('pre_loader').addClass('loader');
                    }
                }
            });
        });
        @else
        $("#common_academic_years").on("change", function () {
            var url = $("#url").val();
            var i = 0;
            var selectedAcademicId = $("#common_academic_years").val();

            $.ajax({
                type: "GET",
                data: { id: selectedAcademicId },
                dataType: "json",
                url: url + "/" + "academic-year-get-class",

                beforeSend: function () {
                    $('#common_select_classes_loader').addClass('pre_loader').removeClass('loader');
                },

                success: function (data) {
                    $("#common_select_classes").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_class') + class_required,
                        })
                    );

                    if (data.length) {
                        $.each(data, function (i, className) {
                            $("#common_select_classes").append(
                                $("<option>", {
                                    value: className.id,
                                    text: className.name,
                                })
                            );
                        });
                    }

                    $('#common_select_classes').niceSelect('update');
                },

                error: function (data) {
                    console.log('Error:', data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_classes_loader').removeClass('pre_loader').addClass('loader');
                    }
                }
            });
        });
        @endif

        $("#common_select_classes").on("change", function () {

            var url = $("#url").val();
            var i = 0;
            var formData = {
                id: $(this).val(),
            };
            $.ajax({
                type: "GET",
                data: formData,
                dataType: "json",
                url: url + "/" + "ajaxStudentPromoteSection",

                beforeSend: function () {
                    $('#common_select_sections_loader').addClass('pre_loader').removeClass(
                        'loader');
                },
                success: function (data) {
                    $("#common_select_sections").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_section') +
                                section_required,
                        })
                    );

                    if (data[0].length) {
                        $.each(data[0], function (i, section) {
                            $("#common_select_sections").append(
                                $("<option>", {
                                    value: section.id,
                                    text: section.section_name,
                                })
                            );
                        });
                    }
                    $('#common_select_sections').niceSelect('update');
                    $('#common_select_sections').trigger('change');

                },
                error: function (data) {
                    console.log("Error:", data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_sections_loader').removeClass('pre_loader')
                            .addClass('loader');
                    }
                }
            });
        });
        $("#common_select_sections").on("change", function () {
            var url = $("#url").val();
            var i = 0;
            var subject = "{{ $subject }}";
            var select_class = $("#common_select_classes").val();
            var class_id = $("#common_select_classes").val();
            var section_id = $(this).val();

            var formData = {
                section: $(this).val(),
                class: $("#common_select_classes").val(),
            };
            // get section for student
            if(subject == false)
            {
            $.ajax({
                type: "GET",
                data: formData,
                dataType: "json",
                url: url + "/" + "ajaxSelectStudent",

                beforeSend: function () {
                    $('#common_select_student_loader').addClass('pre_loader').removeClass(
                        'loader');
                },

                success: function (data) {

                    $("#common_select_student").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_student') +
                                student_required,
                        })
                    );

                    if (data[0].length) {
                        $.each(data[0], function (i, student) {
                            $("#common_select_student").append(
                                $("<option>", {
                                    value: student.id,
                                    text: student.full_name,
                                })
                            );
                        });
                    }
                    $('#common_select_student').niceSelect('update');
                    $('#common_select_student').trigger('change');
                },
                error: function (data) {
                    console.log("Error:", data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_student_loader').removeClass('pre_loader')
                            .addClass('loader');
                    }
                }
            });
            }
            // get subject from section
            if(subject == true)
            {
                getSubject(class_id, section_id);
            }
        });

        function getSubject(class_id, section_id) {
            var url = $("#url").val();
            var i = 0;
            $.ajax({
                type: "GET",
                data: {class: class_id, section: section_id},
                dataType: "json",
                url: url + "/" + "ajaxSelectSubject",

                beforeSend: function () {
                    $('#common_select_student_loader').addClass('pre_loader').removeClass('loader');
                },

                success: function (data) {

                    $("#common_select_subject").empty().append(
                        $("<option>", {
                            value: '',
                            text: window.jsLang('select_subject'),
                        })
                    );

                    if (data[0].length) {
                        $.each(data[0], function (i, subject) {
                            $("#common_select_subject").append(
                                $("<option>", {
                                    value: subject.id,
                                    text: subject.subject_name,
                                })
                            );
                        });
                    }
                    $('#common_select_subject').niceSelect('update');
                    $('#common_select_subject').trigger('change');
                },
                error: function (data) {
                    console.log("Error:", data);
                },
                complete: function () {
                    i--;
                    if (i <= 0) {
                        $('#common_select_student_loader').removeClass('pre_loader').addClass(
                            'loader');
                    }
                }
            });
        }
    });
</script>
@endpush