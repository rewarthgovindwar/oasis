<div class="container-fluid mt-30">
    <div class="student-details">
        <div class="student-meta-box">
            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                    <div class="homework_info">
                        <div class="col-lg-12">
                            <div class="row">
 
                             <h4 class="stu-sub-head">@lang('homework.home_work_summary')</h4>
 
                         </div>
                     </div> 
                    <div class="single-meta">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="value text-left">
                                    @lang('homework.homework_date')
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="name">
                                    @if(isset($homeworkDetails))        
                                        {{@$homeworkDetails->homework_date != ""? dateConvert(@$homeworkDetails->homework_date):''}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="single-meta">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="value text-left">
                                    @lang('homework.submission_date')
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="name">
                                    @if(isset($homeworkDetails))            
                                        {{@$homeworkDetails->submission_date != ""? dateConvert(@$homeworkDetails->submission_date):''}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="single-meta">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="value text-left">
                                    @lang('homework.evaluation_date') 
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="name">
                                    @if(@$homeworkDetails->evaluation_date != "")     
                                        {{@$homeworkDetails->evaluation_date != ""? dateConvert(@$homeworkDetails->evaluation_date):''}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="single-meta">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="value text-left">
                                    @lang('homework.created_by')
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="name">
                                   @if(isset($homeworkDetails))
                                   {{@$homeworkDetails->users->full_name}}
                                   @endif
                               </div>
                           </div>
                       </div>
                   </div>

                   <div class="single-meta">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="value text-left">
                                @lang('homework.evaluated_by')
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="name">
                                @if(isset($homeworkDetails))
                                {{@$homeworkDetails->evaluatedBy->full_name}}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="single-meta">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="value text-left">
                                @if(moduleStatusCheck('University'))
                                    @lang('university::un.semester_label') 
                                @else 
                                    @lang('common.class') 
                                @endif 
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="name">
                                @if(moduleStatusCheck('University'))
                                {{@$homeworkDetails->semesterLabel->name}} ({{@$homeworkDetails->unAcademic->name}})
                                @else
                               {{@$homeworkDetails->classes->class_name}}
                               @endif
                           </div>
                       </div>
                   </div>
               </div>

               <div class="single-meta">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="value text-left">
                                @if(moduleStatusCheck('University'))
                                        @lang('university::un.department') 
                                @else
                                    @lang('common.section')
                                @endif 
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="name">
                                @if(moduleStatusCheck('University'))
                                {{@$homeworkDetails->unDepartment->name}}
                                @else
                                {{@$homeworkDetails->sections->section_name}} @if($homeworkDetails?->shift) [{{@$homeworkDetails?->shift?->shift_name}}] @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="value text-left">
                            @lang('common.subject') 
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="name">
                            @if(isset($homeworkDetails))
                            {{@$homeworkDetails->subjects->subject_name}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="value text-left">
                            @lang('exam.marks') 
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="name">
                            {{@$homeworkDetails->marks}}
                        </div>
                    </div>
                </div>
            </div>
            @php
            if (Auth::user()->role_id == 2) {
               $student_detail = App\SmStudent::where('user_id', Auth::user()->id)->first();
            } else {
                $parent = Auth::user()?->parent;
                $student_detail = App\SmStudent::where('parent_id', $parent?->id)->first();
            }
            
             if($student_detail){
                $student_result = $student_detail->homeworks->where('homework_id', $homeworkDetails?->id)->first();
             }
             
            @endphp
            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="value text-left">
                            @lang('homework.obtained_marks') 
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="name">
                            {{@$student_result != ""? @$student_result->marks:''}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="value text-left">
                            @lang('common.attach_file') 
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="name">
                            @php
                                $files = is_array($homeworkDetails->file) ? $homeworkDetails->file : json_decode($homeworkDetails->file, true);
                                if (!is_array($files)) {
                                    $files = [$homeworkDetails->file];
                                }
                                $previewable = ['jpg', 'jpeg', 'heic', 'png', 'mp4', 'mp3', 'mov', 'pdf'];

                                $files_ext = [];
                                foreach ($files as $f) {
                                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                    $files_ext[] = $ext;
                                }
                                $has_previewable = !empty(array_intersect($files_ext, $previewable));
                            @endphp

                            @if (!empty($files))
                                @if ($has_previewable)
                                    <a class="dropdown-item viewSubmitedHomework" id="show_files" href="#">
                                        <span class="pl ti-download"></span>
                                    </a>
                                @else
                                    @foreach ($files as $file)
                                        <a href="{{ asset($file) }}" download>
                                            @lang('common.download') <span class="pl ti-download"></span>
                                        </a>
                                    @endforeach
                                @endif
                            @endif

                        </div>
                    </div>
                    
                </div>
            </div>

            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="value text-left">
                            @lang('common.description')  
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="name">
                            @if(isset($homeworkDetails))
                            {{@$homeworkDetails->description}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="file-preview" style="display: none">
                @php
                    $files = is_array($homeworkDetails->file)
                        ? $homeworkDetails->file
                        : json_decode($homeworkDetails->file, true);
            
                    $previewable = ['jpg', 'jpeg', 'png', 'heic', 'mp4', 'mov', 'mp3', 'pdf'];
                @endphp
            
                @foreach ($files as $file)
                    @php
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $fileUrl = asset('public/uploads/homeworkcontent/' . $file);
                    @endphp
            
                    <div class="mt-3">
                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'heic']))
                            <img class="img-responsive mt-20" style="width: 100%; height:auto" src="{{ $fileUrl }}">
                        @elseif (in_array($ext, ['mp4', 'mov']))
                            <video class="mt-20 video_play" width="100%" controls>
                                <source src="{{ $fileUrl }}" type="video/mp4">
                                Your browser does not support HTML video.
                            </video>
                        @elseif ($ext === 'mp3')
                            <audio class="mt-20 audio_play" controls style="width: 100%">
                                <source src="{{ $fileUrl }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        @elseif ($ext === 'pdf')
                            <object data="{{ $fileUrl }}" type="application/pdf" width="100%" height="800">
                                <iframe src="{{ $fileUrl }}" width="100%" height="800">
                                    <p>This browser does not support PDFs.</p>
                                </iframe>
                            </object>
                        @else
                            <div class="alert alert-warning">{{ strtoupper($ext) }} file not previewable.</div>
                        @endif
            
                        <div class="mt-4 d-flex justify-content-between">
                            @php
                                $downloadName = time() . '_' . basename($file);
                            @endphp
                            <a class="primary-btn tr-bg" download="{{ $downloadName }}" href="{{ $fileUrl }}">
                                <span class="pl ti-download"></span> @lang('common.download')
                            </a>
                        </div>
                        <hr />
                    </div>
                @endforeach
            </div>
            
            <hr>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>


<script type="text/javascript">


    // for evaluation date
    $('#evaluation_date_icon').on('click', function() {
        $('#evaluation_date').focus();
    });
    $('#show_files').on('click', function() {
        $('.file-preview').show();
        $('.homework_info').hide();
    });

    $('.primary_input_field.date').datepicker({
        autoclose: true
    });

    $('.primary_input_field.date').on('changeDate', function(ev) {
        $(this).focus();
    });

</script>
@push('script')
<script type="text/javascript">
    jQuery('.admin_view_modal').on('hidden.bs.modal', function (e) {
    //   console.log('closed');
    //   jQuery('#viewSubmitedHomework video').attr("src", jQuery("#viewSubmitedHomework  video").attr("src"));
      $('.video_play').get(0).play();
      $('.video_play').trigger('pause');
      
      $('.audio_play').get(0).play();
      $('.audio_play').trigger('pause');
    });
    </script>
@endpush
