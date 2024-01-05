<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * UpdateBackendUsers Migration
 */
class UpdateBackendUsersTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->string('forwn_bloghub_display_name', 128)->nullable();
            $table->string('forwn_bloghub_author_slug', 128)->unique()->nullable();
            $table->text('forwn_bloghub_about_me')->nullable();
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if (Schema::hasColumn('backend_users', 'forwn_bloghub_display_name'))
        {
            Schema::table('backend_users', function (Blueprint $table) {
                $table->dropColumn('forwn_bloghub_display_name');
                $table->dropColumn('forwn_bloghub_author_slug');
                $table->dropColumn('forwn_bloghub_about_me');
            });
        }
    }
}
