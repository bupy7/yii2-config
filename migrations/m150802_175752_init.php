<?php

use yii\db\Schema;
use yii\db\Migration;
use bupy7\config\models\Config;

class m150802_175752_init extends Migration
{
    public function up()
    {
        $this->createTable(Config::tableName(), [
            'id' => Schema::TYPE_PK,
            'module' => Schema::TYPE_STRING . ' NOT NULL',
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'label' => Schema::TYPE_STRING . ' NOT NULL',
            'value' => Schema::TYPE_TEXT,
            'type' => Schema::TYPE_SMALLINT . ' NOT NULL',
            'language' => Schema::TYPE_STRING . '(16) NOT NULL DEFAULT "-"',
            'hint' => Schema::TYPE_TEXT,
            'options' => Schema::TYPE_BINARY,
            'rule' => Schema::TYPE_BINARY,
        ]);
        $this->createIndex('unique-module-name-language', Config::tableName(), ['module', 'name', 'language'], true);
        $this->createIndex('index-language', Config::tableName(), ['language']);
        $this->createIndex('index-module', Config::tableName(), ['module']);
    }

    public function down()
    {
        $this->dropTable(Config::tableName());
    }
}
