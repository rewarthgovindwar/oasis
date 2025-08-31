<?php

namespace Modules\TemplateSettings\Providers;

use Config;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class TemplateSettingsServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'TemplateSettings';

    /**
     * @var string
     */
    protected $moduleNameLower = 'templatesettings';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        // $this->registerFactories();
        $this->loadMigrationsFrom(module_path('TemplateSettings', 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/templatesettings');

        $sourcePath = module_path('TemplateSettings', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function (string $path): string {
            return $path.'/modules/templatesettings';
        }, Config::get('view.paths')), [$sourcePath]), 'templatesettings');
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/templatesettings');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'templatesettings');
        } else {
            $this->loadTranslationsFrom(module_path('TemplateSettings', 'Resources/lang'), 'templatesettings');
        }
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories(): void
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('TemplateSettings', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('TemplateSettings', 'Config/config.php') => config_path('templatesettings.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('TemplateSettings', 'Config/config.php'), 'templatesettings'
        );
    }
}
