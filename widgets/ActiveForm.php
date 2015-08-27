<?php

namespace bupy7\config\widgets;

use bupy7\config\Module;

/**
 * ActiveForm widget for Config module.
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{
    /**
     * Generate field of form with require construction.
     * 
     * @param Model $model The data model.
     * @param string $attribute Attribute name.
     * @param array $options The additional configurations for the field object.
     * @return ActiveField
     */
    public function field($model, $attribute = 'value', $options = [])
    {
        $field = parent::field($model, "[{$model->id}]{$attribute}", $options);
        $field = call_user_func_array([$field, Module::typeList($model->type)], $model->options);
        return $field;
    }
}