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
     *             ->addRule('gender', 'integer')
     *             ->addRule('name', 'string');
     *     },
     * ]
     * ```
     *
     * 2. 把过滤条件作为GET请求的参数 eg. `http://xxx.com?gender=1`
     *
     * @see \yii\data\DataFilter
     */
    public $dataFilter;

    /**
     * 允许排序的参数字段
     * @var array  eg. ['created_at', 'hits']
     */
    public $filterOrder;

    /**
     * 用于筛选，复杂的自定义查询
     *
     * 使用方法如下:
     * 1. 设置返回query对象的回调函数
     * 'buildQuery' => function() {
     *       return call_user_func(['openapi\models\User', 'userList']);
     *  }
     *
     * 2. 实现userList()方法，例如，用户查询可以用gender来筛选
     *   public static function userList()
     *   {
     *       $params = Yii::$app->getRequest()->getQueryParams();
     *       $query = self::find();
     *       if (isset($params['gender'])) {
     *       $query->andWhere(['gender' => (int)$params['gender']]);
     *       }
     *
     *       return $query;
     *    }
     *
     * @var object
     */
    public $buildQuery;


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

        $filter = null;
        if ($this->dataFilter !== null) {
            if (!$this->dataFilter instanceof yii\data\DataFilter) {
                $this->dataFilter = Yii::createObject($this->dataFilter);
            }

            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        // filter
        if ($this->buildQuery !== null && $this->buildQuery instanceof \Closure) {
            $query = Yii::createObject($this->buildQuery);
        } else {
            $query = $modelClass::find();
        }

        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        // sort
        $sortParams = $this->filterOrderParams($requestParams);
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $sortParams,
            ],
        ]);
    }

    /**
     * 过滤排序参数
     * @param $params
     * @return array
     */
    public function filterOrderParams($params)
    {
        if ($this->filterOrder && isset($params['sort'])) {
            foreach ($this->filterOrder as $order) {
                if ($params['sort'] == $order || $params['sort'] == '-' . $order) {
                    return ['sort' => $params['sort']];
                }
            }
        }
        return [];
    }

}