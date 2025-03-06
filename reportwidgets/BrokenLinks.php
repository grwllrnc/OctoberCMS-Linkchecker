<?php

namespace Bombozama\LinkCheck\ReportWidgets;

use Exception;
use Backend\Classes\ReportWidgetBase;
use Carbon\Carbon;
use Bombozama\LinkCheck\Models\BrokenLink;
use Bombozama\LinkCheck\Models\Context;

/**
 * BrokenLinks Report Widget
 *
 * @link https://docs.octobercms.com/3.x/extend/backend/report-widgets.html
 */
class BrokenLinks extends ReportWidgetBase
{
    protected $defaultAlias = 'BrokenLinksReportWidget';

    public function defineProperties()
    {
        return [
            'name' => [
                'title' => 'Name',
                'default' => __('bombozama.linkcheck::lang.reportwidget.title'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => __('bombozama.linkcheck::lang.reportwidget.validation.required.message'),
                    ],
                    'regex' => [
                        'message' => __('bombozama.linkcheck::lang.reportwidget.validation.regex.message'),
                        'pattern' => '^[a-zA-Z\s]+$',
                    ]
                ]
            ],
        ];
    }

    public function render()
    {
        try {
            $this->getBrokenLinks();
        }
        catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('brokenlinks');
    }

    public function getBrokenLinks()
    {
        $last_check = new Carbon(Context::orderBy('id', 'desc')->first()->last_checked);
        $this->vars['total'] = BrokenLink::all()->count();
        $this->vars['grouped'] = BrokenLink::all()->groupBy('status')->toArray();
        $this->vars['last_check'] = $last_check->toDayDateTimeString();
    }
}
