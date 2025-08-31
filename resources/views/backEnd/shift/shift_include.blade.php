@php
    $grid_class = isset($grid_class) ? $grid_class : 'col-lg-3';
    $mt = isset($mt) ? $mt : ' ';
    $required = isset($required) ? $required : false;
    $shift_id = isset($editData) ? $editData : null;
    $label = isset($label) ? $label : null;
    $name = isset($name) ? $name : 'shift';
    $disabled = isset($disabled) ? $disabled : false;
    $id = isset($id) ? $id : 'common_shift';
@endphp

<div class="{{ $grid_class }} {{ $mt }}">
    <div class="primary_input " id="">
        @if (isset($label))
            <label class="primary_input_label" for="">{{ @$label }} @if ($required == true)
                    <span class="text-danger"> *</span>
                @endif </label>
        @endif
        <select class="primary_select select_shift form-control{{ $errors->has('shift') ? ' is-invalid' : '' }}"
            name="{{ $name }}" {{ $disabled == true ? 'disabled' : '' }} id="{{ $id }}">
            <option data-display="@lang('admin.select_shift') @if ($required == true) * @endif" value="">
                @lang('admin.select_shift')
                @if ($required == true)
                    *
                @endif
            </option>
            @foreach (shifts() as $shift)
                <option value="{{ $shift->id }}" {{ isset($editData) && $shift_id == $shift->id ? 'selected' : '' }}>
                    {{ $shift->name }}
                </option>
            @endforeach
        </select>

        @if ($errors->has('shift'))
            <span class="text-danger">
                {{ $errors->first('shift') }}
            </span>
        @endif
    </div>
</div>
<input type="text" value="{{asset('public/backEnd/img/demo_wait.gif')}}" hidden id="class_loader">
{{-- @push('scripts')
    <script>
        $(document).ready(function() {
            //retrive shifts as selected class
       
            

            $("#select_shift_dropdown").on("change", function() {

                $("#select_section_member").on("change", function() {
                    var url = $("#url").val();
                    var i = 0;
                    var select_class = $("#select_class").val();
                    if (!select_class) {
                        var select_class = $("#class_library_member").val();
                    }
                    var formData = {
                        section: $(this).val(),
                        class: select_class,
                    };
                    console.log(formData);
                    // get section for student
                    $.ajax({
                        type: "GET",
                        data: formData,
                        dataType: "json",
                        url: url + "/" + "ajaxSelectStudent",

                        beforeSend: function() {
                            $('#select_student_loader').addClass('pre_loader');
                            $('#select_student_loader').removeClass('loader');
                        },

                        success: function(data) {
                            console.log(data);
                            $.each(data, function(i, item) {
                                if (item.length) {
                                    $("#select_student").find("option").not(
                                        ":first").remove();
                                    $("#select_student_div ul").find("li").not(
                                        ":first").remove();

                                    $.each(item, function(i, student) {
                                        $("#select_student").append(
                                            $("<option>", {
                                                value: student
                                                    .user_id,
                                                text: student
                                                    .full_name,
                                            })
                                        );

                                        $("#select_student_div ul")
                                            .append(
                                                "<li data-value='" +
                                                student.user_id +
                                                "' class='option'>" +
                                                student.full_name +
                                                "</li>"
                                            );
                                    });
                                } else {
                                    $("#select_student_div .current").html(
                                        jsLang('select_student') + " *");
                                    $("#select_student").find("option").not(
                                        ":first").remove();
                                    $("#select_student_div ul").find("li").not(
                                        ":first").remove();
                                }
                            });
                        },
                        error: function(data) {
                            console.log("Error:", data);
                        },
                        complete: function() {
                            i--;
                            if (i <= 0) {
                                $('#select_student_loader').removeClass('pre_loader');
                                $('#select_student_loader').addClass('loader');
                            }
                        }
                    });
                });




                // let studentSelect = $("#select_student");
                // if (studentSelect.length > 0) {
                //     addStudentToList($(this).val());  
                // }

            });
        });

        function addStudentToList(shift_id) {
            var url = $("#url").val();
            var i = 0;
            var select_class = $("#select_class").val();
            var select_section = $("#select_section_member").val();
            if (!select_class) {
                var select_class = $("#class_library_member").val();
            }
            var formData = {
                shift: shift_id,
                class: select_class,
                section: select_section,

            };
            console.log(formData);
            // get section for student
            $.ajax({
                type: "GET",
                data: formData,
                dataType: "json",
                url: url + "/" + "ajaxStudentSectionShift",

                beforeSend: function() {
                    $('#select_student_loader').addClass('pre_loader');
                    $('#select_student_loader').removeClass('loader');
                },

                success: function(data) {
                    console.log(data);
                    $.each(data, function(i, item) {
                        if (item.length) {
                            $("#select_student").find("option").not(":first").remove();
                            $("#select_student_div ul").find("li").not(":first").remove();

                            $.each(item, function(i, student) {
                                $("#select_student").append(
                                    $("<option>", {
                                        value: student.user_id,
                                        text: student.full_name,
                                    })
                                );

                                $("#select_student_div ul").append(
                                    "<li data-value='" +
                                    student.user_id +
                                    "' class='option'>" +
                                    student.full_name +
                                    "</li>"
                                );
                            });
                        } else {
                            $("#select_student_div .current").html(jsLang('select_student') + " *");
                            $("#select_student").find("option").not(":first").remove();
                            $("#select_student_div ul").find("li").not(":first").remove();
                        }
                    });
                },
                error: function(data) {
                    console.log("Error:", data);
                },
                complete: function() {
                    i--;
                    if (i <= 0) {
                        $('#select_student_loader').removeClass('pre_loader');
                        $('#select_student_loader').addClass('loader');
                    }
                }
            });
        }
    </script>
@endpush --}}
