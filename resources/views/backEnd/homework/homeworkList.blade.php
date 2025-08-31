@extends('backEnd.master')
@section('title')
    @lang('homework.homework_list')
@endsection
@section('mainContent')
    @php
        $DATE_FORMAT = systemDateFormat();
    @endphp
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('homework.homework_list')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('homework.home_work')</a>
                    <a href="#">@lang('homework.homework_list')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
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
                            <div class="col-lg-4 text-md-right text-left col-md-6 mb-30-lg">
                                <a href="{{ route('add-homeworks') }}" class="primary-btn small fix-gr-bg">
                                    <span class="ti-plus pr-2"></span>
                                    @lang('homework.add_homework')
                                </a>
                            </div>
                        </div>
                        {{ html()->form('POST', route('homework-list-search'))->attributes([
                                'class' => 'form-horizontal',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                            ])->open() }}
                        <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                        @if (moduleStatusCheck('University'))
                            <div class="row">
                                @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    ['subject' => true]
                                )
                            </div>
                        @else
                            <div class="row">
                                @include('backEnd.common.search_criteria', [
                                    'div' => shiftEnable() ? 'col-lg-3' : 'col-lg-4',
                                    'required' => ['class'],
                                    'visiable' => ['subject', 'shift', 'class', 'section'],
                                    'subject' => true,
                                    'class_name' => 'class_id',
                                    'section_name' => 'section_id',
                                    'subject_name' => 'subject_id',
                                    'selected' => [
                                        'shift_id' => @$shift,
                                        'section_id' => @$section,
                                        'class_id' => @$class,
                                        'subject_id' => @$subject
                                    ],
                                ])
                            </div>
                        @endif

                        <input type="hidden" name="class" id="class" value="{{ @$class }}">
                        <input type="hidden" name="subject" id="subject" value="{{ @$subject }}">
                        <input type="hidden" name="section" id="section" value="{{ @$section }}">
                        <input type="hidden" name="shift" id="shift" value="{{ @$shift }}">

                        <div class="row">
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

            <div class="row mt-40">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-4 no-gutters">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('homework.homework_list')</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <x-table>
                                    <table id="table_id" class="table data-table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                @if (moduleStatusCheck('University'))
                                                    <th>@lang('university::un.semester_label')</th>
                                                    <th>@lang('university::un.department')</th>
                                                @else
                                                    <th>@lang('common.class')</th>
                                                    <th>@lang('common.section')</th>
                                                    @if(shiftEnable())
                                                    <th>@lang('common.shift')</th>
                                                    @endif
                                                @endif

                                                <th>@lang('homework.subject')</th>
                                                <th>@lang('homework.marks')</th>
                                                <th>@lang('homework.home_work_date')</th>
                                                <th>@lang('homework.submission_date')</th>
                                                <th>@lang('homework.evaluation_date')</th>
                                                <th>@lang('homework.created_by')</th>
                                                <th>@lang('common.action')</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </x-table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- delete homework  --}}
    <div class="modal fade admin-query" id="deleteHomeWorkModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('common.delete')</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                {{ html()->form('POST', route('homework-delete'))->attribute('enctype', 'multipart/form-data')->open() }}
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="text-center">
                        <h4>@lang('common.are_you_sure_to_delete')</h4>
                    </div>
                    <div class="mt-40 d-flex justify-content-between">
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>

                        <button class="primary-btn fix-gr-bg" type="submit">@lang('common.delete')</button>
                    </div>
                </div>
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>

@endsection
@include('backEnd.partials.data_table_js')
@include('backEnd.partials.server_side_datatable')
@push('script')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                "ajax": $.fn.dataTable.pipeline({
                    url: "{{ route('homework-list-ajax') }}",
                    data: {
                        class: $("#class").val(),
                        subject: $("#subject").val(),
                        section: $("#section").val(),
                        @if(shiftEnable())
                        section: $("#shift").val(),
                        @endif
                    },
                    pages: "{{ generalSetting()->ss_page_load }}" // number of pages to cache

                }),
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id'
                    },
                    @if (moduleStatusCheck('University'))
                        {
                            data: 'un_session.name',
                            name: 'sections.name'
                        }, {
                            data: 'un_semester.name',
                            name: 'un_semester.name'
                        },
                    @else
                        {
                            data: 'classes.class_name',
                            name: 'classes.class_name'
                        }, {
                            data: 'sections.section_name',
                            name: 'sections.section_name'
                        },
                        @if(shiftEnable())
                        {
                            data: 'shift.name',
                            name: 'shift.name'
                        },
                        @endif
                    @endif {
                        data: 'subjects.subject_name',
                        name: 'subjects.subject_name'
                    },
                    {
                        data: 'marks',
                        name: 'marks'
                    },
                    {
                        data: 'homework_date',
                        name: 'homework_date'
                    },
                    {
                        data: 'submission_date',
                        name: 'submission_date'
                    },
                    {
                        data: 'evaluation_date',
                        name: 'evaluation_date'
                    },
                    {
                        data: 'users.full_name',
                        name: 'users.full_name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: true
                    },
                ],
                bLengthChange: false,
                bDestroy: true,
                language: {
                    search: "<i class='ti-search'></i>",
                    searchPlaceholder: window.jsLang('quick_search'),
                    paginate: {
                        next: "<i class='ti-arrow-right'></i>",
                        previous: "<i class='ti-arrow-left'></i>",
                    },
                },
                dom: "Bfrtip",
                buttons: [{
                        extend: "copyHtml5",
                        text: '<i class="fa fa-files-o"></i>',
                        title: $("#logo_title").val(),
                        titleAttr: window.jsLang('copy_table'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "excelHtml5",
                        text: '<i class="fa fa-file-excel-o"></i>',
                        titleAttr: window.jsLang('export_to_excel'),
                        title: $("#logo_title").val(),
                        margin: [10, 10, 10, 0],
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "csvHtml5",
                        text: '<i class="fa fa-file-text-o"></i>',
                        titleAttr: window.jsLang('export_to_csv'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "pdfHtml5",
                        text: '<i class="fa fa-file-pdf-o"></i>',
                        title: $("#logo_title").val(),
                        titleAttr: window.jsLang('export_to_pdf'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                        orientation: "landscape",
                        pageSize: "A4",
                        margin: [0, 0, 0, 12],
                        alignment: "center",
                        header: true,
                        customize: function(doc) {
                            doc.content[1].margin = [100, 0, 100, 0]; //left, top, right, bottom
                            doc.content.splice(1, 0, {
                                margin: [0, 0, 0, 12],
                                alignment: "center",
                                image: "data:image/png;base64," + $("#logo_img").val(),
                            });
                            doc.defaultStyle = {
                                font: 'DejaVuSans'
                            }
                        },
                    },
                    {
                        extend: "print",
                        text: '<i class="fa fa-print"></i>',
                        titleAttr: window.jsLang('print'),
                        title: $("#logo_title").val(),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "colvis",
                        text: '<i class="fa fa-columns"></i>',
                        postfixButtons: ["colvisRestore"],
                    },
                ],
                columnDefs: [{
                    visible: false,
                }, ],
                responsive: true,
            });
        });
    </script>
    <script>
        function deleteHomeWork(id) {
            var modal = $('#deleteHomeWorkModal');
            modal.find('input[name=id]').val(id)
            modal.modal('show');
        }
    </script>
@endpush
