<h4>{{ __('menumanage::menuManage.Live Preview') }}</h4>
<div class="mt_30">
    @php
        $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InfixBiometrics','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InAppLiveClass'];
        $module_enable = false;
        foreach($paid_modules as $module){
            if(moduleStatusCheck($module))
            {
                $module_enable = true;
            }
        }
    @endphp
    <nav class="preview_menu_wrapper">
        <ul id="previewMenu">

            @if (isset($sidebar_menus))
            
                @foreach($sidebar_menus as $preview_section)
                        @if($preview_section->route == 'module_section')    
                            @if($module_enable)
                                @if($preview_section->childs->count() > 0)
                                    <li class="preview_section">
                                        {{__(@$preview_section->lang_name)}}
                                    </li>
                                    
                                    @foreach (@$preview_section->childs as $key => $item)
                                        @if(!empty($item->module) && in_array($item->module, $paid_modules))
                                            @if(moduleStatusCheck($item->module))
                                                        @if(sidebarPermission($item)==true)                                        
                                                        <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                            @endif
                                        @else    
                                            @if(sidebarPermission($item)==true)
                                                
                                                @if($item->module == 'Fees' || $item->module == 'fees_collection')
                                                    @if($item->module == 'Fees' && generalSetting()->fees_status  == 1 )
                                                         <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }} {{ $item->module }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }} {{ $item->module }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif

                                                    @if($item->module == 'fees_collection' && generalSetting()->fees_status  == 0 )
                                                         <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }} {{ $item->module }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }} {{ $item->module }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                                @else   
                                                 <li class="">
                                                    <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                        <div class="nav_icon_small">
                                                            <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                        </div>
                                                        <div class="nav_title">
                                                            <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                        </div>
                                                    </a>
                                                    @if ($item->childs->count())
                                                        <ul>
                                                            @foreach ($item->childs as $submenu)
                                                                <li>
                                                                    <a href="#">
                                                                        {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }} {{ $item->module }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                                @endif

                                               
                                            @endif
                                        @endif
                                    @endforeach
                                @endif

                            @endif
                        @else    
                            @if($preview_section->childs->count() > 0)
                                <li class="preview_section">
                                    {{__(@$preview_section->lang_name)}}
                                </li>
                                
                                @foreach (@$preview_section->childs as $key => $item)
                                    @if(!empty($item->module) && in_array($item->module, $paid_modules))
                                        @if(moduleStatusCheck($item->module))
                                                    @if(sidebarPermission($item)==true)                                        
                                                    <li class="">
                                                        <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                            <div class="nav_icon_small">
                                                                <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                            </div>
                                                            <div class="nav_title">
                                                                <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                            </div>
                                                        </a>
                                                        @if ($item->childs->count())
                                                            <ul>
                                                                @foreach ($item->childs as $submenu)
                                                                    <li>
                                                                        <a href="#">
                                                                            {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endif
                                        @endif
                                    @else    
                                        @if(sidebarPermission($item)==true)
                                             @if($item->module == 'Fees'  || $item->module == 'fees_collection')
                                                    @if($item->module == 'Fees' && generalSetting()->fees_status  == 1 )
                                                        <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif 
                                                    
                                                    
                                                    @if($item->module == 'fees_collection' && generalSetting()->fees_status  == 0 )
                                                        <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                             @else   
                                             
                                              @if($item->route == 'fees.student-fees-list-parent' || $item->route =='parent-fees')
                                                    @if($item->route == 'fees.student-fees-list-parent' && generalSetting()->fees_status  == 1))
                                                        <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                                    
                                                    @if( $item->route =='parent-fees' &&   generalSetting()->fees_status  == 0)
                                                        <li class="">
                                                            <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                                <div class="nav_icon_small">
                                                                    <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                                </div>
                                                                <div class="nav_title">
                                                                    <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                                </div>
                                                            </a>
                                                            @if ($item->childs->count())
                                                                <ul>
                                                                    @foreach ($item->childs as $submenu)
                                                                        <li>
                                                                            <a href="#">
                                                                                {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                              @else   
                                                    <li class="">
                                                        <a href="#" class="@if ($item->childs->count()) has-arrow @endif">
                                                            <div class="nav_icon_small">
                                                                <span class="{{ $item->icon ?? 'fas fa-th' }}"></span>
                                                            </div>
                                                            <div class="nav_title">
                                                                <span>{{$item ? __($item->lang_name ??  $item->name) : 'no' }}</span>
                                                            </div>
                                                        </a>
                                                        @if ($item->childs->count())
                                                            <ul>
                                                                @foreach ($item->childs as $submenu)
                                                                    <li>
                                                                        <a href="#">
                                                                            {{ !empty($submenu->lang_name) ? __($submenu->lang_name):$submenu->name }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                              @endif
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        @endif

                        
                    @endforeach
            @endif
        </ul>
    </nav>
</div>
