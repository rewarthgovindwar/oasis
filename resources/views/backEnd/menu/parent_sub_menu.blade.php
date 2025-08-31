@php
    $routes = subModuleRoute($child);
@endphp

@php
    $icon = null;
    $all_modules = ['g-meet','zoom','bbb','jitsi'];
    if(in_array($child->route, $all_modules)){
        $icon = 'fas fa-video';
    }
@endphp

<li class="{{ spn_active_link($routes, "mm-active") }}  main ">
    <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
        <div class="nav_icon_small">
            <span class="{{ !empty($child->icon) ? $child->icon:$icon }}"></span>
        </div>
        <div class="nav_title">
             <span>  {{ !empty($child->lang_name) ? __($child->lang_name):$child->name }} </span>
        </div>
    </a>

    

    <ul class="mm-collapse">  

        @if($child->route == 'download-center')
            {{-- Download Center --}}
            @if(userPermission("download-center.parent-content-share-list"))
                @foreach($children as $c)
                    <li class="sub">
                        <a href="{{ validRouteUrl('download-center.parent-content-share-list', $c->id) }}"
                           class="{{ spn_active_link('download-center.parent-content-share-list') }}">
                            {{ __('downloadCenter.content_list') }} - {{ $c->full_name }}
                        </a>
                    </li>
                @endforeach
            @endif
            @if(userPermission("download-center.parent-video-list"))
                @foreach($children as $c)
                    <li class="sub">
                        <a href="{{ validRouteUrl('download-center.parent-video-list', $c->id) }}"
                           class="{{ spn_active_link('download-center.parent-video-list') }}">
                            {{ __('downloadCenter.video_list') }} - {{ $c->full_name }}
                        </a>
                    </li>

                @endforeach
            @endif
        @else    
            @if($child->route == 'lesson-plan')
                {{-- Lesson --}}
                 @foreach($child->childs as $third)
                    @if(userPermission($third->route))
                        @foreach($children as $c)
                            <li class="sub">
                                <a href="{{ validRouteUrl($third->route, $c->id) }}"
                                class="{{ spn_active_link($third->route) }}">
                                    {{ __($third->lang_name) }} - {{ $c->full_name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                  @endforeach
            @else    
                @if($child->route == 'exam')
                    {{-- Exam --}}
                    @foreach($child->childs as $third)
                        @if(userPermission($third->route))
                            @foreach($children as $c)
                                <li class="sub">
                                    <a href="{{ validRouteUrl($third->route, $c->id) }}"
                                    class="{{ spn_active_link($third->route) }}">  {{ __($third->lang_name) }} - {{ $c->full_name }} </a>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                @else    
                    @if($child->route == 'g-meet')
                        {{-- Gmeet --}}
                        @if(userPermission("g-meet.virtual-meeting.index"))
                            <li class="sub">
                                <a href="{{ validRouteUrl('g-meet.virtual-meeting.index') }}"
                                class="{{ spn_active_link('g-meet.virtual-meeting.index') }}">
                                    {{ __('common.virtual_meeting') }}
                                </a>
                            </li>
                        @endif
                        @if(userPermission("g-meet.parent.virtual-class"))
                            @foreach($children as $c)
                                <li class="sub">
                                    <a href="{{ validRouteUrl('g-meet.virtual-class.parent.virtual-class', $c->id) }}" class="{{ spn_active_link('g-meet.virtual-class.parent.virtual-class') }}">
                                        {{ __('common.virtual_class') }} - {{ $c->full_name }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    @else    
                        @if($child->route == 'jitsi')
                        {{-- Jitsi --}}
                                @foreach($child->childs as $third)     

                                    @if($third->route == 'jitsi.parent.virtual-class')
                                         @if(userPermission($third->route))
                                            @foreach($children as $c)
                                                <li class="sub">
                                                    <a href="{{ validRouteUrl($third->route,$c->id) }}" class="{{ spn_active_link($third->route) }} {{ $third->route }}">                                                               
                                                        {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}-{{ $c->full_name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    @else 

                                        @if(userPermission($third->route))
                                            <li class="sub">
                                                <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }} {{ $third->route }}">                                                               
                                                    {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                                                </a>
                                            </li>
                                        @endif

                                    @endif
                                @endforeach
                       
                        @else    
                            @if($child->route == 'bbb')
                                @foreach($child->childs as $third)                  
                                    @if(userPermission($third->route))
                                        @if($third->route == 'bbb.parent.virtual-class' || $third->route == 'bbb.parent.class.recording.list')
                                             @foreach($children as $c)
                                                <li class="sub">
                                                    <a href="{{ validRouteUrl($third->route,$c->id) }}" class="{{ spn_active_link($third->route) }} ">                                                               
                                                        {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }} - {{ $c->full_name }}
                                                    </a>
                                                </li>
                                              @endforeach
                                        @else    
                                            <li class="sub">
                                                <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }}">                                                               
                                                    {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach              
                            @else 
                                @if($child->route == 'zoom')
                                    @foreach($child->childs as $third)                  
                                        @if(userPermission($third->route))
                                            @if($third->route == 'zoom.parent.virtual-class')
                                                @foreach($children as $c)
                                                    <li class="sub">
                                                        <a href="{{ validRouteUrl($third->route,$c->id) }}" class="{{ spn_active_link($third->route) }} ">                                                               
                                                            {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }} - {{ $c->full_name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @else     
                                                <li class="sub">
                                                    <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }} " >                                                               
                                                        {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                    @endforeach
                                @else  
                                    @if($child->route == 'lms_menu')
                                            @foreach($child->childs as $third)                  
                                                @if(userPermission($third->route))
                                                    @if($third->route == 'lms.enrolledCourse' || $third->route == 'lms.student.purchaseLog')
                                                        @foreach($children as $c)
                                                            <li class="sub">
                                                                <a href="{{ validRouteUrl($third->route,$c->id) }}" class="{{ spn_active_link($third->route) }} ">                                                               
                                                                    {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }} - {{ $c->full_name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else    
                                                        <li class="sub">
                                                            <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }} ">                                                               
                                                                {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    
                                                @endif
                                            @endforeach
                                    @else   

                                        @if($child->route == 'online_exam')
                                            @foreach($child->childs as $third)
                                                @foreach($children as $c)
                                                    <li class="sub">
                                                        <a href="{{ validRouteUrl($third->route,$c->id) }}" class="{{ spn_active_link($third->route) }} ">                                                               
                                                            {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }} - {{ $c->full_name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endforeach
                                        @else    
                                            @foreach($child->childs as $third)                  
                                                @if(userPermission($third->route))
                                                    <li class="sub">
                                                        <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }}">                                                               
                                                            {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endif

                                @endif
                            @endif
                        @endif
                    @endif
                @endif
            @endif
        @endif
        
    </ul>





</li>