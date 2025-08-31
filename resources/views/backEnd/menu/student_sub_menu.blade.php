

@php
    $routes = subModuleRoute($child);
@endphp


<li class="{{ spn_active_link($routes, "mm-active") }} main">
    <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
        <div class="nav_icon_small">
            <span class="{{ $child->icon }}"></span>
        </div>
        <div class="nav_title">
             <span> {{ !empty($child->lang_name) ? __($child->lang_name):$child->name }} </span>
        </div>
    </a>
    <ul class="mm-collapse">  
        @foreach($child->childs as $third)                  
            @if(userPermission($third->route))
                <li class="sub">
                    <a href="{{ validRouteUrl($third->route) }}" class="{{ spn_active_link($third->route) }}">   
                        {{ !empty($third->lang_name) ? __($third->lang_name):$third->name }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</li>