<?php

namespace bupy7\config\controllers;

use Yii;
use yii\web\Controller;
use bupy7\config\models\Config;
use yii\base\Model;
use yii\widgets\ActiveForm;
use yii\web\Response;
use bupy7\config\Module;

/**
 * Settings controller of application.
 * 
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class DefaultController extends Controller
{   
    /**
     * @var string Displays parameters from module which set below.
     */
    public $byModule = 'app';
    
    /**
     * Display all config paramters for $byModule.
     * @return mixed
     */
    public function actionIndex()
    {
        $models = Config::find()
            ->where(['module' => $this->byModule])
            ->orderBy(['id' => SORT_ASC])
            ->indexBy('id')
            ->all();
        if (Model::loadMultiple($models, Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validateMultiple($models, 'value');
            }
            $result = true;
            foreach ($models as $model) {
                $result = $result && $model->save(true, ['value']);
            }
            if ($result && Module::getInstance()->configManager->clearCache()) {
                Yii::$app->session->setFlash('success', Module::t('SAVE_SUCCESS'));                
                return $this->redirect(['index']);
            }
        }
        return $this->render('index', [
            'models' => $models,
        ]);
    }   
}
