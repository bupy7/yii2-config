<?php

namespace bupy7\config\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\db\Query;
use bupy7\config\models\Config;

/**
 * Configurtion manager for create, delete and update configuration parameters of application.
 * 
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class ManagerController extends Controller
{  
    /**
     * @var array Configurations of application.
     * Key 'rules' and 'options' this array which will be serialized to string.
     * 'rules' content array rules for this configuration paramters without field name, only validation properties.
     * @example If you want add 'boolean' validation rule, then to model you do following:
     * ```
     * ['field_name', 'boolean']
     * ```
     * Here you must be remove 'field_name': 
     * ```
     * ['boolean']
     * ```
     * This code you must be add to validation rules.
     * @see \bupy7\config\models\Config
     */
    public $params = [
        [
            'module' => 'app', 
            'name' => 'enable', 
            'label' => 'PARAM_ENABLE', 
            'value' => '1', 
            'type' => 7, 
            'language' => 0, 
            'rules' => [
                ['boolean'],
            ],
        ],
        [
            'module' => 'app', 
            'name' => 'reasonDisable', 
            'label' => 'PARAM_REASON_DISABLE',
            'type' => 2, 
            'language' => 1, 
            'rules' => [
                ['string', 'max' => 255],
            ], 
            'options' => [
                ['maxlength' => true]
            ],
        ],
        [
            'module' => 'app', 
            'name' => 'sitenameCap', 
            'label' => 'PARAM_SITENAME_CAP', 
            'value' => 'CPA', 
            'type' => 1, 
            'language' => 1, 
            'rules' => [
                ['required'],
                ['string', 'max' => 255],
            ], 
            'options' => [
                ['maxlength' => true]
            ],
        ],
        [
            'module' => 'app', 
            'name' => 'sitename', 
            'label' => 'PARAM_SITENAME', 
            'value' => 'CPA', 
            'type' => 1, 
            'language' => 1, 
            'rules' => [
                ['required'],
                ['string', 'max' => 255],
            ], 
            'options' => [
                ['maxlength' => true]
            ],
        ],          
        [
            'module' => 'app', 
            'name' => 'displaySitename', 
            'label' => 'PARAM_DISPLAY_SITENAME', 
            'value' => '0', 
            'type' => 7, 
            'language' => 0, 
            'rules' => [
                ['boolean'],
            ], 
        ],
        [
            'module' => 'app', 
            'name' => 'supportEmail', 
            'label' => 'PARAM_SUPPORT_EMAIL', 
            'value' => 'support@support.com', 
            'type' => 1, 
            'language' => 1, 
            'rules' => [
                ['required'],
                ['email'],
            ],
        ],
        [
            'module' => 'app', 
            'name' => 'supportNameEmail', 
            'label' => 'PARAM_SUPPORT_NAME_EMAIL', 
            'value' => 'Support of site', 
            'type' => 1, 
            'language' => 1, 
            'rules' => [
                ['required'],
                ['string', 'max' => 255],
            ],
            'options' => [
                ['maxlength' => true]
            ],
        ],
    ];
    
    /**
     * @var array Translations text messages.
     * Key is message, value is array where [0] is language, [1] is translate.
     */
    public $translations = [
        'PARAM_ENABLE' => ['ru', 'Сайт включён'],
        'PARAM_REASON_DISABLE' => ['ru', 'Причина отключения'],
        'PARAM_SITENAME' => ['ru', 'Название сайта'],
        'PARAM_SITENAME_CAP' => ['ru', 'Название сайта на момент блокировки'],
        'PARAM_DISPLAY_SITENAME' => ['ru', 'Отображать название сайта в заголовке браузера'],
        'PARAM_SUPPORT_EMAIL' => ['ru', 'Email адрес тех.поддержки'],
        'PARAM_SUPPORT_NAME_EMAIL' => ['ru', 'Имя отправителя для Email адреса тех.поддержки'],
    ];
    
    /**
     * Initialize configuration of application.
     */
    public function actionInit()
    {
        $i18n = Yii::$app->getI18n()->getMessageSource('bupy7/config/params');
        
        if ($this->confirm('Initialization configuration of application?')) {
            // delete all params and translations
            Yii::$app->db->createCommand()->delete(Config::tableName())->execute();
            Yii::$app->db->createCommand()->delete($i18n->sourceMessageTable, [
                    'and',
                    ['category' => 'bupy7/config/params'],
                    [
                        'or',
                        ['like', 'message', 'PARAM_'],
                        ['like', 'message', 'HINT_PARAM_'],
                    ],
                ])
               ->execute();
        } else {
            return self::EXIT_CODE_NORMAL;
        }
        
        // insert params
        foreach ($this->params as $param) {
            $this->insert($param);
        }
        
        // flush cache
        $this->run('cache/flush-all');
        
        $this->stdout("\nConfiguration successfully initialized.\n", Console::FG_GREEN);
    }
    
    /**
     * Adding new configuration parameters of application.
     */
    public function actionAddNew()
    {        
        $count = 0;
        foreach ($this->params as $param) {
            if (
                !(new Query)
                    ->from(Config::tableName())
                    ->where([
                        'module' => $param['module'], 
                        'name' => $param['name'],
                    ])
                    ->exists()
            ) {
                $this->insert($param);
                ++$count;
            }
        }
        
        // flush cache
        if ($count > 0) {
            $this->run('cache/flush-all');
        }
        
        $this->stdout("New configuration successfully added: {$count}.\n", Console::FG_GREEN);
    }
    
    /**
     * Insert configuration parameter.
     * @param array $param Parameter from $config.
     */
    protected function insert(array $param)
    {
        $i18n = Yii::$app->getI18n()->getMessageSource('bupy7/config/params');
        
        foreach (['rules', 'options'] as $binary) {
            if (isset($param[$binary])) {
                $param[$binary] = serialize($param[$binary]);
            }
        }
        Yii::$app->db->createCommand()->insert(Config::tableName(), $param)->execute();
        
        foreach (['label', 'hint'] as $msg) {
            if (!isset($param[$msg])) {
                continue;
            }
            Yii::$app->db->createCommand()->insert($i18n->sourceMessageTable, [
                    'category' => 'bupy7/config/params',
                    'message' => $param[$msg],
                ])
                ->execute();
            $lastInsertId = Yii::$app->db->getLastInsertId();
            Yii::$app->db->createCommand()->insert($i18n->messageTable, [
                    'id' => $lastInsertId,
                    'language' => $this->translations[$param[$msg]][0],
                    'translation' => $this->translations[$param[$msg]][1],
                ])
                ->execute();
        }
    }   
}

