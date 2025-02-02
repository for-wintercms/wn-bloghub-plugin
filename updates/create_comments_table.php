<?php namespace ForWinterCms\BlogHub\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * CreateCommentsTable Migration
 */
class CreateCommentsTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        if (!PluginManager::instance()->hasPlugin('Winter.Blog')) {
            return;
        }

        Schema::create('forwn_bloghub_comments', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('status', 32)->default('pending');
            $table->string('title', 128)->default('');
            $table->text('content');
            $table->text('content_html');
            $table->boolean('favorite')->unsigned()->default(false);
            $table->integer('likes')->unsigned()->default(0);
            $table->integer('dislikes')->unsigned()->default(0);
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_uid')->nullable();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('author_id')->unsigned()->nullable();
            $table->string('author_table', 255)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->foreign('post_id')->references('id')->on('winter_blog_posts')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('forwn_bloghub_comments')->onDelete('cascade');
        });

        Schema::table('winter_blog_posts', function (Blueprint $table) {
            $table->string('forwn_bloghub_comment_mode', 32)->default('open');
            $table->boolean('forwn_bloghub_comment_visible')->default(true);
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        Schema::dropIfExists('forwn_bloghub_comments');

        if (method_exists(Schema::class, 'dropColumns')) {
            Schema::dropColumns('winter_blog_posts', ['forwn_bloghub_comment_mode', 'forwn_bloghub_comment_visible']);
        } else {
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                if (Schema::hasColumn('winter_blog_posts', 'forwn_bloghub_comment_mode')) {
                    $table->dropColumn('forwn_bloghub_comment_mode');
                }
            });
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                if (Schema::hasColumn('winter_blog_posts', 'forwn_bloghub_comment_visible')) {
                    $table->dropColumn('forwn_bloghub_comment_visible');
                }
            });
        }
    }
}
