<?php

namespace bupy7\config\components;

use Yii;
use bupy7\config\models\Config;
use bupy7\config\Module;
use Exception;

/**
 * Get config application from table "config".
 * 
 * @author Vasilij "BuPy7" Belosludcev http://mihaly4.ru
 * @since 1.0.0
 */
class ConfigManager extends \yii\base\Component
{
    /**
     * @var array Configuration map of application.
     */
    private $_map = [];

    /**
     * Initialization component.
     * Getting all parameters for the application.
     */
    public function init()
    {
        parent::init();
        $this->_map = Config::getMap();
    }
    
    /**
     * Getting parameter from {@link $data}  on group and name. If such parameter undefined, will throw an exception. 
     * 
     * @param string $module Name of module.
     * @param string $name Name of paramter.
     * @return mixed
     */
    public function get($module, $name)
    {
        if (isset($this->_map[$module][$name])) {
            return $this->_map[$module][$name];
        }
        throw new Exception(Module::t('core', 'PARAMETER_NOT_FOUND', ['module' => $module, 'name' => $name]), 500);
    }
}
