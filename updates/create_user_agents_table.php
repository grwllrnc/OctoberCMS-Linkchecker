<?php namespace Bombozama\Linkcheck\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateUserAgentsTable Migration
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
        Schema::create('bombozama_linkcheck_user_agents', function(Blueprint $table) {
            $table->id();
            $table->string('user_agent');
            $table->timestamps();
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('bombozama_linkcheck_user_agents');
    }
};
