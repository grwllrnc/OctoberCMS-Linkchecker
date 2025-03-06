<?php

namespace Bombozama\LinkCheck\Models;

use DB;
use File;
use Flash;
use Lang;
use Schema;
use Model;
use Config;
use Cms\Classes\Theme;
use October\Rain\Exception\ValidationException;
use Bombozama\LinkCheck\Classes\Helper;
use Bombozama\LinkCheck\Models\UserAgent;

class Settings extends \System\Models\SettingModel 
{
    use \October\Rain\Database\Traits\Validation;

    public $settingsCode = 'bombozama_linkcheck_settings';
    public $settingsFields = 'fields.yaml';
    public $rules = [];


    public function beforeValidate(): void
    {
        $plugins = $this->plugins;
        $modelators = $this->modelators;
        $checkCMS = $this->checkCMS;
        $dirs = $this->dirs;

        if ($checkCMS && !$dirs) {
            throw new ValidationException(['dirs' => 'You must select at least one option if the feature is enabled.']);
        }
        if ($plugins && !$modelators) {
            throw new ValidationException(['modelators' => 'You must select at least one option if the feature is enabled.']);
        }
    }

    # List all CMS Theme dirs for custom selection
    public function getDirOptions(): array
    {
        $dirs = [];
        $theme = Theme::getActiveTheme();
        $theme->getPath();
        foreach (File::directories($theme->getPath()) as $themeSubDir) {
            $dirs[basename($themeSubDir)] = basename($themeSubDir);
        }
        return $dirs;
    }

    # List all CMS Theme dirs for custom selection
    public function getPluginOptions(): array
    {
        $plugins = [];
        foreach (File::directories(plugins_path()) as $author) {
            foreach (File::directories($author) as $plugin) {
                $dir = basename($author) . '/' . basename($plugin);
                if ($dir === 'bombozama/linkcheck') {
                    continue;
                }
                $plugins[$dir] = $dir;
            } 
        }
        return $plugins;
    }

    # Render options for dropdowns on settings/fields.yaml
    public function getModelatorOptions(): array
    {
        $include_plugins = $this->plugins ?? [];
        $models  = $out = [];
        $authors = File::directories(plugins_path());
        $plugin_pattern = '/\/(\w+\/\w+)$/';
        foreach ( $authors as $author ) {
            foreach (File::directories($author) as $plugin) {
                preg_match($plugin_pattern, $plugin, $matches);
                
                if (!in_array($matches[1], $include_plugins)) {
                    continue;
                }
                
                $modelPath = $plugin . DIRECTORY_SEPARATOR . 'models';
                
                if (!File::exists($modelPath)) {
                    continue;
                }

                foreach (File::files($modelPath) as $modelFile) {
                    # All links in the LinkCheck plugin table are broken. Skip.
                    $linkCheckPluginPath = plugins_path() . DIRECTORY_SEPARATOR . 'bombozama' . DIRECTORY_SEPARATOR . 'linkcheck';
                    
                    if ($plugin == $linkCheckPluginPath) {
                        continue;
                    }

                    $models[] = Helper::getFullClassNameFromFile((string) $modelFile);
                }
            }
        }
        foreach ($models as $model) {
            if (substr($model, - 5) == 'Pivot') {
                continue;
            }
            // Check if class is abstact, because abstract classes cannot be instanciated
            $class = new \ReflectionClass($model);
            if (!$class->isAbstract()) {
                $object = new $model();

                if (!isset($object->table)) {
                    continue;
                }

                foreach (Schema::getColumnListing($object->table) as $column) {
                    $type = DB::connection()->getDoctrineColumn( $object->table, $column )->getType()->getName();
                    
                    if (in_array($type, ['string', 'text'])) {
                        $out[$model . '::' . $column] = $model . '::' . $column;
                    }
                }
            }
        }

        return $out;
    }

    public function getUserAgentOptions()
    {
        return UserAgent::pluck('user_agent', 'id');
    }

    public function filterFields($fields, $context = null)
    {
        if ($this->checkCMS) {
            $fields->dirs->hidden = false;
        }

        if ($this->plugins) {
            $fields->modelators->hidden = false;
        }
    }
}
