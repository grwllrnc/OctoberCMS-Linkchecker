<?php

namespace Bombozama\LinkCheck;

use Backend;
use Flash;
use Lang;
use System\Classes\PluginBase;
use Bombozama\LinkCheck\Models\Context;
use Bombozama\LinkCheck\Models\Settings;
use Bombozama\LinkCheck\ReportWidgets\BrokenLinks;

/**
 * LinkCheck Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'bombozama.linkcheck::lang.details.name',
            'description' => 'bombozama.linkcheck::lang.details.description',
            'author'      => 'Gonzalo Henríquez',
            'icon'        => 'icon-chain-broken',
            'homepage'    => 'https://github.com/bombozama/linkcheck',
        ];
    }

    public function register()
    {
        Settings::extend(function ($model) {
            $model->bindEvent('model.beforeSave', function () use ($model) {
                if (!post('Settings[plugins]')) {
                    // If no plugin was selected, remove the selected model fields
                    // by setting modelators to an empty string.
                    $model->modelators = '';
                    Flash::info(Lang::get('bombozama.linkcheck::lang.strings.plugins_empty'));
                }
            });
        });
    }

    public function registerPermissions()
    {
        return [
            'bombozama.linkcheck.manage' => [
                'tab'   => 'bombozama.linkcheck::lang.plugin.tab',
                'label' => 'bombozama.linkcheck::lang.plugin.manage',
            ],
            'bombozama.linkcheck.view' => [
                'tab'   => 'bombozama.linkcheck::lang.plugin.tab',
                'label' => 'bombozama.linkcheck::lang.plugin.view',
            ],
            'bombozama.linkcheck.useragent' => [
                'tab'   => 'bombozama.linkcheck::lang.plugin.tab',
                'label' => 'bombozama.linkcheck::lang.plugin.useragent',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'bombozama.linkcheck::lang.menu.settings.label',
                'description' => 'bombozama.linkcheck::lang.menu.settings.description',
                'category'    => 'bombozama.linkcheck::lang.plugin.category',
                'icon'        => 'icon-chain-broken',
                'class'       => 'Bombozama\LinkCheck\Models\Settings',
                'order'       => 410,
                'permissions' => ['bombozama.linkcheck.manage'],
            ],
            'brokenlinks' => [
                'label'       => 'bombozama.linkcheck::lang.menu.brokenlinks.label',
                'description' => 'bombozama.linkcheck::lang.menu.brokenlinks.description',
                'category'    => 'bombozama.linkcheck::lang.plugin.category',
                'icon'        => 'icon-list',
                'url'         => Backend::url('bombozama/linkcheck/context'),
                'order'       => 411,
                'permissions' => ['bombozama.linkcheck.view'],
            ],
            'userAgent' => [
                'label'       => 'bombozama.linkcheck::lang.menu.useragent.label',
                'description' => 'bombozama.linkcheck::lang.menu.useragent.description',
                'category'    => 'bombozama.linkcheck::lang.plugin.category',
                'icon'        => 'icon-user-secret',
                'url'         => Backend::url('bombozama/linkcheck/useragent'),
                'order'       => 412,
                'permissions' => ['bombozama.linkcheck.useragent'],
            ],
        ];
    }

    public function registerListColumnTypes()
    {
        return [
            'httpstatus' => [$this, 'httpStatus'],
        ];
    }

    public function httpStatus($value, $column, $record)
    {
        return '<span title="' . Lang::get('bombozama.linkcheck::lang.codes.' . $value) . '">' . $value . '</span>';
    }

    public function registerReportWidgets()
    {
        return [
            BrokenLinks::class => [
                'label' => 'bombozama.linkcheck::lang.details.name',
                'context' => 'dashboard',
                'permissions' => [
                    'bombozama.linkcheck.view',
                ],
            ],
        ];
    }

    // Please do https://octobercms.com/docs/setup/installation#crontab-setup
    public function registerSchedule($schedule)
    {
        $settings = Settings::instance();
        if ($settings->time) {
            $schedule->call(function() {
                Context::processLinks();
            })->cron($settings->time);
        }
    }
}
