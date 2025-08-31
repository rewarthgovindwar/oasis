@php
    $school_config = schoolConfig();
    $isSchoolAdmin = Session::get('isSchoolAdmin');
@endphp
<!-- sidebar part here -->
<nav id="sidebar" class="sidebar">

    <div class="sidebar-header update_sidebar">
        @if (Auth::user()->role_id != 2 && Auth::user()->role_id != 3)
            @if (userPermission('dashboard'))
                @if (moduleStatusCheck('Saas') == true &&
                    Auth::user()->is_administrator == 'yes' &&
                    Session::get('isSchoolAdmin') == false &&
                    Auth::user()->role_id == 1)
                    <a href="{{ route('superadmin-dashboard') }}" id="superadmin-dashboard">
                @elseif (moduleStatusCheck('Saas') == true &&
                    moduleStatusCheck('SaasHr') == true &&
                    Auth::user()->is_administrator == 'yes' &&
                    Session::get('isSchoolAdmin') == false)
                    <a href="{{ route('superadmin-dashboard') }}" id="superadmin-dashboard">
                @else
                    <a href="{{ route('admin-dashboard') }}" id="admin-dashboard">
                @endif
            @else
                <a href="{{url('/')}}" id="admin-dashboard">
            @endif
        @else
            <a href="{{ url('/') }}" id="admin-dashboard">
        @endif
        @if (!is_null($school_config->logo))
            <img src="{{ asset($school_config->logo) }}" alt="logo">
        @else
            <img src="{{ asset('public/uploads/settings/logo.png') }}" alt="logo">
        @endif
        </a>
        <a id="close_sidebar" class="d-lg-none">
            <i class="ti-close"></i>
        </a>

    </div>
    @if (Auth::user()->is_saas == 0)
       
        <ul class="sidebar_menu list-unstyled" id="sidebar_menu">
            @if (moduleStatusCheck('Saas') == true &&
                Auth::user()->is_administrator == 'yes' &&
                Session::get('isSchoolAdmin') == false &&
                Auth::user()->role_id == 1)
                @include('saas::menu.Saas')

            @elseif(moduleStatusCheck('Saas') == true &&
                Auth::user()->is_administrator == 'yes' &&
                Session::get('isSchoolAdmin') == false &&
                moduleStatusCheck('SaasHr') == true)
                @include('saas::menu.Saas')
            @else
                @if(auth()->user()->role_id == 2)
                    @includeIf('backEnd.menu.student', ['paid_modules' => $paid_modules])
                @elseif(auth()->user()->role_id == 3)
                    @includeIf('backEnd.menu.parent', ['children' => $children, 'paid_modules' => $paid_modules])
                @else
                    
                    @includeIf('backEnd.menu.staff', ['paid_modules' => $paid_modules])
                @endif
            @endif
        </ul>
    @endif
</nav>
<!-- sidebar part end -->
@push('script')
    <script>
        $(document).ready(function(){
            var sections=[];
            $('.menu_seperator').each(function() { sections.push($(this).data('section')); });

            jQuery.each(sections, function(index, section) {
                if($('.'+section).length == 0) {
                    $('#seperator_'+section).addClass('d-none');
                }else{
                    $('#seperator_'+section).removeClass('d-none');
                }
            });
        })

    </script>
@endpush