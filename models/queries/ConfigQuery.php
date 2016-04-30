<?php

namespace bupy7\config\models\queries;

use Yii;
use yii\db\ActiveQuery;

/**
 * @inheritdoc
 * @author Belosludcev Vasilij <https://github.com/bupy7>
 * @since 1.0.4
 */
class ConfigQuery extends ActiveQuery
{
    /**
     * Find by module name..
     * @param stirng $module The module name.
     * @return ActiveQuery
     */
    public function byModule($module)
    {
        return $this->andWhere(['module' => $module]);
    }
    
    /**
     * Find by name of parameter.
     * @param string $name The name of parameter.
     * @return ActiveQuery
     */
    public function byName($name)
    {
        return $this->andWhere(['name' => $name]);
    }
}
