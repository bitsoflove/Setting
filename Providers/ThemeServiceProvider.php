<?php namespace Modules\Setting\Providers;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {
            $this->registerAllThemes();
            $this->setActiveTheme();
        });
    }

    /**
     * Set the active theme based on the settings
     */
    private function setActiveTheme()
    {
        if ($this->app->runningInConsole() || ! app('asgard.isInstalled')) {
            return;
        }

        if ($this->inAdministration()) {
            $themeName = $this->app['config']->get('asgard.core.core.admin-theme');
            return $this->app['stylist']->activate($themeName, true);
        }

        //if multi site, then grab themeName from db
        if (is_module_enabled('Site')) {
            $currentLocale = \Site::currentLocale();

            //if(!empty($currentLocale)) {
                $themeName = $currentLocale->theme;
            //} else {
            //    \Log::warning('Invalid locale defined! ThemeServiceProvider@setActiveTeam');
            //    $themeName = $this->app['setting.settings']->get('core::template', null, 'Flatly');
            //}
        } else {
            $themeName = $this->app['setting.settings']->get('core::template', null, 'Flatly');
        }

        return $this->app['stylist']->activate($themeName, true);
    }

    /**
     * Check if we are in the administration
     * @return bool
     */
    private function inAdministration()
    {
        $segment = config('laravellocalization.hideDefaultLocaleInURL', false) ? 1 : 2;

        return $this->app['request']->segment($segment) === $this->app['config']->get('asgard.core.core.admin-prefix');
    }

    /**
     * Register all themes with activating them
     */
    private function registerAllThemes()
    {
        $directories = $this->app['files']->directories(config('stylist.themes.paths', [base_path('/Themes')])[0]);

        foreach ($directories as $directory) {
            $this->app['stylist']->registerPath($directory);
        }
    }
}
