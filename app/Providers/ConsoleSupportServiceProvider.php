<?php namespace App\Providers;

class ConsoleSupportServiceProvider extends \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider
{
    /**
     * Constructor
     * @param $args
     */
    function __construct(...$args)
    {
        parent::__construct(...$args);

        $parent_providers = collect($this->providers)->filter(function($provider)
        {
            return $provider != 'Illuminate\Database\MigrationServiceProvider';
        });

        $parent_providers->push('App\Providers\MigrationServiceProvider');

        $this->providers = $parent_providers->all();
    }
}
