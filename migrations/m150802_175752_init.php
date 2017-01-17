<?php

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
            'id' => $this->primaryKey(),
            'module' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'value' => $this->text(),
            'type' => $this->smallInteger()->notNull(),
            'language' => $this->string(16)->notNull()->defaultValue('-'),
            'hint' => $this->text(),
            'options' => $this->text(),
            'rules' => $this->text(),
        ], $tableOptions);
        $this->createIndex('config_idx_1', Config::tableName(), ['module', 'name', 'language'], true);
        $this->createIndex('config_idx_2', Config::tableName(), ['language']);
        $this->createIndex('config_idx_3', Config::tableName(), ['module']);
    }

    public function down()
    {
        $this->dropTable(Config::tableName());
    }
}
