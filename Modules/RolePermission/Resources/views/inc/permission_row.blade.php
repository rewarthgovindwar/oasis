@php
     $new_fees = ['fees.fees-group','fees.due-fees','fees.fees-type','fees.fine-report','fees','fees.fees-invoice-list','fees.payment-report','fees-invoice-bulk-print','fees.bank-payment','fees.balance-report','fees-invoice-bulk-print-settings','fees_forward','fees.waiver-report'];
     $old_fees = ['fees_statement','balance_fees_report','transaction_report','fine-report','fees-bulk-print', 'fees_group', 'fees_type','fees-master','fees_discount','collect_fees','search_fees_payment','search_fees_due','fees_forward','bank-payment-slip'];
@endphp

@if(in_array($row2->route, $new_fees) || in_array($row2->route, $old_fees))
    @if(in_array($row2->route, $new_fees) && generalSetting()->fees_status  == 1)
        @if(in_array( $row2->route, $default_theme) || in_array($row2->route, $edulia_theme))                                            
            @if(in_array( $row2->route, $default_theme) && $active_theme == 'default')
                <li>
                    <div class="submodule">
                        <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                            value="{{ $row2->id }}"
                            class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                            type="checkbox"
                            {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                        <label
                            for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}  </label>
                        <br>
                    </div>

                    <ul class="option mt-20">

                        @foreach ($row2->subModule as $row3)
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </li>
            @endif
            @if(in_array( $row2->route, $edulia_theme) && $active_theme == 'edulia')
                <li>
                    <div class="submodule">
                        <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                            value="{{ $row2->id }}"
                            class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                            type="checkbox"
                            {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                        <label
                            for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}</label>
                        <br>
                    </div>

                    <ul class="option mt-20">

                        @foreach ($row2->subModule as $row3)
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </li>
            @endif                                            
        @else     
            <li>
                <div class="submodule">
                    <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                        value="{{ $row2->id }}"
                        class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                        type="checkbox"
                        {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                    <label
                        for="Sub_Module_{{ $row2->id }}">{{ !empty($row2->lang_name) ?  __( $row2->lang_name):$row2->name }}  </label>
                    <br>
                </div>

                <ul class="option mt-20">

                    @foreach ($row2->subModule as $row3)
                        @if(!empty($row3->module))
                            @if(moduleStatusCheck($row3->module))
                                <li>
                                    <div class="module_link_option_div" id="{{ $row2->id }}">
                                        <input id="Option_{{ $row3->id }}" name="module_id[]"
                                            value="{{ $row3->id }}"
                                            class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                            type="checkbox"
                                            {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                        <label
                                            for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}  </label>
                                        <br>
                                    </div>
                                </li>
                            @endif
                        @else   
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endif
                    @endforeach

                </ul>
            </li>
        @endif

    @endif

    @if(in_array($row2->route, $old_fees) && generalSetting()->fees_status  == 0)
        @if(in_array( $row2->route, $default_theme) || in_array($row2->route, $edulia_theme))                                            
            @if(in_array( $row2->route, $default_theme) && $active_theme == 'default')
                <li>
                    <div class="submodule">
                        <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                            value="{{ $row2->id }}"
                            class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                            type="checkbox"
                            {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                        <label
                            for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}</label>
                        <br>
                    </div>

                    <ul class="option mt-20">

                        @foreach ($row2->subModule as $row3)
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </li>
            @endif
            @if(in_array( $row2->route, $edulia_theme) && $active_theme == 'edulia')
                <li>
                    <div class="submodule">
                        <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                            value="{{ $row2->id }}"
                            class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                            type="checkbox"
                            {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                        <label
                            for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}</label>
                        <br>
                    </div>

                    <ul class="option mt-20">

                        @foreach ($row2->subModule as $row3)
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </li>
            @endif                                            
        @else     
            <li>
                <div class="submodule">
                    <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                        value="{{ $row2->id }}"
                        class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                        type="checkbox"
                        {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                    <label
                        for="Sub_Module_{{ $row2->id }}">{{ !empty($row2->lang_name) ?  __( $row2->lang_name):$row2->name }}  </label>
                    <br>
                </div>

                <ul class="option mt-20">

                    @foreach ($row2->subModule as $row3)
                        @if(!empty($row3->module))
                            @if(moduleStatusCheck($row3->module))
                                <li>
                                    <div class="module_link_option_div" id="{{ $row2->id }}">
                                        <input id="Option_{{ $row3->id }}" name="module_id[]"
                                            value="{{ $row3->id }}"
                                            class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                            type="checkbox"
                                            {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                        <label
                                            for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                        <br>
                                    </div>
                                </li>
                            @endif
                        @else   
                            <li>
                                <div class="module_link_option_div" id="{{ $row2->id }}">
                                    <input id="Option_{{ $row3->id }}" name="module_id[]"
                                        value="{{ $row3->id }}"
                                        class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                        type="checkbox"
                                        {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                    <label
                                        for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                                    <br>
                                </div>
                            </li>
                        @endif
                    @endforeach

                </ul>
            </li>
        @endif
    @endif
@else    
@if(in_array( $row2->route, $default_theme) || in_array($row2->route, $edulia_theme))                                            
    @if(in_array( $row2->route, $default_theme) && $active_theme == 'default')
        <li>
            <div class="submodule">
                <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                    value="{{ $row2->id }}"
                    class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                    type="checkbox"
                    {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                <label
                    for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}</label>
                <br>
            </div>

            <ul class="option mt-20">

                @foreach ($row2->subModule as $row3)
                    <li>
                        <div class="module_link_option_div" id="{{ $row2->id }}">
                            <input id="Option_{{ $row3->id }}" name="module_id[]"
                                value="{{ $row3->id }}"
                                class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                type="checkbox"
                                {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                            <label
                                for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                            <br>
                        </div>
                    </li>
                @endforeach

            </ul>
        </li>
    @endif
    @if(in_array( $row2->route, $edulia_theme) && $active_theme == 'edulia')
        <li>
            <div class="submodule">
                <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                    value="{{ $row2->id }}"
                    class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                    type="checkbox"
                    {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

                <label
                    for="Sub_Module_{{ $row2->id }}">{{ __( $row2->lang_name) }}</label>
                <br>
            </div>

            <ul class="option mt-20">

                @foreach ($row2->subModule as $row3)
                    <li>
                        <div class="module_link_option_div" id="{{ $row2->id }}">
                            <input id="Option_{{ $row3->id }}" name="module_id[]"
                                value="{{ $row3->id }}"
                                class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                type="checkbox"
                                {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                            <label
                                for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                            <br>
                        </div>
                    </li>
                @endforeach

            </ul>
        </li>
    @endif                                            
@else   

 @if(!empty($row2->module))
        @if(moduleStatusCheck($row2->module))
            <li>
        <div class="submodule">
            <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                value="{{ $row2->id }}"
                class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                type="checkbox"
                {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

            <label
                for="Sub_Module_{{ $row2->id }}">{{ !empty($row2->lang_name) ?  __( $row2->lang_name):$row2->name }}  </label>
            <br>
        </div>

        <ul class="option mt-20">

            @foreach ($row2->subModule as $row3)
                @if(!empty($row3->module))
                    @if(moduleStatusCheck($row3->module))
                        <li>
                            <div class="module_link_option_div" id="{{ $row2->id }}">
                                <input id="Option_{{ $row3->id }}" name="module_id[]"
                                    value="{{ $row3->id }}"
                                    class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                    type="checkbox"
                                    {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                <label
                                    for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}  </label>
                                <br>
                            </div>
                        </li>
                    @endif
                @else   
                    <li>
                        <div class="module_link_option_div" id="{{ $row2->id }}">
                            <input id="Option_{{ $row3->id }}" name="module_id[]"
                                value="{{ $row3->id }}"
                                class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                type="checkbox"
                                {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                            <label
                                for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                            <br>
                        </div>
                    </li>
                @endif
            @endforeach

        </ul>
    </li>
        @endif
 @else  
    <li>
        <div class="submodule">
            <input id="Sub_Module_{{ $row2->id }}" name="module_id[]"
                value="{{ $row2->id }}"
                class="infix_csk common-radio  module_id_{{ $permission->id }} module_link"
                type="checkbox"
                {{ in_array($row2->id, $already_assigned) ? 'checked' : '' }}>

            <label
                for="Sub_Module_{{ $row2->id }}">{{ !empty($row2->lang_name) ?  __( $row2->lang_name):$row2->name }}  </label>
            <br>
        </div>

        <ul class="option mt-20">

            @foreach ($row2->subModule as $row3)
                @if(!empty($row3->module))
                    @if(moduleStatusCheck($row3->module))
                        <li>
                            <div class="module_link_option_div" id="{{ $row2->id }}">
                                <input id="Option_{{ $row3->id }}" name="module_id[]"
                                    value="{{ $row3->id }}"
                                    class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                    type="checkbox"
                                    {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                                <label
                                    for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}  </label>
                                <br>
                            </div>
                        </li>
                    @endif
                @else   
                    <li>
                        <div class="module_link_option_div" id="{{ $row2->id }}">
                            <input id="Option_{{ $row3->id }}" name="module_id[]"
                                value="{{ $row3->id }}"
                                class="infix_csk common-radio    module_id_{{ $permission->id }} module_option_{{ $permission->id }}_{{ $row2->id }} module_link_option"
                                type="checkbox"
                                {{ in_array($row3->id, $already_assigned) ? 'checked' : '' }}>

                            <label
                                for="Option_{{ $row3->id }}">{{ __('rolepermission::permissions.' . $row3->name) }}</label>
                            <br>
                        </div>
                    </li>
                @endif
            @endforeach

        </ul>
    </li>
    
 @endif
@endif
@endif  


