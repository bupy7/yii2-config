<?php

use yii\db\Schema;
use yii\db\Migration;
use bupy7\config\models\Config;

class m150802_175752_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
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
            'rules' => Schema::TYPE_BINARY,
        ], $tableOptions);
        $this->createIndex('unique-module-name-language', Config::tableName(), ['module', 'name', 'language'], true);
        $this->createIndex('index-language', Config::tableName(), ['language']);
        $this->createIndex('index-module', Config::tableName(), ['module']);
    }

    public function down()
    {
        $this->dropTable(Config::tableName());
    }
}
