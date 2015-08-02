<?php

namespace bupy7\config;

use Yii;
use bupy7\config\components\ConfigManager;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * This is module for configuration dynamic parameters of application.
 * @author Belosludcev Vasilij http://mihaly4.ru
 * @since 1.0.0
 */
class Module extends \yii\base\Module
{
    /**
     * @var boolean Whether to enable caching translated messages and configuration parameters.
     */
    public $enableCaching = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }
    
    /**
     * Translates a message to the specified language.
     * 
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current of application
     * language.
     * @return string
     */
    static public function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('bupy7/config/' . $category, $message, $params, $language);
    }
    
    /**
     * Registration of translation class.
     */
    protected function registerTranslations()
    {
        Yii::$app->i18n->translations['bupy7/config/core'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@bupy7/config/messages',
            'fileMap' => [
                'bupy7/config/core' => 'core.php',
            ],
        ];
        Yii::$app->i18n->translations['bupy7/config/params'] = [
            'class' => 'yii\i18n\DbMessageSource',
            'forceTranslation' => true,
            'enableCaching' => $this->enableCaching,
            'on missingTranslation' => function($event) {
                $transaction = $event->sender->db->beginTransaction();
                try {
                    $event->sender->db->createCommand()->insert($event->sender->sourceMessageTable, [
                        'category' => $event->category,
                        'message' => $event->message,
                    ])->execute();
                    $id = $event->sender->db->getLastInsertID();
                    $event->sender->db->createCommand()->insert($event->sender->messageTable, [
                        'id' => $id,
                        'language' => Yii::$app->language,
                        'translation' => $event->message,
                    ])->execute();

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            },
        ];
    }
}
