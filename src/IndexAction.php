<?php
/**
 * Created by PhpStorm.
 * Date: 2018/11/26
 * Time: 上午11:39
 */

namespace gybe\rest;

use yii\data\ActiveDataProvider;
use yii;

class IndexAction extends Action
{
    public $prepareDataProvider;

    /**
     * @var \yii\data\DataFilter|null 用于构造过滤条件的过滤器
     *
     * 使用方法如下：
     * 1. 设置指定过滤的属性和规则
     * ```php
     * [
     *     'class' => 'yii\data\ActiveDataFilter',
     *     'searchModel' => function () {
     *         return (new \yii\base\DynamicModel(['id' => null, 'name' => null, 'price' => null]))
     *             ->addRule('id', 'integer')
     *             ->addRule('name', 'string');
     *     },
     * ]
     * ```
     *
     * 2. 把过滤条件放到requestBody eg. `{"filter":{"and":[{"id":1}]}}`
     *
     * @see \yii\data\DataFilter
     */
    public $dataFilter;

    /**
     * 手动指定的过滤属性,用于处理关联资源
     * @var array eg. [['gender' => 1], ['status' => 1]]
     */
    public $specifiedFilter;

    public function run()
    {
        return $this->prepareDataProvider();
    }

    /**
     * @return mixed|object
     * @throws yii\base\InvalidConfigException
     */
    protected function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        if ($this->specifiedFilter) {
            $specifiedFilter = [
                'filter' => [
                    'and' => $this->specifiedFilter
                ]
            ];
            if ($requestParams && isset($requestParams['filter'])) {
                if (isset($requestParams['filter']['and'])) {
                    $requestParams['filter']['and'] = array_merge($requestParams['filter']['and'], $this->specifiedFilter);
                }
            } else {
                $requestParams = $specifiedFilter;
            }
        }

        $filter = null;
        if ($this->dataFilter !== null) {
            if (!$this->dataFilter instanceof yii\data\DataFilter) {
                $this->dataFilter = Yii::createObject($this->dataFilter);
            }

            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    Yii::warning($this->dataFilter->errors);
                    return $this->dataFilter;
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        $query = $modelClass::find();
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }

}