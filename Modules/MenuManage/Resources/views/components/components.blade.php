<h4>{{ __('common.Menu List') }}</h4>
<div class="">
    @push('css')
        <link href="{{ asset('Modules/MenuManage/Resources/assets/css/jquery.nestable.min.css') }}" rel="stylesheet">
        <link href="{{ asset('Modules/MenuManage/Resources/assets/css/sidebar.css') }}" rel="stylesheet">
    @endpush
    @php
            $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InfixBiometrics','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InAppLiveClass'];
           
    @endphp

    <div class="row">
        <div class="col-xl-12 menu_item_div" id="itemDiv">
           
            @if (isset($sidebar_menus))
                    @foreach ($sidebar_menus as $sidebar_menu)                    
                           
                        <div class="closed_section" data-id="{{ $sidebar_menu->id }}"  data-parent_section="{{ $sidebar_menu->id }}">
                            <div id="accordion" class="dd">
                                <div class="section_nav">
                                    <h5>{{ $sidebar_menu->name }}</h5>
                                    <div class="setting_icons">
                                        <span class="edit-btn">
                                            <a class=" btn-modal" data-container="#commonModal" type="button"
                                                href="{{ route('sidebar-manager.section-edit-form', [$sidebar_menu->id, 'role_id' => @$sidebar_menu->id,'role_name' => $role_name]) }}">
                                                <i class="ti-pencil-alt"></i>
                                            </a>

                                        </span>
                                        <i class="ti-close delete_section" data-id="{{ $sidebar_menu->id }}"></i>
                                        <i class="ti-angle-up toggle_up_down"></i>
                                    </div>
                                </div>
                            </div>                                
                            @if ($sidebar_menu->childs->count())
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="dd menu-list used_menu" 
                                                    data-section="{{ $sidebar_menu->id }}">
                                                    <ol class="dd-list">
                                                        @foreach ($sidebar_menu->childs as $menu)
                                                        
                                                            @if(!empty($menu->module) && in_array($menu->module, $paid_modules))
                                                                @if(moduleStatusCheck($menu->module))
                                                                    
                                                                    <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                        data-section_id="{{ $menu->parent_id }}"
                                                                        data-parent_route="{{$menu->parent_id}}"
                                                                        data-parent="{{ $menu->parent_id }}" >
                                                                        <div class="card accordion_card"
                                                                            id="accordion_{{ $menu->id }}">
                                                                            <div class="card-header item_header"
                                                                                id="heading_{{ $menu->id }}">
                                                                                <div class="dd-handle">
                                                                                    <div class="float-left">
                                                                                        {{ $menu->name }}
                                                                                    </div>
                                                                                </div>
                                                                                <div class="float-right btn_div">
                                                                                    <div class="edit_icon">
                                                                                        

                                                                                        <i class="ti-close remove_menu"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>

                                                                        <ol class="dd-list">
                                                                            @foreach ($menu->childs as $submenu)

                                                                            @if(sidebarPermission($submenu))
                                                                                <li data-id="{{ $submenu->id }}" >
                                                                                    <div class="card accordion_card"
                                                                                        id="accordion_{{ $submenu->id }}">
                                                                                        <div class="card-header item_header"
                                                                                            id="heading_{{ $submenu->id }}">
                                                                                            <div class="dd-handle">
                                                                                                <div class="float-left">
                                                                                                    {{ __($submenu->lang_name ) }}
                                                                                                    
                                                                                                </div>
                                                                                            </div>
                                                                                        <div class="float-right btn_div">
                                                                                            <div class="edit_icon">
                                                                                                

                                                                                                <i class="ti-close remove_menu"></i>
                                                                                            </div>
                                                                                        </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </li>
                                                                                @endif
                                                                            @endforeach
                                                                        </ol>
                                                                    </li>  
                                                                    
                                                                @endif
                                                            @else    
                                                                @if((sidebarPermission($menu)==true))
                                                                    @if($menu->module == 'fees_collection'  || $menu->module == 'Fees')
                                                                         @if($menu->module == 'Fees' && generalSetting()->fees_status  == 1 )
                                                                            <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                            data-section_id="{{ $menu->parent_id }}"
                                                                            data-parent_route="{{$menu->parent_id}}"
                                                                            data-parent="{{ $menu->parent_id }}" >
                                                                            <div class="card accordion_card"
                                                                                id="accordion_{{ $menu->id }}">
                                                                                <div class="card-header item_header"
                                                                                    id="heading_{{ $menu->id }}">
                                                                                    <div class="dd-handle">
                                                                                        <div class="float-left">
                                                                                            {{ $menu->name }} 
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="float-right btn_div">
                                                                                        <div class="edit_icon">
                                                                                            
    
                                                                                            <i class="ti-close remove_menu"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
    
                                                                            </div>
    
                                                                            <ol class="dd-list">
                                                                                @foreach ($menu->childs as $submenu)
                                                                                    @if(sidebarPermission($submenu))
                                                                                        <li data-id="{{ $submenu->id }}" >
                                                                                            <div class="card accordion_card"
                                                                                                id="accordion_{{ $submenu->id }}">
                                                                                                <div class="card-header item_header"
                                                                                                    id="heading_{{ $submenu->id }}">
                                                                                                    <div class="dd-handle">
                                                                                                        <div class="float-left">
                                                                                                            {{ $submenu->name }}
                                                                                                            
                                                                                                        </div>
                                                                                                    </div>
                                                                                                <div class="float-right btn_div">
                                                                                                    <div class="edit_icon">
                                                                                                        
        
                                                                                                        <i class="ti-close remove_menu"></i>
                                                                                                    </div>
                                                                                                </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </li>
                                                                                    @endif
                                                                                @endforeach
                                                                            </ol>
                                                                        </li> 
                                                                         @endif
                                                                         @if($menu->module == 'fees_collection' && generalSetting()->fees_status  == 0 )
                                                                            <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                            data-section_id="{{ $menu->parent_id }}"
                                                                            data-parent_route="{{$menu->parent_id}}"
                                                                            data-parent="{{ $menu->parent_id }}" >
                                                                            <div class="card accordion_card"
                                                                                id="accordion_{{ $menu->id }}">
                                                                                <div class="card-header item_header"
                                                                                    id="heading_{{ $menu->id }}">
                                                                                    <div class="dd-handle">
                                                                                        <div class="float-left">
                                                                                            {{ $menu->name }}
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="float-right btn_div">
                                                                                        <div class="edit_icon">
                                                                                            
    
                                                                                            <i class="ti-close remove_menu"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
    
                                                                            </div>
    
                                                                            <ol class="dd-list">
                                                                                @foreach ($menu->childs as $submenu)
                                                                                    @if(sidebarPermission($submenu))
                                                                                        <li data-id="{{ $submenu->id }}" >
                                                                                            <div class="card accordion_card"
                                                                                                id="accordion_{{ $submenu->id }}">
                                                                                                <div class="card-header item_header"
                                                                                                    id="heading_{{ $submenu->id }}">
                                                                                                    <div class="dd-handle">
                                                                                                        <div class="float-left">
                                                                                                            {{ $submenu->name }}
                                                                                                            
                                                                                                        </div>
                                                                                                    </div>
                                                                                                <div class="float-right btn_div">
                                                                                                    <div class="edit_icon">
                                                                                                        
        
                                                                                                        <i class="ti-close remove_menu"></i>
                                                                                                    </div>
                                                                                                </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </li>
                                                                                    @endif
                                                                                @endforeach
                                                                            </ol>
                                                                        </li> 
                                                                         @endif
                                                                    @else   
                                                                    
                                                                        @if($menu->route == 'fees.student-fees-list-parent' || $menu->route =='parent-fees')
                                                                            @if($menu->route == 'fees.student-fees-list-parent' && generalSetting()->fees_status  == 1)
                                                                                 <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                                    data-section_id="{{ $menu->parent_id }}"
                                                                                    data-parent_route="{{$menu->parent_id}}"
                                                                                    data-parent="{{ $menu->parent_id }}" >
                                                                                    <div class="card accordion_card"
                                                                                        id="accordion_{{ $menu->id }}">
                                                                                        <div class="card-header item_header"
                                                                                            id="heading_{{ $menu->id }}">
                                                                                            <div class="dd-handle">
                                                                                                <div class="float-left">
                                                                                                    {{ $menu->name }} 
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="float-right btn_div">
                                                                                                <div class="edit_icon">
                                                                                                    
            
                                                                                                    <i class="ti-close remove_menu"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
            
                                                                                    </div>
            
                                                                                    <ol class="dd-list">
                                                                                        @foreach ($menu->childs as $submenu)
                                                                                            @if(sidebarPermission($submenu))
                                                                                                <li data-id="{{ $submenu->id }}" >
                                                                                                    <div class="card accordion_card"
                                                                                                        id="accordion_{{ $submenu->id }}">
                                                                                                        <div class="card-header item_header"
                                                                                                            id="heading_{{ $submenu->id }}">
                                                                                                            <div class="dd-handle">
                                                                                                                <div class="float-left">
                                                                                                                    {{ $submenu->name }}
                                                                                                                    
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        <div class="float-right btn_div">
                                                                                                            <div class="edit_icon">
                                                                                                                
                
                                                                                                                <i class="ti-close remove_menu"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                        </li>
                                                                                    @endif
                                                                                @endforeach
                                                                            </ol>
                                                                        </li> 
                                                                            @endif
                                                                            
                                                                            @if($menu->route == 'parent-fees' && generalSetting()->fees_status  == 0)
                                                                                 <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                                    data-section_id="{{ $menu->parent_id }}"
                                                                                    data-parent_route="{{$menu->parent_id}}"
                                                                                    data-parent="{{ $menu->parent_id }}" >
                                                                                    <div class="card accordion_card"
                                                                                        id="accordion_{{ $menu->id }}">
                                                                                        <div class="card-header item_header"
                                                                                            id="heading_{{ $menu->id }}">
                                                                                            <div class="dd-handle">
                                                                                                <div class="float-left">
                                                                                                    {{ $menu->name }} 
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="float-right btn_div">
                                                                                                <div class="edit_icon">
                                                                                                    
            
                                                                                                    <i class="ti-close remove_menu"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
            
                                                                                    </div>
            
                                                                                    <ol class="dd-list">
                                                                                        @foreach ($menu->childs as $submenu)
                                                                                            @if(sidebarPermission($submenu))
                                                                                                <li data-id="{{ $submenu->id }}" >
                                                                                                    <div class="card accordion_card"
                                                                                                        id="accordion_{{ $submenu->id }}">
                                                                                                        <div class="card-header item_header"
                                                                                                            id="heading_{{ $submenu->id }}">
                                                                                                            <div class="dd-handle">
                                                                                                                <div class="float-left">
                                                                                                                    {{ $submenu->name }}
                                                                                                                    
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        <div class="float-right btn_div">
                                                                                                            <div class="edit_icon">
                                                                                                                
                
                                                                                                                <i class="ti-close remove_menu"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </li>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </ol>
                                                                                </li> 
                                                                            @endif
                                                                            
                                                                        @else    
                                                                             <li class="dd-item" data-id="{{ $menu->id }}" 
                                                                                data-section_id="{{ $menu->parent_id }}"
                                                                                data-parent_route="{{$menu->parent_id}}"
                                                                                data-parent="{{ $menu->parent_id }}" >
                                                                                <div class="card accordion_card"
                                                                                    id="accordion_{{ $menu->id }}">
                                                                                    <div class="card-header item_header"
                                                                                        id="heading_{{ $menu->id }}">
                                                                                        <div class="dd-handle">
                                                                                            <div class="float-left">
                                                                                                {{ $menu->name }} 
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="float-right btn_div">
                                                                                            <div class="edit_icon">
                                                                                                
        
                                                                                                <i class="ti-close remove_menu"></i>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
        
                                                                                </div>
        
                                                                                <ol class="dd-list">
                                                                                    @foreach ($menu->childs as $submenu)
                                                                                        @if(sidebarPermission($submenu))
                                                                                            <li data-id="{{ $submenu->id }}" >
                                                                                                <div class="card accordion_card"
                                                                                                    id="accordion_{{ $submenu->id }}">
                                                                                                    <div class="card-header item_header"
                                                                                                        id="heading_{{ $submenu->id }}">
                                                                                                        <div class="dd-handle">
                                                                                                            <div class="float-left">
                                                                                                                {{ $submenu->name }}
                                                                                                                
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    <div class="float-right btn_div">
                                                                                                        <div class="edit_icon">
                                                                                                            
            
                                                                                                            <i class="ti-close remove_menu"></i>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </li>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </ol>
                                                                            </li> 
                                                                        @endif
                                                                            
                                                                    @endif 
                                                                @endif  
                                                            @endif
                                                                                                               
                                                        @endforeach
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion2" class="dd menu-list used_menu"
                                                    data-section="{{ $sidebar_menu->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
            @endif
        </div>
    </div>


    @push('scripts')
        <script src="{{ asset('public/backEnd/js/jquery.nestable.min.js') }}"></script>
        <script src="{{ asset('Modules/MenuManage/Resources/assets/js/sidebar.js') }}"></script>
    @endpush


</div>
