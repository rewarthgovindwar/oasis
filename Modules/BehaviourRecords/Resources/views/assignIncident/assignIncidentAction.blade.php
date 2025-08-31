
<x-drop-down>
    @if(userPermission('behaviour_records.assign_incident_save'))
         <a onclick="assignViewIncident({{ $row->id }})" data-record="{{ $row->studentRecord->id }}"
        href="javascript:void(0)"   class="dropdown-item record{{ $row->id }}">@lang('behaviourRecords.assign_view')</a>
    @endif
</x-drop-down>
