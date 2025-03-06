<?php

namespace Bombozama\LinkCheck\Models;

use Model;
use Bombozama\LinkCheck\Models\Context;
use Bombozama\LinkCheck\Classes\Helper;

class BrokenLink extends Model
{
    public $table = 'bombozama_linkcheck_broken_links';
    protected $fillable = ['url', 'status'];
    public $rules = [
        'url' => 'required',
        'status' => 'required',
    ];
    public $hasMany = [
        'context' => Context::class
    ];

    /**
     * Checks to see if links return selected response codes (in settings)
     * @param $url: the link url that should be checked
     * @return array
     */
    public static function isBrokenLink(Settings $settings, String $url): array
    {
        $response = Helper::getResponseCode($url, $settings->user_agent);
        $report = [];

        if (in_array(200, $settings->codes)) {
            for ($i = 200; $i <= 206; $i++) {
                $report[] = $i;
            }
        }
        if (in_array(300, $settings->codes)) {
            for ($i = 300; $i <= 308; $i++) {
                $report[] = $i;
            }
        }
        if (in_array(400, $settings->codes)) {
            for ($i = 400; $i <= 431; $i++) {
                $report[] = $i;
            }
        }
        if (in_array(500, $settings->codes)) {
            for ($i = 500; $i <= 511; $i++) {
                $report[] = $i;
            }
        }

        if (in_array($response, $report)) {
            $new_broken_link = BrokenLink::create([
                'url' => $url,
                'status' => $response,
            ]);
            return $new_broken_link->toArray();
        }

        return array(
            'id' => null,
            'url' => $url,
            'status' => false,
        );
    }
}