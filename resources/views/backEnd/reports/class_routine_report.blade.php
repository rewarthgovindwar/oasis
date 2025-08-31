@extends('backEnd.master')
@section('title')
@lang('reports.class_routine_report')
@endsection
@section('mainContent')
<section class="sms-breadcrumb mb-20">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('reports.class_routine_report')</h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('reports.reports')</a>
                <a href="#">@lang('reports.class_routine_report')</a>
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
                            <div class="col-lg-8 col-md-6">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('common.select_criteria') </h3>
                                </div>
                            </div>
                        </div>

                        {{ html()->form('POST', route('class_routine_reports'))->attributes([
                            'class' => 'form-horizontal',
                            'files' => true,
                            'enctype' => 'multipart/form-data',
                            'id' => 'search_student',
                        ])->open() }}
                            <div class="row">
                                <input type="hidden" name="url" id="url" value="{{URL::to('/')}}">
                                @if(moduleStatusCheck('University'))
                                @includeIf('university::common.session_faculty_depart_academic_semester_level',['required' => ['USN','UF', 'UD', 'UA', 'US', 'USL', 'USEC'], 'hide' => ['USUB']])
                                @else
                                    @include('backEnd.common.search_criteria', [
                                        'div' => shiftEnable() ? 'col-lg-4' : 'col-lg-6',
                                        'required' => ['class','section'],
                                        'visiable' => ['shift','class', 'section'],
                                        'class_name' => 'class',
                                        'section_name' => 'section',
                                        'selected' => [
                                            'class_id' => @$class_id,
                                            'section_id' => @$section_id,
                                            'shift_id' => @$shift_id,
                                        ]
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

@if(isset($sm_routine_updates))
<section class="mt-20">
    <div class="container-fluid p-0">
        <div class="white-box mt-40">
            <div class="row">
                <div class="col-lg-4 no-gutters">
                    <div class="main-title">
                        <h3 class="mb-15">@lang('reports.class_routine')</h3>
                    </div>
                </div>
                <div class="col-lg-8 pull-right">
                    @if(moduleStatusCheck('University'))
                    <a href="{{route('university.academics.classRoutinePrint', [$un_semester_label_id, $un_section_id])}}" class="primary-btn small fix-gr-bg pull-right" target="_blank"><i class="ti-printer"> </i> @lang('reports.print')</a>
                    @else
                    <a href="{{route('classRoutinePrint', [$class_id, $section_id, shiftEnable() ? $shift_id : null])}}" class="primary-btn small fix-gr-bg pull-right" target="_blank"><i class="ti-printer"> </i> @lang('reports.print')</a>
                    @endif 
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                    <table id="default_table" class="table " cellspacing="0" width="100%">
                        <thead>
                           
                            <tr>
                               
                                @php
                                    $height= 0;
                                    $tr = [];
                                @endphp
                            @foreach($sm_weekends as $sm_weekend)
                            @php
                            $count = $sm_weekend->classRoutine()->where('class_id', $class_id)->where('section_id', $section_id)->count();
                            @endphp
                              
                                @if( $count >$height)
                                    @php
                                        $height = $count;
                                    @endphp
                                @endif
                
                                <th>{{@$sm_weekend->name}}</th>
                            @endforeach
                            </tr>
                        </thead>
    
                        @php
                        $used = [];
                        $tr=[];
            
                        @endphp
                        @foreach($sm_weekends as $sm_weekend)
                        @php
                        
                            $i = 0;
                        @endphp
                        @foreach($sm_weekend->classRoutine()->where('class_id', $class_id)->where('section_id', $section_id)->get() as $routine)
                        
                            @php
                            if(!in_array($routine->id, $used)){
    
                                if(moduleStatusCheck('University')){
                                    $tr[$i][$sm_weekend->name][$loop->index]['subject']= $routine->unSubject ? $routine->unSubject->subject_name :'';
                                    $tr[$i][$sm_weekend->name][$loop->index]['subject_code']= $routine->unSubject ? $routine->unSubject->subject_code :'';
                                }else{
                                    $tr[$i][$sm_weekend->name][$loop->index]['subject']= $routine->subject ? $routine->subject->subject_name :'';
                                    $tr[$i][$sm_weekend->name][$loop->index]['subject_code']= $routine->subject ? $routine->subject->subject_code :'';
                                }
    
                                $tr[$i][$sm_weekend->name][$loop->index]['class_room']= $routine->classRoom ? $routine->classRoom->room : '';
                                $tr[$i][$sm_weekend->name][$loop->index]['teacher']= $routine->teacherDetail ? $routine->teacherDetail->full_name :'';
                                $tr[$i][$sm_weekend->name][$loop->index]['start_time']=  $routine->start_time;
                                $tr[$i][$sm_weekend->name][$loop->index]['end_time']= $routine->end_time;
                                $tr[$i][$sm_weekend->name][$loop->index]['is_break']= $routine->is_break;
                                $used[] = $routine->id;
                            } 
                                 
                            @endphp
                        @endforeach
            
                        @php
                            
                            $i++;
                        @endphp
            
                        @endforeach
                        <tbody>
                       
                            @for($i = 0; $i < $height; $i++)
                            <tr>
                             @foreach($tr as $days)
                              @foreach($sm_weekends as $sm_weekend)
                                 <td>
                                     @php
                                          $classes=gv($days,$sm_weekend->name);
                                      @endphp
                                      @if($classes && gv($classes,$i))              
                                        @if($classes[$i]['is_break'])
                                       <strong > @lang('reports.break') </strong>
                                          
                                        <span class=""> ({{date('h:i A', strtotime(@$classes[$i]['start_time']))  }}  - {{date('h:i A', strtotime(@$classes[$i]['end_time']))  }})  <br> </span> 
                                         @else
                                             <span class=""> <strong>@lang('common.subject') :</strong>   {{ $classes[$i]['subject'] }} ({{ $classes[$i]['subject_code'] }}) <br>  </span>            
                                             @if ($classes[$i]['class_room'])
                                                 <span class=""> <strong>@lang('common.room') :</strong>     {{ $classes[$i]['class_room'] }}  <br>     </span>
                                             @endif    
                                             @if ($classes[$i]['teacher'])
                                             <strong>@lang('common.teacher') :</strong>   <span class=""> {{ $classes[$i]['teacher'] }}  <br> </span>
                                             @endif           
                         
                                             <span class=""> <strong>@lang('common.time') :</strong> {{date('h:i A', strtotime(@$classes[$i]['start_time']))  }}  - {{date('h:i A', strtotime(@$classes[$i]['end_time']))  }}  <br> </span> 
                                          @endif
                     
                                     @endif
                                     
                                 </td>
                                 @endforeach
                     
                       
                                         
                             @endforeach
                            </tr>
                     
                            @endfor
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endif



@endsection
