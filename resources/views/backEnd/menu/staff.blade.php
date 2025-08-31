@php
    $menus = getMenus("staff");  
    $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InfixBiometrics','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InAppLiveClass'];
    $module_enable = false;
    foreach($paid_modules as $module){
        if(moduleStatusCheck($module)){
            $module_enable = true;
        }
    }
    $free_modules = ['Chat','fees_collection','Fees'];
   
@endphp
    
@foreach($menus as $key => $menu)
    @if($menu->route == 'dashboard_section')
       <span class="menu_seperator" id="{{$menu->route}}"  data-section="{{ $menu->route }}">{{ __($menu->lang_name)}} </span>
        @if($menu->childs->count() > 0)   
            
                @foreach($menu->childs as $child)
                   @if(userPermission($child->route))  
                        <li class="{{ spn_active_link([$child->route], "mm-active") }} {{ $child->route }} main">
                            <a href="{{ validRouteUrl($child->route) }}">
                                <div class="nav_icon_small">
                                    <span class="{{ $child->icon }}"></span>
                                </div>
                                <div class="nav_title">
                                    <span>{{ !empty($child->lang_name) ?  __($child->lang_name):$child->name }}</span>
                                </div>
                            </a>
                        </li>
                    @endif 
                @endforeach
            @endif
    @else  
        @if ($menu->route == 'module_section')
            @if($module_enable)
                    @if($menu->childs->count() > 0)    
                    <span class="menu_seperator" id="seperator_{{$menu->route}}"  data-section="{{ $menu->route }}">{{ __($menu->lang_name)}} </span>
                        @foreach($menu->childs as $child)
                            @if($child->childs->count() > 0)
                                @if(userPermission($child->route) && isMenuAllowToShow($child->route))
                                    @if(!empty($child->module) && in_array($child->module,$paid_modules) )
                                        @if( moduleStatusCheck($child->module))
                                            @includeIf('backEnd.menu.staff_sub_menu',compact('menu','child'))     
                                        @endif
                                    @else                                    
                                        @includeIf('backEnd.menu.staff_sub_menu',compact('menu','child'))       
                                    @endif                            
                                @endif
                                
                            @else  
                                @if(userPermission($child->route) && isMenuAllowToShow($child->route))  
                                    <li class="{{ spn_active_link([$child->route], "mm-active") }}  main">
                                        <a href="{{ validRouteUrl($child->route) }}">
                                            <div class="nav_icon_small">
                                                <span class="{{ $child->icon }}"></span>
                                            </div>
                                            <div class="nav_title">
                                                <span>{{ !empty($child->lang_name) ?  __($child->lang_name):$child->name }}</span>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                {{-- @if($key == 1)
                                    @if(auth()->user()->school_id != 1)
                                        @includeIf('saas::menu.staff')
                                    @endif
                                @endif --}}
                            @endif
                        @endforeach
                    @endif

            @endif

        @else   
            @if($menu->childs->count() > 0)   
            <span class="menu_seperator" id="seperator_{{$menu->route}}"  data-section="{{ $menu->route }}">{{ __($menu->lang_name)}} </span>
                @foreach($menu->childs as $child)
                    @if($child->childs->count() > 0)
                        @if(userPermission($child->route) && isMenuAllowToShow($child->route))
                        @if($child->module == 'Fees' || $child->module == 'fees_collection')
                            @if($child->module == 'Fees' && generalSetting()->fees_status  == 1)
                            @includeIf('backEnd.menu.staff_sub_menu',compact('menu','child'))
                            @endif
                            @if($child->module == 'fees_collection' && generalSetting()->fees_status  == 0)
                            @includeIf('backEnd.menu.staff_sub_menu',compact('menu','child'))
                            @endif
                        @else
                            @includeIf('backEnd.menu.staff_sub_menu',compact('menu','child'))
                        @endif 
                        @endif
                    
                    @else  
                        
                        @if(userPermission($child->route) && isMenuAllowToShow($child->route))  
                            @if($child->route == 'manage-adons')
                                @if(! moduleStatusCheck('Saas'))
                                     <li class="{{ spn_active_link([$child->route], "mm-active") }} {{ $child->route }} main">
                                        <a href="{{ validRouteUrl($child->route) }}">
                                            <div class="nav_icon_small">
                                                <span class="{{ $child->icon }}"></span>
                                            </div>
                                            <div class="nav_title">
                                                <span>{{ !empty($child->lang_name) ?  __($child->lang_name):$child->name }} </span>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                            @else
                            <li class="{{ spn_active_link([$child->route], "mm-active") }} {{ $child->route }} main">
                                <a href="{{ validRouteUrl($child->route) }}">
                                    <div class="nav_icon_small">
                                        <span class="{{ $child->icon }}"></span>
                                    </div>
                                    <div class="nav_title">
                                        <span>{{ !empty($child->lang_name) ?  __($child->lang_name):$child->name }} </span>
                                    </div>
                                </a>
                            </li>
                            @endif
                        @endif                    
                    @endif
                @endforeach
            @endif
            
        @endif
    @endif

@endforeach


