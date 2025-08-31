@extends('backEnd.master')
@section('title')
    @lang('fees.fees_forward')
@endsection
@section('mainContent')
    @php
        $setting = generalSetting();
        if (!empty($setting->currency_symbol)) {
            $currency = $setting->currency_symbol;
        } else {
            $currency = '$';
        }
    @endphp

    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('fees.fees_forward')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('fees.fees_collection')</a>
                    <a href="#">@lang('fees.fees_forward')</a>
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
                            <div class="col-lg-4 col-md-6">
                                <div class="main-title">
                                    <h3 class="mb-15">@lang('common.select_criteria')</h3>
                                </div>
                            </div>
                        </div>
                        {{ html()->form('POST', route('fees-forward-search'))->attributes([
                                'class' => 'form-horizontal',
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                                'id' => 'search_studentA',
                            ])->open() }}
                        <div class="row">
                            <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
                            @if (moduleStatusCheck('University'))
                                @includeIf(
                                    'university::common.session_faculty_depart_academic_semester_level',
                                    ['mt' => 'mt-30', 'hide' => ['USUB'], 'required' => ['USL', 'USEC']]
                                )
                            @else
                            @include('backEnd.shift.shift_class_section_include', [
                                'div' => shiftEnable() ? 'col-lg-4' : 'col-lg-6',
                                'mt' => 'mt-15',
                                'visiable' => ['shift', 'class', 'section'],
                                'required' => ['class', 'section'],
                                'title' => ['class', 'section','shift'],
                                'class_name' => 'class',
                                'section_name' => 'section',
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
            @if (isset($students))
                {{ html()->form('POST', route('fees-forward-store'))->attributes([
                        'class' => 'form-horizontal',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                    ])->open() }}
                <div class="row mt-40">
                    <div class="col-lg-12">
                        <div class="white-box">
                            <div class="row">
                                <div class="col-lg-6 no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-15">@lang('fees.previous_Session_Balance_Fees')</h3>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="update" value="{{ isset($update) ? 1 : '' }}">
                            <div class="row">
                                <div class="col-lg-12">
                                    <x-table>
                                        <table id="table_id" class="table" cellspacing="0" width="100%">
                                            <thead>
                                                @if (isset($update))
                                                    <tr>
                                                        <td colspan="6">
                                                            <div class="alert">
                                                                <h4 class="mb-0"> @lang('fees.previous_balance_can_only_update_now.') </h4>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <th width="15%">@lang('student.student_name')</th>
                                                    <th width="15%">@lang('student.admission_no')</th>
                                                    <th width="15%">@lang('student.roll_no')</th>
                                                    <th width="15%">@lang('student.father_name')</th>
                                                    <th width="15%">@lang('fees.balance')
                                                        ({{ generalSetting()->currency_symbol }})</th>
                                                    <th width="25%">@lang('fees.short_note')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($students as $student)
                                                    <tr>
                                                        <td>{{ $student->studentDetail->full_name }} <input type="hidden"
                                                                name="id[]"
                                                                value="{{ isset($update) ? @$student->studentDetail->forwardBalance->id : $student->student_id }}">
                                                        </td>
                                                        <td>{{ $student->studentDetail->admission_no }}</td>
                                                        <td>{{ $student->studentDetail->roll_no }}</td>

                                                        <td>{{ $student->studentDetail->parents != '' ? $student->studentDetail->parents->fathers_name : '' }}
                                                        </td>
                                                        <td>
                                                            <div class="primary_input">
                                                                <input oninput="numberCheckWithDot(this)" type="text"
                                                                    class="primary_input_field form-control" cols="0"
                                                                    rows="1"
                                                                    name="balance[{{ isset($update) ? @$student->studentDetail->forwardBalance->id : @$student->student_id }}]"
                                                                    maxlength="8"
                                                                    value="{{ isset($update) ? @$student->studentDetail->forwardBalance->balance : '' }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="primary_input">
                                                                <input type="text"
                                                                    class="primary_input_field form-control" cols="0"
                                                                    rows="1"
                                                                    name="notes[{{ isset($update) ? @$student->studentDetail->forwardBalance->id : $student->student_id }}]"
                                                                    value="{{ isset($update) ? @$student->studentDetail->forwardBalance->notes : 'Fees Carry Forward' }}">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="text-center">
                                                        <button type="submit" class="primary-btn fix-gr-bg mb-0 submit">
                                                            <span class="ti-save pr"></span>
                                                            @lang('common.save')
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </x-table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ html()->form()->close() }}
            @endif
        </div>
    </section>
@endsection
@include('backEnd.partials.data_table_js')
