@php
    $menus = getMenus("student");    
    $paid_modules = ['Zoom','University','Gmeet','QRCodeAttendance','BBB','ParentRegistration','InAppLiveClass','AiContent','Lms','Certificate','Jitsi','WhatsappSupport','InfixBiometrics'];
@endphp
@foreach($menus as $key => $menu)
<span class="menu_seperator" id="seperator_{{ \Illuminate\Support\str::lower($menu->name) }}"  data-section="{{ $menu->route }}">{{ __($menu->lang_name)}}</span>
    @if($menu->childs->count() > 0)      
        @foreach($menu->childs as $child)
            @if($child->childs->count() > 0)
                @if(userPermission($child->route))
                    @if(!empty($child->module) && in_array($child->module, $paid_modules))
                        @if(moduleStatusCheck($child->module))
                            @includeIf('backEnd.menu.student_sub_menu',['menu' => $menu,'child' => $child]) 
                        @endif
                    @else    
                        @includeIf('backEnd.menu.student_sub_menu',['menu' => $menu,'child' => $child]) 
                    @endif
                @endif
            @else  
                @if(userPermission($child->route))  
                    <li class="{{ spn_active_link([$child->route], "mm-active") }} {{ $child->route }} main">
                        <a href="{{ validRouteUrl($child->route) }}">
                            <div class="nav_icon_small">
                                <span class="{{ $child->icon }}"></span>
                            </div>
                            <div class="nav_title">
                                <span> {{ !empty($child->lang_name) ?  __($child->lang_name):$child->name }}</span>
                            </div>
                        </a>
                    </li>
                @endif               
            @endif
        @endforeach
    @endif
@endforeach

{{-- @if( userPermission("student-dashboard"))
    <li class="{{ spn_active_link(['student-dashboard',], "mm-active") }} student-dashboard main">
        <a href="{{ validRouteUrl('student-dashboard') }}">
            <div class="nav_icon_small">
                <span class="fas fa-bars"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('common.dashboard') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("student-profile") &&  userPermission("student-profile"))
    <li class="{{ spn_active_link(['student-profile',], "mm-active") }} student-profile main">
        <a href="{{ validRouteUrl('student-profile') }}">
            <div class="nav_icon_small">
                <span class="flaticon-resume"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('student.my_profile') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("fees.student-fees-list") &&  userPermission("fees.student-fees-list"))
    <li class="{{ spn_active_link(['fees.student-fees-list',], "mm-active") }} fees.student-fees-list main">
        <a href="{{ validRouteUrl('fees.student-fees-list') }}">
            <div class="nav_icon_small">
                <span class="fas fa-money"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('fees.fees') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("wallet.my-wallet") &&  userPermission("wallet.my-wallet"))
    <li class="{{ spn_active_link(['wallet.my-wallet',], "mm-active") }} wallet.my-wallet main">
        <a href="{{ validRouteUrl('wallet.my-wallet') }}">
            <div class="nav_icon_small">
                <span class="ti-wallet"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('wallet::wallet.my_wallet') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("student_class_routine") &&  userPermission("student_class_routine"))
    <li class="{{ spn_active_link(['student_class_routine',], "mm-active") }} student_class_routine main">
        <a href="{{ validRouteUrl('student_class_routine') }}">
            <div class="nav_icon_small">
                <span class="flaticon-calendar-1"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('academics.class_routine') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("download_center") &&  userPermission("download-center"))
    <li class="{{ spn_active_link(['download-center','download-center.content-share-list','download-center.video-list',], "mm-active") }} download-center main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="fas fa-solid fa-download"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('downloadCenter.download_center') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("download-center.content-share-list"))
                <li class="sub">
                    <a href="{{ validRouteUrl('download-center.content-share-list') }}"
                       class="{{ spn_active_link('download-center.content-share-list') }}">
                        {{ __('downloadCenter.shared_content_list') }}
                    </a>
                </li>
            @endif
            @if(userPermission("download-center.video-list"))
                <li class="sub">
                    <a href="{{ validRouteUrl('download-center.video-list') }}"
                       class="{{ spn_active_link('download-center.video-list') }}">
                        {{ __('downloadCenter.video_list') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("lesson_plan") &&  userPermission("lesson-plan"))
    <li class="{{ spn_active_link(['lesson-plan','lesson-student-lessonPlan','lesson-student-lessonPlan-overview',], "mm-active") }} lesson-plan main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="fas fa fa-list-alt"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('lesson::lesson.lesson_plan') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("lesson-student-lessonPlan"))
                <li class="sub">
                    <a href="{{ validRouteUrl('lesson-student-lessonPlan') }}"
                       class="{{ spn_active_link('lesson-student-lessonPlan') }}">
                        {{ __('lesson::lesson.lesson_plan') }}
                    </a>
                </li>
            @endif
            @if(userPermission("lesson-student-lessonPlan-overview"))
                <li class="sub">
                    <a href="{{ validRouteUrl('lesson-student-lessonPlan-overview') }}"
                       class="{{ spn_active_link('lesson-student-lessonPlan-overview') }}">
                        {{ __('lesson::lesson.lesson_plan_overview') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("student_homework") &&  userPermission("student_homework"))
    <li class="{{ spn_active_link(['student_homework',], "mm-active") }} student_homework main">
        <a href="{{ validRouteUrl('student_homework') }}">
            <div class="nav_icon_small">
                <span class="fas fa-book-open"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('homework.homework_list') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("download_center") &&  userPermission("download_center"))
    <li class="{{ spn_active_link(['download_center','student_assignment','student_syllabus','student_others_download',], "mm-active") }} download_center main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="flaticon-data-storage"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('study.study_material') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("student_assignment"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student_assignment') }}"
                       class="{{ spn_active_link('student_assignment') }}">
                        {{ __('study.assignment') }}
                    </a>
                </li>
            @endif
            @if(userPermission("student_syllabus"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student_syllabus') }}" class="{{ spn_active_link('student_syllabus') }}">
                        {{ __('study.syllabus') }}
                    </a>
                </li>
            @endif
            @if(userPermission("student_others_download"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student_others_download') }}"
                       class="{{ spn_active_link('student_others_download') }}">
                        {{ __('study.others_download') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("student_my_attendance") &&  userPermission("student_my_attendance"))
    <li class="{{ spn_active_link(['student_my_attendance',], "mm-active") }} student_my_attendance main">
        <a href="{{ validRouteUrl('student_my_attendance') }}">
            <div class="nav_icon_small">
                <span class="flaticon-authentication"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('student.attendance') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("leave") &&  userPermission("leave"))
    <li class="{{ spn_active_link(['leave','student-apply-leave','student-pending-leave',], "mm-active") }} leave main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="flaticon-slumber"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('leave.leave') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("student-apply-leave"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student-apply-leave') }}"
                       class="{{ spn_active_link('student-apply-leave') }}">
                        {{ __('leave.apply_leave') }}
                    </a>
                </li>
            @endif
            @if(userPermission("student-pending-leave"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student-pending-leave') }}"
                       class="{{ spn_active_link('student-pending-leave') }}">
                        {{ __('leave.pending_leave_request') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("chat") &&  userPermission("chat"))
    <li class="{{ spn_active_link(['chat','chat.index','chat.invitation','chat.blocked.users',], "mm-active") }} chat main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="fas fa fa-weixin"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('chat::chat.chat') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("chat.index"))
                <li class="sub">
                    <a href="{{ validRouteUrl('chat.index') }}" class="{{ spn_active_link('chat.index') }}">
                        {{ __('chat::chat.chat_box') }}
                    </a>
                </li>
            @endif
            @if(userPermission("chat.invitation"))
                <li class="sub">
                    <a href="{{ validRouteUrl('chat.invitation') }}" class="{{ spn_active_link('chat.invitation') }}">
                        {{ __('chat::chat.invitation') }}
                    </a>
                </li>
            @endif
            @if(userPermission("chat.blocked.users"))
                <li class="sub">
                    <a href="{{ validRouteUrl('chat.blocked.users') }}"
                       class="{{ spn_active_link('chat.blocked.users') }}">
                        {{ __('chat::chat.blocked_user') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("examination") &&  userPermission("examination"))
    <li class="{{ spn_active_link(['examination','student_result','student_exam_schedule',], "mm-active") }} examination main">
        <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
            <div class="nav_icon_small">
                <span class="flaticon-test"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('exam.examinations') }}</span>
            </div>
        </a>
        <ul class="mm-collapse">
            @if(userPermission("student_result"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student_result') }}" class="{{ spn_active_link('student_result') }}">
                        {{ __('reports.result') }}
                    </a>
                </li>
            @endif
            @if(userPermission("student_exam_schedule"))
                <li class="sub">
                    <a href="{{ validRouteUrl('student_exam_schedule') }}"
                       class="{{ spn_active_link('student_exam_schedule') }}">
                        {{ __('exam.exam_schedule') }}
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
@if(isMenuAllowToShow("student_noticeboard") &&  userPermission("student_noticeboard"))
    <li class="{{ spn_active_link(['student_noticeboard',], "mm-active") }} student_noticeboard main">
        <a href="{{ validRouteUrl('student_noticeboard') }}">
            <div class="nav_icon_small">
                <span class="flaticon-poster"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('communicate.notice_board') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("academic-calendar") &&  userPermission("academic-calendar"))
    <li class="{{ spn_active_link(['academic-calendar',], "mm-active") }} academic-calendar main">
        <a href="{{ validRouteUrl('academic-calendar') }}">
            <div class="nav_icon_small">
                <span class="flaticon-poster"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('communicate.calendar') }}</span>
            </div>
        </a>
    </li>
@endif
@if(isMenuAllowToShow("student_subject") &&  userPermission("student_subject"))
    <li class="{{ spn_active_link(['student_subject',], "mm-active") }} student_subject main">
        <a href="{{ validRouteUrl('student_subject') }}">
            <div class="nav_icon_small">
                <span class="flaticon-reading-1"></span>
            </div>
            <div class="nav_title">
                <span>{{ __('common.subjects') }}</span>
            </div>
        </a>
    </li>
@endif
@if (!moduleStatusCheck('OnlineExam'))
    @if(isMenuAllowToShow("online_exam") &&  userPermission("online_exam"))
        <li class="{{ spn_active_link(['online_exam','student_online_exam','student_view_result',], "mm-active") }} online_exam main">
            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                <div class="nav_icon_small">
                    <span class="flaticon-test-1"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('exam.online_exam') }}</span>
                </div>
            </a>
            <ul class="mm-collapse">
                @if(userPermission("student_online_exam"))
                    <li class="sub">
                        <a href="{{ validRouteUrl('student_online_exam') }}"
                           class="{{ spn_active_link('student_online_exam') }}">
                            {{ __('exam.active_exams') }}
                        </a>
                    </li>
                @endif
                @if(userPermission("student_view_result"))
                    <li class="sub">
                        <a href="{{ validRouteUrl('student_view_result') }}"
                           class="{{ spn_active_link('student_view_result') }}">
                            {{ __('exam.view_result') }}
                        </a>
                    </li>
                @endif
            </ul>
            @endif
        </li>
    @endif
    @if(isMenuAllowToShow("student_teacher") &&  userPermission("student_teacher"))
        <li class="{{ spn_active_link(['student_teacher',], "mm-active") }} student_teacher main">
            <a href="{{ validRouteUrl('student_teacher') }}">
                <div class="nav_icon_small">
                    <span class="flaticon-professor"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('common.teacher') }}</span>
                </div>
            </a>
        </li>
    @endif
    @if(isMenuAllowToShow("student_transport") &&  userPermission("student_transport"))
        <li class="{{ spn_active_link(['student_transport',], "mm-active") }} student_transport main">
            <a href="{{ validRouteUrl('student_transport') }}">
                <div class="nav_icon_small">
                    <span class="flaticon-bus"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('transport.transport') }}</span>
                </div>
            </a>
        </li>
    @endif
    @if(isMenuAllowToShow("library") &&  userPermission("library"))
        <li class="{{ spn_active_link(['library','student_library','student_book_issue',], "mm-active") }} library main">
            <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">
                <div class="nav_icon_small">
                    <span class="flaticon-book-1"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('library.library') }}</span>
                </div>
            </a>
            <ul class="mm-collapse">
                @if(userPermission("student_library"))
                    <li class="sub">
                        <a href="{{ validRouteUrl('student_library') }}"
                           class="{{ spn_active_link('student_library') }}">
                            {{ __('library.book_list') }}
                        </a>
                    </li>
                @endif
                @if(userPermission("student_book_issue"))
                    <li class="sub">
                        <a href="{{ validRouteUrl('student_book_issue') }}"
                           class="{{ spn_active_link('student_book_issue') }}">
                            {{ __('library.book_issue') }}
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif
    @if(isMenuAllowToShow("student_dormitory") &&  userPermission("student_dormitory"))
        <li class="{{ spn_active_link(['student_dormitory',], "mm-active") }} student_dormitory main">
            <a href="{{ validRouteUrl('student_dormitory') }}">
                <div class="nav_icon_small">
                    <span class="fas fa-hotel"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('dormitory.dormitory') }}</span>
                </div>
            </a>
        </li>
    @endif
    <span class="menu_seperator" id="seperator_module_section" data-section="module"> {{ __("common.module")}} </span>
    @foreach($paid_modules as $module)
        @includeIf(strtolower($module)."::menu.student")
    @endforeach --}}