@extends('backEnd.master')
@section('mainContent')
    <style>
        table.meritList{
            width: 100%;
        }
        table.meritList th{
            padding: 2px;
            text-transform: capitalize !important;
            font-size: 11px !important;
            border: 1px solid rgba(0, 0, 0, .1) !important;
            text-align: center !important;
        } 
        table.meritList td{
            padding: 2px;
            font-size: 11px !important;
            border: 1px solid rgba(0, 0, 0, .1) !important;
            text-align: center !important;
        }
        .single-report-admit table tr td {
            padding: 5px 5px !important;
        }
        .single-report-admit table tr th {
            padding: 5px 5px !important;
            vertical-align: middle;
        }
        .main-wrapper {
            display: block !important ;
        }
        #main-content {
            width: auto !important;
        }
    </style>
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('reports.merit_list_report') </h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                    <a href="#">@lang('reports.reports')</a>
                    <a href="#">@lang('reports.merit_list_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-8 col-md-6">
                    <div class="main-title">
                        <h3 class="mb-30">@lang('common.select_criteria') </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if(session()->has('message-success') != "")
                    @if(session()->has('message-success'))
                        <div class="alert alert-success">
                            {{ session()->get('message-success') }}
                        </div>
                    @endif
                @endif
                @if(session()->has('message-danger') != "")
                    @if(session()->has('message-danger'))
                        <div class="alert alert-danger">
                            {{ session()->get('message-danger') }}
                        </div>
                    @endif
                @endif
                <div class="white-box">
                    {{ html()->form('POST', route('merit_list_reports'))->attributes([
                        'class' => 'form-horizontal',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                        'id' => 'search_student',
                    ])->open() }}
                    <div class="row">
                        <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                        @if(moduleStatusCheck('University'))
                            <div class="col-lg-12">
                                <div class="row">
                                    @includeIf('university::common.session_faculty_depart_academic_semester_level',
                                    ['required' =>
                                        ['USN', 'UD', 'UA', 'US', 'USL'],'hide'=> ['USUB']
                                    ])

                                    <div class="col-lg-3 mt-15" id="select_exam_typ_subject_div">
                                        <label for=""> @lang('exam.select_exam') *</label>
                                        {{ html()->select('exam_type', ["" => __('exam.select_exam').'*'], null)->attributes([
                                            'class' => 'primary_select form-control' . ($errors->has('exam_type') ? ' is-invalid' : ''),
                                            'id' => 'select_exam_typ_subject',
                                        ]) }}
                                        
                                        <div class="pull-right loader loader_style" id="select_exam_type_loader">
                                            <img class="loader_img_style" src="{{asset('public/backEnd/img/demo_wait.gif')}}" alt="loader">
                                        </div>
                                        @if ($errors->has('exam_type'))
                                            <span class="text-danger custom-error-message" role="alert">
                                                {{ @$errors->first('exam_type') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-lg-4 mt-30-md">
                                <select class="primary_select form-control{{ $errors->has('exam') ? ' is-invalid' : '' }}" name="exam">
                                    <option data-display="@lang('common.select_exam')*" value="">@lang('common.select_exam') *</option>
                                    @foreach($exams as $exam)
                                        <option value="{{$exam->id}}" {{isset($exam_id)? ($exam_id == $exam->id? 'selected':''):''}}>{{$exam->title}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('exam'))
                                    <span class="text-danger invalid-select" role="alert">
                                            {{ $errors->first('exam') }}
                                        </span>
                                @endif
                            </div>
                            <div class="col-lg-4 mt-30-md">
                                <select class="primary_select form-control {{ $errors->has('class') ? ' is-invalid' : '' }}" id="select_class" name="class">
                                    <option data-display="@lang('common.select_class') *" value="">@lang('common.select_class') *</option>
                                    @foreach($classes as $class)
                                        <option value="{{$class->id}}" {{isset($class_id)? ($class_id == $class->id? 'selected':''):''}}>{{$class->class_name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('class'))
                                    <span class="text-danger invalid-select" role="alert">
                                            {{ $errors->first('class') }}
                                        </span>
                                @endif
                            </div>
                            <div class="col-lg-4 mt-30-md" id="select_section_div">
                                <select class="primary_select form-control{{ $errors->has('section') ? ' is-invalid' : '' }} select_section" id="select_section" name="section">
                                    <option data-display="@lang('common.select_section')*" value="">@lang('common.select_section') *</option>
                                </select>
                                @if ($errors->has('section'))
                                    <span class="text-danger invalid-select" role="alert">
                                            {{ $errors->first('section') }}
                                        </span>
                                @endif
                            </div>
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
    </section>

    @if(isset($allresult_data))


        <section class="student-details">
            <div class="container-fluid p-0">
                <div class="row mt-40">
                    <div class="col-lg-4 no-gutters">
                        <div class="main-title">
                            <h3 class="mb-30 mt-0">@lang('reports.merit_list_report')</h3>
                        </div>
                    </div>
                    <div class="col-lg-8 pull-right">
                        <a href="{{route('merit-list/print', [$InputExamId, $InputClassId, $InputSectionId])}}" class="primary-btn small fix-gr-bg pull-right" target="_blank"><i class="ti-printer"> </i> Print</a>

                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="white-box">
                            <div class="row justify-content-center">
                                <div class="col-lg-12">
                                    <div class="single-report-admit">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex">
                                                    <div>
                                                        <img class="logo-img" src="{{ generalSetting()->logo }}" alt="">
                                                    </div>
                                                    <div class="ml-30">
                                                        <h3 class="text-white"> {{isset($school_name)?$school_name:'Infix School Management ERP'}} </h3>
                                                        <p class="text-white mb-0"> {{isset(generalSetting()->address)?generalSetting()->address:'Infix School Address'}} </p>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="card-body">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h3>@lang('reports.order_of_merit_list')</h3>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <p class="mb-0">
                                                                        @lang('common.academic_year') : <span class="primary-color fw-500">{{generalSetting()->session_year}}</span>
                                                                    </p>
                                                                    <p class="mb-0">
                                                                        @lang('exam.exam') : <span class="primary-color fw-500">{{$exam_name}}</span>
                                                                    </p>
                                                                    <p class="mb-0">
                                                                        @lang('common.class') : <span class="primary-color fw-500">{{$class_name}}</span>
                                                                    </p>
                                                                    <p class="mb-0">
                                                                        @lang('common.section') : <span class="primary-color fw-500">{{$section->section_name}}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h3>@lang('common.subjects')</h3>
                                                            <div class="row">
                                                                <div class="col-md-12 w-100" style="columns: 2">
                                                                    @foreach($assign_subjects as $subject)
                                                                        <p class="mb-0">
                                                                            <span class="primary-color fw-500">{{$subject->subject->subject_name}}</span>
                                                                        </p>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>

                                                <div class="table-responsive">

                                                    <table class="w-100 mt-30 mb-20 table table-bordered meritList">
                                                        <thead>
                                                        <tr>
                                                            <th>Merit @lang('reports.position')</th>
                                                            <th>@lang('student.admission_no')</th>
                                                            <th>@lang('common.student')</th>
                                                            @foreach($subjectlist as $subject)
                                                                <th>{{$subject}}</th>
                                                            @endforeach

                                                            <th>@lang('exam.total_mark')</th>
                                                            <th>@lang('reports.average')</th>
                                                            {{--<th>@lang('exam.gpa')</th> --}}
                                                            <th>@lang('reports.remarks')</th>
                                                        </tr>
                                                        </thead>

                                                        <tbody>
                                                        @php $i=1; $subject_mark = []; $total_student_mark = 0; @endphp

                                                        @foreach($allresult_data as $key => $row)
                                                            <tr>
                                                                <td>{{$i}}</td>
                                                                <td>{{$row->admission_no}}</td>
                                                                <td style="text-align:left !important;" nowrap >{{$row->student_name}}</td>

                                                                @php $markslist = explode(',',$row->marks_string);@endphp
                                                                @if(!empty($markslist))
                                                                    @foreach($markslist as $mark)
                                                                        @php
                                                                            $subject_mark[]= $mark;
                                                                            $total_student_mark = $total_student_mark + $mark;
                                                                        @endphp
                                                                        <td>  {{!empty($mark)? round($mark, 2) :0}}</td>
                                                                    @endforeach

                                                                @endif



                                                                <td>{{round($total_student_mark, 2)}} </td>
                                                                <td>{{!empty($row->average_mark)?round($row->average_mark, 2):0}} @php $total_student_mark=0; @endphp </td>

                                                                {{-- <td>
                                                                        <?php
                                                                            if($row->result == 'F'){
                                                                                $gpaR = '0.00';
                                                                                echo $gpaR;
                                                                            }else{
                                                                                $total_grade_point = 0;
                                                                                $number_of_subject = count($subject_mark);
                                                                                foreach ($subject_mark as $mark) {
                                                                                    $grade_gpa = DB::table('sm_marks_grades')->where('percent_from','<=',$mark)->where('percent_upto','>=',$mark)->first();
                                                                                    $total_grade_point = $total_grade_point + $grade_gpa->gpa;
                                                                                }
                                                                                if($total_grade_point==0){
                                                                                    $gpaR = '0.00';
                                                                                    echo $gpaR;
                                                                                }else{
                                                                                    if($number_of_subject  == 0){
                                                                                        $gpaR = '0.00';
                                                                                        echo $gpaR;
                                                                                    }else{
                                                                                        $gpaR =  number_format((float)$total_grade_point/$number_of_subject, 2, '.', '');
                                                                                        echo $gpaR; 
                                                                                    }
                                                                                }
                                                                            }
                                                                        ?>


                                                                </td>--}}
                                                                @php
                                                                    $mark_grade = markGpa($row->average_mark);
                                                                @endphp
                                                                <td>
                                                                    {{ $mark_grade->description }}
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $i++;
                                                            @endphp
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    @endif


@endsection
