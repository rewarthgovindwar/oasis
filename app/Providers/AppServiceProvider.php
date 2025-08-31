<?php

namespace App\Providers;

use App\Models\Plugin;
use App\SmGeneralSettings;
use App\SmNotification;
use App\SmParent;
use Exception;
use Illuminate\Database\Schema\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\MenuManage\Entities\Sidebar;
use Modules\RolePermission\Entities\AssignPermission;
use Modules\RolePermission\Entities\InfixModuleInfo;
use Modules\RolePermission\Entities\InfixRole;
use Modules\RolePermission\Entities\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): ?bool
    {

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle);
        });

        try {
            Paginator::useBootstrapFour();
            Builder::defaultStringLength(191);

            view()->composer('backEnd.partials.parents_sidebar', function ($view): void {
                $data = [
                    'childrens' => SmParent::myChildrens(),
                ];
                $view->with($data);

            });

            view()->composer('backEnd.partials.menu', function ($view): void {
                $notifications = DB::table('notifications')->where('notifiable_id', auth()->id())
                    ->where('read_at', null)
                    ->get();

                foreach ($notifications as $notification) {
                    $notification->data = json_decode($notification->data);
                }

                $view->with(['notifications_for_chat' => $notifications]);
            });

            view()->composer(['backEnd.master', 'backEnd.partials.menu'], function ($view): void {
                $data = [
                    'notifications' => SmNotification::notifications(),
                ];
                $view->with($data);
            });

            view()->composer(['plugins.tawk_to'], function ($view): void {
                $data = [
                    'agent' => new \Jenssegers\Agent\Agent(),
                    'tawk_setting' => Plugin::where('name', 'tawk')->where('school_id', app('school')->id)->first(),
                ];
                $view->with($data);
            });

            view()->composer(['backEnd.partials.menu', 'layouts.pb-site', 'frontEnd.home.front_master'], function ($view): void {
                $pluginCheck = Plugin::whereIn('name', ['tawk', 'messenger'])->where('school_id', app('school')->id)->get();
                $tawk = $pluginCheck->where('name', 'tawk')->first();
                $messenger = $pluginCheck->where('name', 'messenger')->first();
                $data = [
                    'tawk' => $tawk,
                    'messenger' => $messenger,
                    'position' => $tawk ? $tawk->position : null,
                    'messenger_position' => $messenger ? $messenger->position : null,
                ];
                $view->with($data);
            });

            view()->composer(['plugins.messenger'], function ($view): void {
                $data = [
                    'agent' => new \Jenssegers\Agent\Agent(),
                    'messenger_setting' => Plugin::where('name', 'messenger')->where('school_id', app('school')->id)->first(),
                ];
                $view->with($data);
            });

            if (Storage::exists('.app_installed') && Storage::get('.app_installed')) {
                config(['broadcasting.default' => saasEnv('chatting_method')]);
                config(['broadcasting.connections.pusher.key' => saasEnv('pusher_app_key')]);
                config(['broadcasting.connections.pusher.secret' => saasEnv('pusher_app_secret')]);
                config(['broadcasting.connections.pusher.app_id' => saasEnv('pusher_app_id')]);
                config(['broadcasting.connections.pusher.options.cluster' => saasEnv('pusher_app_cluster')]);
            }

        } catch (Exception $exception) {
            return false;
        }

        return null;
    }

    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
        $this->app->singleton('dashboard_bg', function () {
            return DB::table('sm_background_settings')->where('school_id', app('school')->id)->where([['is_default', 1], ['title', 'Dashboard Background']])->first();
        });

        $this->app->singleton('school_info', function () {
            if (app()->bound('school')) {
                return SmGeneralSettings::where('school_id', app('school')->id)->first();
            }

            return false;
        });

        $this->app->singleton('school_menu_permissions', function () {
            $module_ids = getPlanPermissionMenuModuleId();

            return InfixModuleInfo::where('parent_id', 0)->with(['children'])->whereIn('id', $module_ids)->get();
        });

        $this->app->singleton('permission', function (): void {

            $infixRole = InfixRole::find(Auth::user()->role_id);
            $permissionIds = AssignPermission::where('role_id', Auth::user()->role_id)
                ->when($infixRole->is_saas == 0, function ($q): void {
                    $q->where('school_id', Auth::user()->school_id);
                })->pluck('permission_id')->toArray();
                dd($permissionIds);
            $permissions = Permission::whereIn('id', $permissionIds)->pluck('route')->toArray();

        });

        $this->app->singleton('saasSettings', function () {
            return \Modules\Saas\Entities\SaasSettings::where('saas_status', 0)->pluck('infix_module_id')->toArray();
        });

        $this->app->singleton('sidebar_news', function () {
            return Sidebar::get();
        });

    }
}
