<?php

namespace bupy7\config\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\db\Query;
use bupy7\config\models\Config;
use bupy7\config\Module;

/**
 * Configurtion manager for create, delete and update configuration parameters of application.
 * 
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class ManagerController extends Controller
{  
    /**
     * @var array List parameters of config the application.
     * @see Module::$params
     */
    protected $params;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->params = Module::getInstance()->params;
    }
    
    /**
     * Initialize configuration of application.
     */
    public function actionInit()
    {
        if (!$this->confirm('Initialization configuration of application?')) {
            return self::EXIT_CODE_NORMAL;
        }     
        // insert params
        $success = 0;
        $all = count($this->params);
        foreach ($this->params as $param) {
            $success += $this->insert($param);
        }       
        // flush cache
        $this->run('cache/flush-all');      
        $this->stdout(
            "\nConfiguration successfully initialized. All parameters: {$all}. Successfully added: {$success}.\n", 
            Console::FG_GREEN
        );
    }
    
    /**
     * Rescan configuration parameters of application. Delete not exists and add new parameters.
     */
    public function actionRescan()
    {        
        $added = 0;
        $removed = 0;
        $allowedParams = (new Query)
            ->from(Config::tableName())
            ->indexBy(function($row) {
                return $row['module'] . '.' . $row['name'];
            })
            ->all();
        // add
        foreach ($this->params as $param) {
            $key = $param['module'] . '.' . $param['name'];
            if (!isset($allowedParams[$key])) {
                $added += $this->insert($param);
            }
            unset($allowedParams[$key]);
        }     
        // remove
        foreach ($allowedParams as $param) {
            $removed += Yii::$app->db->createCommand()
                ->delete(Config::tableName(), ['id' => $param['id']])
                ->execute();
        }
        // flush cache
        if ($added > 0) {
            $this->run('cache/flush-all');
        }       
        $this->stdout("Rescan successfully finished. Added: {$added}. Removed: {$removed}.\n", Console::FG_GREEN);
    }
    
    /**
     * Insert configuration parameter.
     * @param array $param Parameter from $config.
     * @return integer
     */
    protected function insert(array $param)
    {
        foreach (['rules', 'options'] as $binary) {
            if (isset($param[$binary])) {
                $param[$binary] = serialize($param[$binary]);
            }
        }
        return Yii::$app->db->createCommand()->insert(Config::tableName(), $param)->execute();
    }   
}

