<?php namespace Bombozama\LinkCheck\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateContextTable extends Migration
{

    public function up()
    {
        Schema::table('bombozama_linkcheck_context', function($table)
        {
            $table->string('last_checked')->change();
        });
    }

    public function down()
    {
        Schema::table('bombozama_linkcheck_context', function($table)
        {
            $table->dateTime('last_checked');
        });
    }

}