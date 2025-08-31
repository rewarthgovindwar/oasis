<li class="{{ spn_active_link([$child->route,], "mm-active") }} {{ $child->route }} main">
    <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
        <div class="nav_icon_small">
            <span class="flaticon-reading"></span>
        </div>
        <div class="nav_title">
            <span>{{ __($child->lang_name) }} </span>
        </div>
    </a>

   
     @if($child->route == 'fees.student-fees-list')
        <ul class="mm-collapse">
            @if(userPermission($child->route))
                @foreach($children as $c)
                    <li class="sub">
                        <a href="{{ validRouteUrl('fees.student-fees-list-parent', $c->id) }}"
                            class="{{ spn_active_link('fees.student-fees-list') }}">
                            {{ __($child->lang_name) }} - {{ $c->full_name }}
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
     @else     
        <ul class="mm-collapse">
            @if(userPermission($child->route))
                @foreach($children as $c)
                    <li class="sub">
                        <a href="{{ validRouteUrl($child->route, $c->id) }}"
                            class="{{ spn_active_link($child->route) }}">
                            {{ __($child->lang_name) }} - {{ $c->full_name }}
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
     @endif

     @if($child->route == 'fees.student-fees-list')
        <ul class="mm-collapse">
            @if(userPermission($child->route))
                @foreach($children as $c)
                    <li class="sub">
                        <a href="{{ validRouteUrl('fees.student-fees-list-parent', $c->id) }}"
                            class="{{ spn_active_link('fees.student-fees-list') }}">
                            {{ __($child->lang_name) }} - {{ $c->full_name }}
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>    
     @endif

    
</li>