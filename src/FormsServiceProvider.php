<?php

namespace Khan\Forms;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\ServiceProvider;
use Khan\Forms\Commands\MakeFormCommand;
use Khan\Forms\Commands\TestFormCommand;
use Khan\Forms\Contracts\FormRegistryInterface;

class FormsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/forms.php', 'forms');

        $this->app->singleton('khan.forms', function ($app) {
            return new Forms(
                $app,
                $app->make(ValidationFactory::class)
            );
        });

        $this->app->alias('khan.forms', Forms::class);
        $this->app->alias('khan.forms', FormRegistryInterface::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'forms');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFormCommand::class,
                TestFormCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/forms.php' => config_path('forms.php'),
            ], 'forms-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/forms'),
            ], 'forms-views');

            $this->publishes([
                __DIR__ . '/Stubs/form.stub' => base_path('stubs/form.stub'),
            ], 'forms-stubs');
        }
    }
}
