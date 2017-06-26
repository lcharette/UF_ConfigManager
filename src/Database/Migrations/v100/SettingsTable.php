<?php
/**
 * UF Config Manager
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2016 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */
namespace UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * Settings table migration
 * @extends Migration
 */
class SettingsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('settings')) {
            $this->schema->create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('value')->nullable();
                $table->boolean('cached')->default(1);
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('settings');
    }
}