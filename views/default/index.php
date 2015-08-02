<?php

use yii\helpers\Html;
use bupy7\config\Module;
use bupy7\config\widgets\ActiveForm;

$this->title = Module::t('core', 'SETTINGS');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?></h1>
</div>
<?php
$form = ActiveForm::begin([
    'enableClientScript' => true,
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
    'validateOnChange' => false,
    'validateOnBlur' => false,
]);
?>
<?php foreach ($models as $model) : ?>
    <?= $form->field($model); ?>
<?php endforeach; ?>
<?php
echo Html::submitButton(Module::t('core', 'SAVE'), ['class' => 'btn btn-success']);

ActiveForm::end();