<?php

namespace JaxWilko\DataMigrator;

use Event;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'          => 'jaxwilko.datamigrator::lang.plugin.name',
            'description'   => 'jaxwilko.datamigrator::lang.plugin.description',
            'author'        => 'Jack Wilkinson',
            'icon'          => 'oc-icon-database',
            'homepage'      => 'https://github.com/jaxwilko/oc-data-migrator-plugin'
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'jaxwilko.datamigrator::lang.settings.label',
                'description' => 'jaxwilko.datamigrator::lang.settings.description',
                'icon' => 'oc-icon-database',
                'class' => 'JaxWilko\DataMigrator\Models\Settings',
                'category' => SettingsManager::CATEGORY_CMS,
                'permissions' => ['jaxwilko.datamigrator.settings']
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'jaxwilko.datamigrator.access_controls' => [
                'label' => 'jaxwilko.datamigrator::lang.settings.controls_description',
                'tab' => 'jaxwilko.datamigrator::lang.plugin.name',
                'order' => 1000,
            ],
            'jaxwilko.datamigrator.build_data' => [
                'label' => 'jaxwilko.datamigrator::lang.settings.build_data',
                'tab' => 'jaxwilko.datamigrator::lang.plugin.name',
                'order' => 1001,
            ],
        ];
    }

    public function boot()
    {
        \App::register('\JaxWilko\DataMigrator\ServiceProvider');
    }
}
