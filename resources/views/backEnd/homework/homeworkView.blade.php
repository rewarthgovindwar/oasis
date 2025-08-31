<div class="container-fluid mt-30">
    <div class="student-details">
        <div class="student-meta-box">
            <div class="single-meta">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="homework_info">
                            <div class="col-lg-12">
                                <div class="row">

                                    <h4 class="stu-sub-head">@lang('homework.evaluation_summary')</h4>

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
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->homework_date != '' ? dateConvert(@$homeworkDetails->homework_date) : '' }}
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
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->submission_date != '' ? dateConvert(@$homeworkDetails->submission_date) : '' }}
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
                                            @if (@$homeworkDetails->evaluation_date != '')
                                                {{ @$homeworkDetails->evaluation_date != '' ? dateConvert(@$homeworkDetails->evaluation_date) : '' }}
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
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->users->full_name }}
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
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->evaluatedBy->full_name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-meta">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="value text-left">
                                            @if (moduleStatusCheck('University'))
                                                @lang('university::un.semester_label')
                                            @else
                                                @lang('common.class')
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <div class="name">
                                            @if (moduleStatusCheck('University'))
                                                {{ @$homeworkDetails->semesterLabel->name }}
                                                ({{ @$homeworkDetails->unAcademic->name }})
                                            @else
                                                {{ @$homeworkDetails->classes->class_name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-meta">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="value text-left">
                                            @if (moduleStatusCheck('University'))
                                                @lang('university::un.department')
                                            @else
                                                @lang('common.section')
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="name">
                                            @if (moduleStatusCheck('University'))
                                                {{ @$homeworkDetails->unDepartment->name }}
                                            @else
                                                {{ @$homeworkDetails->sections->section_name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(shiftEnable())
                            <div class="single-meta">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="value text-left">
                                            @lang('common.shift')
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="name">
                                            {{ @$homeworkDetails->shift->name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="single-meta">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="value text-left">
                                            @lang('common.subject')
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="name">
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->subjects->subject_name }}
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
                                            {{ @$homeworkDetails->marks }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="single-meta">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="value text-left">
                                            @lang('homework.obtained_marks')
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="name">
                                            {{ @$student_result != '' ? @$student_result->marks : '' }}
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
                                                $raw_files = $homeworkDetails->file;
                                                $files = is_array($raw_files) ? $raw_files : json_decode($raw_files, true);
                                                if (!is_array($files)) {
                                                    $files = [$raw_files];
                                                }

                                                $previewable_exts = ['jpg', 'jpeg', 'heic', 'png', 'mp4', 'mp3', 'mov', 'pdf'];
                                                $file_extensions = [];

                                                foreach ($files as $file) {
                                                    $file = trim($file, '"');
                                                    $file_extensions[] = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                }
                                                $has_previewable = false;
                                                foreach ($file_extensions as $ext) {
                                                    if (in_array($ext, $previewable_exts)) {
                                                        $has_previewable = true;
                                                        break;
                                                    }
                                                }
                                            @endphp

                                            @if (!empty($files))
                                                @if ($has_previewable)
                                                    <a class="viewSubmitedHomework download_link" id="show_files" href="#">
                                                        <span class="pl ti-download"></span>
                                                    </a>
                                                @else
                                                    @foreach ($files as $file)
                                                        <a href="{{ asset($file) }}" download class="download_link">
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
                                            @if (isset($homeworkDetails))
                                                {{ @$homeworkDetails->description }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="file-preview" style="display: none">
                            @php
                                $raw_file = $homeworkDetails->file;
                                $files = is_array($raw_file) ? $raw_file : json_decode($raw_file, true);
                                if (!is_array($files)) {
                                    $files = [$raw_file];
                                }

                                $preview_files = ['jpg', 'jpeg', 'png', 'heic', 'mp4', 'mov', 'mp3', 'pdf'];
                            @endphp

                            @foreach ($files as $file)
                                @php
                                    $file = trim($file, '"');
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $file_url = asset('public/uploads/homeworkcontent/' . $file);
                                    $set_filename = time() . '_' . basename($file);
                                @endphp

                                @if (in_array($ext, ['jpg', 'jpeg', 'png', 'heic']))
                                    <img class="img-responsive mt-20" style="width: 100%; height:auto" src="{{ $file_url }}">
                                @elseif (in_array($ext, ['mp4', 'mov']))
                                    <video class="mt-20 video_play" width="100%" controls>
                                        <source src="{{ $file_url }}" type="video/{{ $ext }}">
                                        Your browser does not support HTML video.
                                    </video>
                                @elseif ($ext === 'mp3')
                                    <audio class="mt-20 audio_play" controls style="width: 100%">
                                        <source src="{{ $file_url }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                @elseif ($ext === 'pdf')
                                    <object data="{{ $file_url }}" type="application/pdf" width="100%" height="800">
                                        <iframe src="{{ $file_url }}" width="100%" height="800">
                                            <p>This browser does not support PDFs!</p>
                                        </iframe>
                                    </object>
                                @else
                                    <div class="alert alert-warning">
                                        {{ $ext }} file is not previewable.
                                    </div>
                                @endif

                                <div class="mt-40 d-flex justify-content-between">
                                    <a class="primary-btn tr-bg" download="{{ $set_filename }}" href="{{ $file_url }}">
                                        <span class="pl ti-download">@lang('common.download')</span>
                                    </a>
                                </div>
                                <hr>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
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
        jQuery('.admin_view_modal').on('hidden.bs.modal', function(e) {
            $('.video_play').get(0).play();
            $('.video_play').trigger('pause');

            $('.audio_play').get(0).play();
            $('.audio_play').trigger('pause');
        });
    </script>
@endpush
