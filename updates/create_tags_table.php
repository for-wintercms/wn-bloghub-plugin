<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * CreateTagsTable Migration
 */
class CreateTagsTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        if (!PluginManager::instance()->hasPlugin('Winter.Blog')) {
            return;
        }

        Schema::create('forwn_bloghub_tags', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('slug', 64)->unique();
            $table->string('title', 128)->nullable();
            $table->text('description')->nullable();
            $table->boolean('promote')->default(false);
            $table->string('color', 32)->default('primary');
            $table->timestamps();
        });

        Schema::create('forwn_bloghub_tags_posts', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->integer('tag_id')->unsigned();
            $table->integer('post_id')->unsigned();

            $table->primary(['tag_id', 'post_id']);
            $table->foreign('tag_id')->references('id')->on('forwn_bloghub_tags')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('winter_blog_posts')->onDelete('cascade');
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        Schema::dropIfExists('forwn_bloghub_tags_posts');
        Schema::dropIfExists('forwn_bloghub_tags');
    }
}
