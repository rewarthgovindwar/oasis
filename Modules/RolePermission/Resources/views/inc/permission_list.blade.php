@php
     $default_theme  = ['course-heading-update','admin-home-page','custom-links','social-media','From Download','class-exam-routine-page','course-details-heading','news-heading-update','exam-result-page','contact-page','about-page','conpactPage'];
     $edulia_theme = ['home-slider','pagebuilder','expert-teacher','photo-gallery','video-gallery','front-result','front-class-routine','front-exam-routine','front-academic-calendar','admin-home-page'];
     $active_theme = activeTheme();
     $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InAppLiveClass','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InfixBiometrics'];
@endphp
<div class="single_role_blocks">
                    <div class="single_permission" id="{{ $permission->id }}">

                        <div class="permission_header d-flex align-items-center justify-content-between">

                            <div>
                                <input type="checkbox" name="module_id[]" value="{{ $permission->id }}"
                                    id="Main_Module_{{ $key }}"
                                    class="common-radio permission-checkAll main_module_id_{{ $permission->id }}"
                                    {{ in_array($permission->id, $already_assigned) ? 'checked' : '' }}>
                                <label
                                    for="Main_Module_{{ $key }}">{{ __($permission->lang_name) }}</label>
                            </div>

                            <div class="arrow collapsed" data-toggle="collapse" data-target="#Role{{ $permission->id }}">


                            </div>

                        </div>

                        <div id="Role{{ $permission->id }}" class="collapse">
                            <div class="permission_body">
                                <ul>
                                    @foreach ($permission->subModule as $row2)
                                        @if(!empty($row2->module) && in_array($permission->module, $paid_modules) )
                                            @if(moduleStatusCheck($row2->module))
                                              @includeIf('rolepermission::inc.permission_row')
                                            @endif
                                        @else   
                                             @includeIf('rolepermission::inc.permission_row')
                                        @endif
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
</div>