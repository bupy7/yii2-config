<?php

namespace bupy7\config;

use Yii;
use yii\i18n\MessageSource;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\caching\Cache;
use bupy7\config\components\ConfigManager;
use bupy7\config\commands\ManagerController;
use yii\console\Application as ConsoleApplication;
use yii\base\BootstrapInterface;

/**
 * This is module for configuration dynamic parameters of application.
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * Type field of 'textInput' .
     * @see \yii\widgets\ActiveField::textInput()
     */
    const TYPE_INPUT = 1;
    /**
     * Type field of 'textArea'.
     * @see \yii\widgets\ActiveField::textArea()
     */
    const TYPE_TEXT = 2;
    /**
     * Type field of 'dropDownList'.
     * @see \yii\widgets\ActiveField::dropDownList()
     */
    const TYPE_DROPDOWN = 3;
    /**
     * Type field of 'checkboxList'.
     * @see \yii\widgets\ActiveField::checkboxList()
     */
    const TYPE_CHECKBOX_LIST = 4;
    /**
     * Type field of 'radioList'.
     * @see \yii\widgets\ActiveField::radioList()
     */
    const TYPE_RADIO_LIST = 5;
    /**
     * Type field of 'checkbox'.
     * @see \yii\widgets\ActiveField::checkbox()
     */
    const TYPE_YES_NO = 6;
    /**
     * Type field of 'widget'.
     * @see \yii\widgets\ActiveField::widget()
     */
    const TYPE_WIDGET = 7;
    
    /**
     * Сonfiguration parameter does not depend on language settings.
     */
    const LANGUAGE_ALL = '-';
    
    /**
     * @var array|MessageSource Translation message configuration for parameters of config.
     */
    public $i18n;
    /**
     * @var string string translation message file category name of i18n for parameters of config.
     */
    public $messageCategory = 'app';   
    /**
     * @var boolean Whether to enable caching translated messages and configuration parameters.
     */
    public $enableCaching = false;
    /**
     * @var string|array|Cache Cache component that uses for caching of parameters.
     */
    public $cache = 'cache';
    /**
     * @var string|array|ConfigManager Config manager of get parameters.
     */
    public $configManager = 'configManager';
    /**
     * @var array List parameters of config the application.
     * Key 'rules' and 'options' this array which will be serialized to string.
     * 'rules' content array rules for this configuration paramters without field name, only validation properties.
     * All parameters **required** must be content following options:
     *
     *  - `module` *(string)* - Name of module parameter where it will be use (app, 
     *  shop, cart, blog, news and etc.).
     *  - `name` *(string)* - Name of parameter (mainPageTitle, adminEmail and etc.).
     *
     *  > Module name and name must be unique.
     *
     *  - `label` *(string)* - Label of parameter. It must be translation message. More info 
     *  to `Yii::t()`.
     *  - `type` *(integer)* - Type of field (`bupy7\config\Module::TYPE_INPUT`, 
     *  `bupy7\config\Module::TYPE_TEXT` and etc). Allowed type field you can see to 
     *  `bupy7\config\Module`.
     *  - `rules` *(array)* - Rules of field. All rules must be specified without field name.
     *  Example: 
     *  ```php
     *  'rules' => [
     *      ['required'],
     *      ['string', 'max' => 255],
     *  ], 
     *  ```
     *  More info to `bupy7\config\models\Config::afterFind()`. 
     *
     *  Additional options:
     * - `language` *(string)* - Language for which this config parameter will be uses ('ru', 'en' and etc). 
     * If language is `bupy7\config\Module::LANGUAGE_ALL` or not set, then this parameter will be uses for all 
     * languages. More info `yii\console\Application::$language|yii\web\Application::$language`.
     *  - `value` *(string)* -  Value of config parameter.
     *  - `options` *(array)* - Options depend of field type. More info to 
     *  `bupy7\config\widgets\ActiveForm::field()`.
     * Example for ```textInput``` type:
     * ```php
     * 'options' => [
     *     ['maxlength' => true]
     * ],
     * ```
     *  - `hint` *(string)* - Hint of field. It must be translation message. More info 
     *  to `Yii::t()`.
     */
    public $params = [
        [
            'module' => 'app',                          // module name where uses this parameter
            'name' => 'enable',                         // unique name of parameters
            'label' => 'PARAM_ENABLE',                  // label
            'value' => '1',                             // value
            'type' => self::TYPE_YES_NO,                // type of field
            'language' => self::LANGUAGE_ALL,           // for multilanguage application
            'rules' => [                                // rules of field
                ['boolean'],
            ],
            'options' => [                              // field options
                ['maxlength' => true]
            ],
            'hint' => 'HINT_PARAM_DISPLAY_SITENAME',    // hint
        ],
    ];   
    /**
     * @var string Table name of configuration parameters.
     * @see 1.0.3
     */
    public $tableName = '{{%config}}';
    
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
     * @inheritdoc
     * @since 1.0.2
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApplication) {
            $app->controllerMap[$this->id] = [
                'class' => ManagerController::className(),
            ];
        }
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
     * Returned list types or item.
     * @param integer $key Whether is set, then will be returned element with this key.
     * @return mixed
     */
    static public function typeList($key = null)
    {
        $items = [
            self::TYPE_INPUT => 'textInput',
            self::TYPE_TEXT => 'textArea',
            self::TYPE_DROPDOWN => 'dropDownList',
            self::TYPE_CHECKBOX_LIST => 'checkboxList',
            self::TYPE_RADIO_LIST => 'radioList',
            self::TYPE_YES_NO => 'checkbox',
            self::TYPE_WIDGET => 'widget',
        ];
        return self::findByKey($items, $key);
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
        if ($this->i18n instanceof MessageSource) {
            Yii::$app->i18n->translations[$this->messageCategory] = Yii::createObject($this->i18n);
        } elseif (is_array($this->i18n)) {
            Yii::$app->i18n->translations[$this->messageCategory] = $this->i18n;
        }
    }
    
    /**
     * Find by key for list of items.
     * @param array $items List of items.
     * @param mixed $key Key of item.
     * @return mixed
     */
    static protected function findByKey($items, $key)
    {
        if ($key !== null) {
            if (isset($items[$key])) {
                return $items[$key];
            } else {
                return null;
            }
        }
        return $items;
    }
}
