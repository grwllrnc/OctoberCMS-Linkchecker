<?php namespace Bombozama\LinkCheck\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateBrokenLinksTable2 extends Migration
{

    public function up()
    {
        Schema::table('bombozama_linkcheck_broken_links', function ($table)
        {
            $table->dropColumn('plugin');
            $table->dropColumn('model');
            $table->dropColumn('model_id');
            $table->dropColumn('context');
            $table->dropColumn('field');
        });
    }

    public function down()
    {
        Schema::table('bombozama_linkcheck_broken_links', function($table)
        {
            $table->string('plugin');
            $table->string('model');
            $table->integer('model_id')->nullable();
            $table->string('field')->nullable();
            $table->text('context')->nullable();
        });
    }

}