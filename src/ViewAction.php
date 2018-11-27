<?php
/**
 * Created by PhpStorm.
 * Date: 2018/11/26
 * Time: 上午11:47
 */

namespace gybe\rest;

use yii;

class ViewAction extends Action
{
    /**
     * Displays a model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface the model being displayed
     */
    public function run($id)
    {
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        return $model;
    }

}