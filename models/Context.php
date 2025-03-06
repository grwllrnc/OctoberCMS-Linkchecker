<?php 

namespace Bombozama\LinkCheck\Models;

use Carbon\Carbon;
use Model;
use Config;
use Bombozama\LinkCheck\Classes\Helper;
use Bombozama\LinkCheck\Models\Settings;
use Bombozama\LinkCheck\Models\BrokenLink;
use Cms\Classes\Theme;
use File;

class Context extends Model
{
    public $table = 'bombozama_linkcheck_context';
    protected $fillable = ['broken_link_id', 'model', 'plugin', 'model_id', 'field'];
    public $belongsTo = [
        'brokenLink' => \Bombozama\LinkCheck\Models\BrokenLink::class
    ];

    /**
     * Checks for broken links in selected database fields and/or all CMS files
     * @return array
     */
    public static function processLinks(): array
    {
        \Log::info(__('bombozama.linkcheck::lang.strings.log.check_started'));
        $start = microtime(true);
        $helper = new Helper();
        $url_set = array();
        $check_count = 0;
        $url_count = 0;
        $settings = Settings::instance();
        
        # Remove all data from tables
        Context::truncate();
        BrokenLink::truncate();
        
        if ($modulators = $settings->modelators) {
            foreach ($modulators as $el) {
                list($modelName, $field) = explode('::', $el);
                $models = $modelName::whereNotNull($field)->get();

                foreach ($models as $model) {
                    $model_field;

                    if (is_array($model->$field)) {
                        $model_field = implode(', ', $helper->flattenArray($model->$field));
                    } else {
                        $model_field = $model->$field;
                    }

                    $urls = $helper->scanForUrls($model_field);
                    
                    if (!$urls) {
                        continue;
                    }
                    $modelParts = explode('\\', $modelName);
                    
                    foreach ($urls as $url) {
                        $broken_link_id = null;
                        $status = false;
                        
                        if (isset($url_set[$url])) {

                            if ($url_set[$url] === false) {
                                continue;
                            }

                            $existing_url = BrokenLink::where('url', $url)->first();
                            $broken_link_id = $existing_url->id;
                            $status = $existing_url->status;
                        } else {
                            $status = BrokenLink::isBrokenLink($settings, $url);
                            $url_set[$url] = $status['status'];

                            if ($status['status'] === false) {
                                continue;
                            }

                            $broken_link_id = $status['id'];
                        }

                        Context::insert([
                            'broken_link_id'    => $broken_link_id,
                            'plugin'            => (sizeof($modelParts) >= 3) ? $modelParts[1] . '.' . $modelParts[2] : $modelName,
                            'model'             => (sizeof($modelParts) > 3) ? $modelParts[4] : $modelName,
                            'model_id'          => $model->id,
                            'field'             => $field,
                            'last_checked'      => Carbon::now(new \DateTimeZone('UTC')),
                        ]);
                    }
                }
            }
        }

        /**
         * Go process the current theme
         */
        $theme = Theme::getActiveTheme();
        $theme->getPath();

        /**
         * Should we process theme pages?
         */
        if ($settings['checkCMS'] == '1') {
            $include_dirs = ($settings->dirs !== "") ? $settings->dirs : [];

            foreach (File::directories($theme->getPath()) as $themeSubDir) {

                # Skip all folders except whitlisted folders
                if (!in_array(basename($themeSubDir), $include_dirs)) {
                    continue;
                }

                foreach (File::allFiles($themeSubDir) as $filePath) {
                    set_time_limit(120); // Increasing PHP script execution time
                    
                    if (str_contains($filePath, 'static-pages-en') === false) {
                        $urls = $helper->scanForUrls(file_get_contents($filePath));

                        if (!$urls) {
                            continue;
                        }

                        foreach ($urls as $url) {
                            $broken_link_id = null;
                            $status = false;

                            if (isset($url_set[$url])) {
                                
                                if ($url_set[$url] === false) {
                                    continue;
                                }

                                $existing_url = BrokenLink::where('url', $url)->first();
                                $broken_link_id = $existing_url->id;
                                $status = $existing_url->status;
                            } else {
                                $status = BrokenLink::isBrokenLink($settings, $url);
                                $url_set[$url] = $status['status'];
                                
                                if ($status['status'] === false) {
                                    continue;
                                }
                                
                                $broken_link_id = $status['id'];
                            }

                            Context::insert([
                                'broken_link_id'    => $broken_link_id,
                                'plugin'            => 'CMS',
                                'model'             => str_replace($theme->getPath() . DIRECTORY_SEPARATOR, '', $filePath),
                                'last_checked'      => Carbon::now(new \DateTimeZone('UTC')),
                            ]);
                        }
                    }
                }
            }
        }

        $checked = count($url_set);
        $reported = array_filter($url_set, fn($url) => $url !== false);
        \Log::info(__('bombozama.linkcheck::lang.strings.log.summary', ['seconds' => round(microtime(true) - $start, 2), 'urls' => $checked, 'reported' => count($reported)]));
        
        return array(
            'checked' => $checked,
            'reported' => count($reported),
        );
    }
}