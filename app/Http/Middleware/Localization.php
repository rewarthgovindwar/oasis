<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Storage::exists('.app_installed') && Storage::get('.app_installed')) {
            App::setLocale(getUserLanguage());
            Cache::forget('translations');
            if (Storage::exists('.app_installed') && Storage::get('.app_installed')) {
                Cache::remember('translations', Carbon::now()->addHours(6), function () {
                    return $this->getTranslations();
                });

            }

            $school_id = 1;
            if (auth()->check()) {
                $school_id = auth()->user()->school_id;
            } elseif (app()->bound('school')) {
                $school_id = app('school')->id;
            }

            date_default_timezone_set(generalSetting()->timeZone->time_zone ?? 'Asia/Dhaka');
        }

        return $next($request);
    }

    public function getTranslations()
    {
        $translations = collect();

        $ln = [
            'en',
        ];
        $userLang = getUserLanguage();

        if ($userLang !== 'en') {
            $ln[] = $userLang;
        }

        foreach ($ln as $locale) {
            $translations[$locale] = [
                'json' => $this->jsonTranslations($locale),
                'php' => $this->phpTranslations($locale),
            ];
        }

        return $translations;
    }

    private function jsonTranslations(string $locale)
    {
        $files = glob(resource_path('lang/'.$locale.'/*.php'));

        $modules = \Nwidart\Modules\Facades\Module::all();
        foreach ($modules as $module) {
            if (moduleStatusCheck($module->getName())) {
                $module_files = glob(module_path($module->getName()).'/Resources/lang/'.$locale.'/*.php');
                foreach ($module_files as $module_file) {
                    $files[] = $module_file;
                }
            }
        }

        $lang = [];

        foreach ($files as $file) {
            $file_name = basename($file, '.php');
            if ($file_name !== 'lang' && file_exists($file) && is_array(include $file)) {
                $lang = array_merge($lang, include ($file));
            }
        }

        return json_encode($lang, 1);

    }

    private function phpTranslations(string $locale)
    {
        $path = resource_path('lang/'.$locale);
        if (file_exists($path)) {
            return collect(File::allFiles($path))->flatMap(function ($file) use ($locale) {
                $key = $file->getBasename('.php');
                $translation = $key;

                return [$key => trans($translation, [], $locale)];
            });
        }

        return null;

    }
}
