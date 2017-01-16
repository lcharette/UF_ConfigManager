<?php

    use Illuminate\Database\Schema\Blueprint;

    /**
     * Settings table.
     */
    if (!$schema->hasTable('settings')) {
        $schema->create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->string('value')->nullable();
            $table->boolean('cached')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
        });
        echo "Created table 'settings'..." . PHP_EOL;
    } else {
        echo "Table 'settings' already exists.  Skipping..." . PHP_EOL;
    }