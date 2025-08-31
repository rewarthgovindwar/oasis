@push('css')
    <style>
        @media (max-width: 767px) {
            .dataTables_filter label {
                top: -25px !important;
                width: 100%;
            }
        }

        @media screen and (max-width: 640px) {
            div.dt-buttons {
                display: none;
            }

            .dataTables_filter label {
                top: -60px !important;
                width: 100%;
                float: right;
            }

            /* .main-title{
            margin-bottom: 40px
        } */
        }
    </style>
@endpush

@extends('backEnd.master')
@section('title')
    @lang('academics.assign_subject')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('academics.assign_subject')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('academics.academics')</a>
                    <a href="#">@lang('academics.assign_subject')</a>
                </div>
            </div>
        </div>
    </section>

    <div id="ajaxSpinnerContainer">
        {{-- <img src="{{asset('public/uploads/settings')}}/ajax-loader.gif" id="ajaxSpinnerImage" title="loading..." /> --}}
    </div>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="main-title">
                                    <h3 class="mb-15 ">@lang('common.select_criteria')</h3>
                                </div>
                            </div>
                            @if (userPermission('assign-subject-store'))
                                <div class="col-lg-6 text-left text-sm-right col-md-6 col-sm-6">
                                    <a href="{{ route('assign_subject_create') }}" class="primary-btn small fix-gr-bg">
                                        <span class="ti-plus pr-2"></span>
                                        @lang('academics.assign_subject')
                                    </a>
                                </div>
                            @endif
                        </div>
                        {{ html()->form('POST', route('assign-subject'))->attribute('class', 'form-horizontal')->attribute('enctype', 'multipart/form-data')->attribute('files', true)->attribute('id', 'search_student')->open() }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                            @if (moduleStatusCheck('University'))
                            @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    [
                                        'required' => ['USN', 'UD', 'UA', 'US', 'USL'],
                                        'div' => 'col-lg-3',
                                        'hide' => ['USUB'],
                                    ]
                                )
                            @else    
                                @include('backEnd.shift.shift_class_section_include', [
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
                <div class="white-box mt-40">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="main-title">
                                <h3 class="mb-15">@lang('academics.assign_subject') </h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <x-table>
                                <table id="table_id" class="table" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>@lang('academics.subject')</th>
                                            <th>@lang('common.teacher')</th>
                                            @if (@generalSetting()->result_type == 'mark')
                                                <th>@lang('academics.pass_mark')</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php $i = 4; @endphp
                                        @foreach ($assign_subjects as $assign_subject)
                                            <tr>
                                                <td>{{ @$assign_subject->subject != '' ? @$assign_subject->subject->subject_name : '' }}
                                                </td>
                                                <td>
                                                    @if (@$assign_subject->teacher != '')
                                                        {{ @$assign_subject->teacher->full_name }}
                                                    @else
                                                        @lang('academics.not_assigned_yet')
                                                    @endif
                                                </td>
                                                @if (@generalSetting()->result_type == 'mark')
                                                    <td>{{ @$assign_subject->pass_mark }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
@include('backEnd.partials.data_table_js')
