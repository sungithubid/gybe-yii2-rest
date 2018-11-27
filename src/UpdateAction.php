<?php
/**
 * Created by PhpStorm.
 * Date: 2018/11/26
 * Time: 下午1:47
 */

namespace gybe\rest;

use yii;

class UpdateAction extends Action
{
    /**
     * 指定更新的字段 eg. ['username', 'phone']
     * @var array
     */
    public $specifiedProperty;

    public function run($id)
    {
        $model = $this->findModel($id);
        $model->load($this->getPutParams(), '');

        if ($model->save() === false && $model->hasErrors()) {
            $errorArr = $model->errors;
            throw new yii\base\UserException(current(current($errorArr)));
        }

        return $model;
    }

    /**
     * put请求时，获取指定更新字段的参数
     * @return array
     * @throws yii\base\InvalidConfigException
     */
    protected function getPutParams()
    {
        $data = $params = Yii::$app->getRequest()->getBodyParams();
        if ($this->specifiedProperty && is_array($this->specifiedProperty)) {
            $data = [];
            foreach ($params as $key => $param) {
                if (in_array($key, $this->specifiedProperty)) {
                    $data[$key] = $param;
                }
            }
        }
        return $data;
    }


}