<?php namespace Bombozama\Linkcheck\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateContextTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::create('bombozama_linkcheck_context', function(Blueprint $table) {
            $table->id();
            $table->integer('broken_link_id');
            $table->string('plugin');
            $table->string('model');
            $table->integer('model_id')->nullable();
            $table->string('field')->nullable();
            $table->dateTime('last_checked');
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('bombozama_linkcheck_context');
    }
};
