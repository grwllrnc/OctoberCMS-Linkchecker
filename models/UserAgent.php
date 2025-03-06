<?php

namespace Bombozama\Linkcheck\Models;

use Model;

/**
 * UserAgent Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class UserAgent extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'bombozama_linkcheck_user_agents';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'user_agent' => 'required',
    ];
}
