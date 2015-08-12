<?php

namespace bupy7\config;

use Yii;
use yii\i18n\MessageSource;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\caching\Cache;
use bupy7\config\components\ConfigManager;
use bupy7\config\models\Config;
use bupy7\config\widgets\ActiveForm;

/**
 * This is module for configuration dynamic parameters of application.
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class Module extends \yii\base\Module
{
    /**
     * @var array|MessageSource Translation message configuration for parameters of config.
     */
    public $translation;
    /**
     * @var string The translation message category for parameters of config.
     */
    public $messageCategory = 'app';   
    /**
     * @var boolean Whether to enable caching translated messages and configuration parameters.
     */
    public $enableCaching = false;
    /**
     * @var string|array|Cache
     */
    public $cache = 'cache';
    /**
     * @var string|array|ConfigManager
     */
    public $configManager = 'configManager';
    /**
     * @var array List parameters of config the application.
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
     * @see Config::rules()
     * @see Config::afterFind()
     * @see Config::attributeLabels()
     * @see ActiveForm::field()
     */
    public $params = [
        [
            'module' => 'app',                          // module name where uses this parameter
            'name' => 'enable',                         // unique name of parameters
            'label' => 'PARAM_ENABLE',                  // label
            'value' => '1',                             // value
            'type' => Config::TYPE_YES_NO,              // type
            'language' => Config::LANGUAGE_ALL,         // for multilanguage application
            'rules' => [                                // rules
                ['boolean'],
            ],
            'options' => [                              // HTML-options
                ['maxlength' => true]
            ],
            'hint' => 'HINT_PARAM_DISPLAY_SITENAME',    // hint
        ],
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $components = [
            'cache' => [
                'value' => $this->cache,
                'type' => Cache::className(),
            ],
            'configManager' => [
                'value' => $this->configManager,
                'type' => ConfigManager::className(),
            ],
        ];
        foreach ($components as $key => $val) {
            if (is_string($this->{$key})) {
                $this->{$key} = Instance::ensure($this->{$key}, $val['type']);
            } elseif (is_array($this->{$key})) {
                $this->{$key} = Yii::createObject($this->{$key});
            }
            if (!($this->{$key} instanceof $val['type'])) {
                throw new InvalidConfigException("Property `${$key}` must be specified.");
            }
        }
        if (empty($this->messageCategory)) {
            throw new InvalidConfigException('Property `$messageCategory` must be specified.');
        }
        $this->registerTranslations();
    }
    
    /**
     * Translates a message to the specified language.
     * 
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current of application
     * language.
     * @return string
     */
    static public function t($message, $params = [], $language = null)
    {
        return Yii::t('bupy7/config', $message, $params, $language);
    }
    
    /**
     * Registration of translation class.
     */
    protected function registerTranslations()
    {
        Yii::$app->i18n->translations['bupy7/config'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'forceTranslation' => true,
            'basePath' => '@bupy7/config/messages',
            'fileMap' => [
                'bupy7/config' => 'core.php',
            ],
        ];
        if ($this->translation instanceof MessageSource) {
            Yii::$app->i18n->translations[$this->messageCategory] = Yii::createObject($this->translation);
        } elseif (is_array($this->translation)) {
            Yii::$app->i18n->translations[$this->messageCategory] = $this->translation;
        }
    }
}
