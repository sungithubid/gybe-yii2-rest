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
     * 2. 把过滤条件GET参数 eg. `http:xxx.com?gender=1`
     *
     * @see \yii\data\DataFilter
     */
    public $dataFilter;

    /**
     * 手动指定的过滤属性
     * @var array eg. [['type' => 1], ['status' => 1]]
     */
    public $specifiedFilter;


    /**
     * 指定可筛选的request参数
     * @var array  eg.['gender', 'birthday']
     */
    public $filterParams;


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
        $requestParams = Yii::$app->getRequest()->getQueryParams();
        $filterParams = $this->getFilter();

        $filter = null;
        if ($this->dataFilter !== null) {
            if (!$this->dataFilter instanceof yii\data\DataFilter) {
                $this->dataFilter = Yii::createObject($this->dataFilter);
            }

            if ($this->dataFilter->load($filterParams)) {
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

    /**
     * 获取筛选条件
     * @return array
     */
    public function getFilter()
    {
        $filterArr = [];
        $filter = $this->filterParams();


        if ($filter) {
            $filterData = $filter;
        }
        if ($this->specifiedFilter) {
            $filterData = $this->specifiedFilter;
        }
        if ($filter && $this->specifiedFilter) {
            $filterData = array_merge($filter, $this->specifiedFilter);
        }

        if (isset($filterData)) {
            $filterArr = [
                'filter' => [
                    'and' => $filterData
                ]
            ];
        }

        return $filterArr;
    }

    /**
     * 处理request参数
     * @return array
     */
    public function filterParams()
    {
        $data = [];
        $requestParams = Yii::$app->getRequest()->getQueryParams();
        if ($this->filterParams && is_array($this->filterParams)) {
            foreach ($this->filterParams as $v) {
                if (isset($requestParams[$v])) {
                    $temp[$v] = $requestParams[$v];
                    $data[] = $temp;
                }
            }
        }
        return $data;
    }

}