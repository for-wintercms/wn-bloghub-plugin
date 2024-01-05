<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * CreateMetaTable Migration
 */
class CreateMetaTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        if (!PluginManager::instance()->hasPlugin('Winter.Blog')) {
            return;
        }

        Schema::create('forwn_bloghub_meta', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 64);
            $table->text('value')->nullable();
            $table->integer('metable_id')->nullable();
            $table->string('metable_type', 64);
            $table->timestamps();

            $table->unique(['name', 'metable_id', 'metable_type']);
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        Schema::dropIfExists('forwn_bloghub_meta');
    }
}
