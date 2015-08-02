<?php

namespace bupy7\config\models;

use Yii;
use yii\db\Query;
use yii\db\ActiveRecord;
use bupy7\config\Module;

/**
 * This is the model class for table "{{%config}}".
 * 
 * @property integer $id 
 * @property string $module
 * @property string $name
 * @property string $label
 * @property string $value
 * @property integer $type
 * @property integer $language
 * @property string $hint
 * @property string $options
 * @property string $rules
 * 
 * @author Vasilij "BuPy7" Belosludcev http://mihaly4.ru
 * @since 1.0.0
 */
class Config extends ActiveRecord
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
    const TYPE_YES_NO = 7;
    
    /**
     * Сonfiguration parameter does not depend on language settings.
     */
    const LANGUAGE_NO = 0;
    /**
     * Configuration paramter depend on 'russian' language settings.
     */
    const LANGUAGE_RU = 1;
    
    /**
     * @var array Languages map.
     */
    static private $_lang = [
        'ru' => self::LANGUAGE_RU,
    ]; 
        
    /**
     * @var array Rules of field.
     * @see rules()
     */
    private $_rules = [];
    
    /**
     * @var type Labels of fields.
     * @see attributeLabels()
     */
    private $_labels = [];
    
    /**
     * @var array Map types.
     */
    static private $_types = [
        self::TYPE_INPUT => 'textInput',
        self::TYPE_TEXT => 'textArea',
        self::TYPE_DROPDOWN => 'dropDownList',
        self::TYPE_CHECKBOX_LIST => 'checkboxList',
        self::TYPE_RADIO_LIST => 'radioList',
        self::TYPE_YES_NO => 'checkbox',
    ];
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->_rules;
    }

    /**
     * @inheritdoc
     */
    static public function tableName()
    {
        return '{{%config}}';
    }
    
    /**
     * Unserialize 'options' field.
     * Unserialize and add rules of field.
     * Add label of fields.
     */
    public function afterFind()
    {
        // unserialize 'options' field
        $options = unserialize($this->options);
        if ($options === false) {
            $options = [];
        } 
        $this->options = $options;
        
        // unserialize and add rules of field
        $rules = unserialize($this->rules);
        if ($rules === false) {
            $rules = [];
        }
        foreach ($rules as &$rule) {
            array_unshift($rule, 'value');
        }
        $this->_rules = $rules;
        
        // add label of fields
        $this->_labels = ['value' => Module::t('params', $this->label)];
        
        parent::afterFind();
    }

    /**
     * Labels of fields.
     * @return array
     */
    public function attributeLabels()
    {
        return $this->_labels;
    }
    
    /**
     * Returned list type. If '$value' is set, then will be returned element with this key.
     * @return mixed
     */
    static public function typeList($value = null)
    {
        if ($value !== null) {
            if (isset(self::$_types[$value])) {
                return self::$_types[$value];
            } else {
                return null;
            }
        }
        return self::$_types;
    }
    
    /**
     * Return configuration map of application.
     * @return array
     */
    static public function getMap()
    {
        $query = (new Query)
            ->select([
                'module', 
                'name', 
                'value',
            ])
            ->from(self::tableName())
            ->where([
                'or', 
                ['language' => self::$_lang[Yii::$app->language]], 
                ['language' => self::LANGUAGE_NO],
            ]);
        $result = [];
        foreach ($query->batch() as $rows) {
            for ($i = 0; $i != count($rows); $i++) {
                $result[$rows[$i]['module']][$rows[$i]['name']] = $rows[$i]['value'];
            }
        }
        return $result;
    }
}
