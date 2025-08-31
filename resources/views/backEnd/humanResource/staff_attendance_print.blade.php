<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if(userRtlLtl()==1) dir="rtl" class="rtl" @endif>

<head>
    <title>@lang('hr.staff_attendance') </title>
    <meta charset="utf-8">
</head>

<style>
table,
th,
tr,
td {
    font-size: 11px !important;
    padding: 0px !important;
    text-align: center !important;
}

#attendance.th,
#attendance.tr,
#attendance.td {
    font-size: 10px !important;
    padding: 0px !important;
    text-align: center !important;
    border: 1px solid #ddd;
    vertical-align: middle !important;
}

#attendance th {
    background: #ddd;
    text-align: center;
}

#attendance {
    border: 1px solid black;
    border-collapse: collapse;
}

#attendance tr {
    border: 1px solid black;
    border-collapse: collapse;
}

#attendance th {
    border: 1px solid black;
    border-collapse: collapse;
    text-align: center !important;
    font-size: 11px;
}

#attendance td {
    border: 1px solid black;
    border-collapse: collapse;
    text-align: center;
    font-size: 10px;
}
</style>

<script>
var is_chrome = function() {
    return Boolean(window.chrome);
}
if (is_chrome) {
    window.print();
    // setTimeout(function(){window.close();}, 10000); 
    //give them 10 seconds to print, then close
} else {
    window.print();
}
</script>

<body onLoad="loadHandler();" style="font-family: 'dejavu sans', sans-serif;">
    <div class="container-fluid">

        <table cellspacing="0" width="100%">
            <tr>
                <td>
                    <img class="logo-img" style="max-width: 120px" src="{{ url('/')}}/{{generalSetting()->logo }}"
                        alt="">
                </td>
                <td>
                    <h3 style="font-size:22px !important" class="text-white">
                        {{isset(generalSetting()->school_name)?generalSetting()->school_name:'Infix School Management ERP'}}
                    </h3>
                    <p style="font-size:18px !important" class="text-white mb-0">
                        {{isset(generalSetting()->address)?generalSetting()->address:'Infix School Address'}} </p>
                </td>
                <td style="text-aligh:center">
                    <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">
                        @lang('common.role'): {{ $role->name}} </p>
                    <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">
                        @lang('hr.month'): {{ date("F", strtotime('00-'.$month.'-01')) }} </p>
                    <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">
                        @lang('common.year'): {{ $year }} </p>

                </td>
            </tr>
        </table>


        <h3 style="text-align:center">@lang('hr.staff_attendance_report')</h3>

        <table id="attendance" style="width: 100%; table-layout: fixed">
            <tr>
                <th width="7%">@lang('hr.staff_name')</th>
                <th width="7%">@lang('hr.staff_no')</th>
                <th>@lang('hr.P')</th>
                <th>@lang('hr.L')</th>
                <th>@lang('hr.A')</th>
                <th>@lang('hr.H')</th>
                <th>@lang('hr.F')</th>
                <th width="5%">%</th>
                @for($i = 1; $i <= $days; $i++)
                    <th class="{{ $i <= 18 ? 'all' : 'none' }}">
                        {{ $i }} <br>
                        {{ date('D', strtotime("$year-$month-$i")) }}
                    </th>
                @endfor
            </tr>
            @foreach($attendances as $staff_id => $records)
                @php
                    $staff = $staffs->where('id', $staff_id)->first();
                    $attendanceCounts = ['P' => 0, 'L' => 0, 'A' => 0, 'H' => 0, 'F' => 0];
                    $total_attendance = count($records);
                    $count_absent = 0;
                @endphp
                @foreach($records as $record)
                    @php $attendanceCounts[$record->attendence_type]++; @endphp
                    @if($record->attendence_type == 'A')
                        @php $count_absent++; @endphp
                    @endif
                @endforeach
                <tr>
                    <td>{{ $staff->full_name }}</td>
                    <td>{{ $staff->staff_no }}</td>
                    <td>{{ $attendanceCounts['P'] }}</td>
                    <td>{{ $attendanceCounts['L'] }}</td>
                    <td>{{ $attendanceCounts['A'] }}</td>
                    <td>{{ $attendanceCounts['H'] }}</td>
                    <td>{{ $attendanceCounts['F'] }}</td>
                    <td>
                        {{ $count_absent == 0 ? '100%' : number_format((($total_attendance - $count_absent) / $total_attendance) * 100, 2) . '%' }}
                    </td>
                    @for($i = 1; $i <= $days; $i++)
                        @php $date = "$year-$month-$i"; @endphp
                        <td class="{{ $i <= 18 ? 'all' : 'none' }}">
                            {{ optional($records->where('attendence_date', $date)->first())->attendence_type ?? trans('hr.A') }}
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>


</body>

</html>