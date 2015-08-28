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
     * @var array Rules of field.
     * @see rules()
     */
    private $_rules = [];
    /**
     * @var type Label of fields.
     * @see attributeLabels()
     */
    private $_labels = [];
    /**
     * @var type Hint of fields.
     * @see attributeHints()
     */
    private $_hints = [];
    
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
        $this->_labels = ['value' => Yii::t(Module::getInstance()->messageCategory, $this->label)];
        // add hint of fields
        $this->_hints = ['value' => Yii::t(Module::getInstance()->messageCategory, $this->hint)];
        
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->_labels;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return $this->_hints;
    }
    
    /**
     * Return list of parameters.
     * @return array
     */
    static public function paramsArray()
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
                ['language' => Yii::$app->language], 
                ['language' => null],
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
