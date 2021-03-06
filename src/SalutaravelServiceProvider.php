<?php

namespace AaronAdrian\Salutaravel;


use AaronAdrian\Salutaravel\Exceptions\SalutaravelException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class SalutaravelServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->bootValidationExtensions();
        $this->publishes([
            $this->getPackageConfigPath() => config_path('salutaravel.php')
        ], 'salutaravel_config');
    }

    public function register()
    {
        $this->mergeConfigFrom($this->getPackageConfigPath(), 'salutaravel');

    }

    protected function getPackageConfigPath()
    {
        return __DIR__.'/../config/salutaravel.php';
    }

    protected function bootValidationExtensions()
    {
        $rules = collect(config('salutaravel.rules'));

        Validator::extend('salutation', function ($attribute, $value, $parameters) use ($rules) {

            $parameters = collect($parameters);

            if($parameters->isEmpty())
            {
                throw new SalutaravelException(str_replace_first('?', $rules->keys()->map(function($item) { return '"' . $item . '"'; })->implode(', '), 'Salutation validation rule must contain one of the following parameters: ?'));
            }

            $ruleParameter = $parameters->first();

            if(!$rules->keys()->contains($ruleParameter))
            {
                throw new SalutaravelException(str_replace_first('?', $ruleParameter, 'The parameter "?" is not valid for the Salutation validation rule.'));
            }

            return (new $rules[$ruleParameter])->passes($attribute, $value);
        });
    }

}