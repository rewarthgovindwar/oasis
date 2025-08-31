@php
    $menus = getMenus("parent");    
@endphp
@foreach($menus as $key => $menu)
<span class="menu_seperator" id="seperator_{{ \Illuminate\Support\str::lower($menu->name) }}"  data-section="{{ $menu->route }}">{{ __($menu->lang_name)}}</span>
    @if($menu->childs->count() > 0)      
        @foreach($menu->childs as $child)
            @if($child->childs->count() > 0)
                @if(userPermission($child->route))
                    @if(!empty($child->module))
                        @if(moduleStatusCheck($child->module))
                            @includeIf('backEnd.menu.parent_sub_menu',['menu' => $menu,'child' => $child]) 
                        @endif
                    @else     
                            @includeIf('backEnd.menu.parent_sub_menu',['menu' => $menu,'child' => $child]) 

                    @endif                   
                @endif
            @else  
                @if(userPermission($child->route))
                     @if($child->route == 'my_children')
                     {{-- childs --}}
                        @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                     @else   
                         @if($child->route == 'fees.student-fees-list')
                            {{-- Fees --}}
                            @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                         @else   
                            @if($child->route == 'parent_class_routine')
                            {{-- Class Routine --}}
                                @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                            @else   
                                @if($child->route == 'parent_homework')
                                    {{-- Class Homework --}}
                                    @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                @else   
                                    @if($child->route == 'parent_attendance')
                                        {{-- Class Homework --}}
                                        @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                    @else   
                                        @if($child->route == 'parent_subjects')
                                            {{-- Parent Subjects --}}
                                            @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                        @else   
                                            @if($child->route == 'parent_teacher_list')
                                                {{-- Parent Teacers --}}
                                                @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                            @else   
                                                @if($child->route == 'parent_transport')
                                                    {{-- Parent Transport --}}
                                                    @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                                @else   
                                                    @if($child->route == 'parent_dormitory_list')
                                                        {{-- Parent Transport --}}
                                                        @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
                                                    @else   
                                                        @if($child->route == 'fees.student-fees-list-parent')
                                                            {{-- Parent Fees 2 --}}
                                                            @includeIf('backEnd.menu.parent_children_menu',compact('children','child')) 
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
                                            @endif 
                                        @endif
                                    @endif
                                @endif 
                            @endif
                        @endif
                     @endif
                   
                @endif               
            @endif
        @endforeach
    @endif
@endforeach