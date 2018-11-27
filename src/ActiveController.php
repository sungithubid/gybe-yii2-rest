<?php
/**
 * Created by PhpStorm.
 * Date: 2018/11/26
 * Time: 上午11:35
 */

namespace gybe\rest;


use yii\helpers\ArrayHelper;
use yii;

class ActiveController extends \yii\rest\ActiveController
{
    /**
     * 增加addLinks属性，用来添加自定义links地址
     * **使用方法**
     * ```
     * addLinks => [
     *  'direct' => 'https://ip.cn/'
     * ]
     * ```
     * @var array
     */
    public $serializer = [
        'class' => 'gybe\rest\Serializer',
        'collectionEnvelope' => 'items',
        'addLinks' => []
    ];

    public function init()
    {
        parent::init();

        // Re set response components
        Yii::$app->setComponents([
            'response' => [
                'class' => 'yii\web\Response',
                'format' => yii\web\Response::FORMAT_JSON,
                'charset' => 'UTF-8',
                'formatters' => [
                    yii\web\Response::FORMAT_JSON => [
                        'class' => 'yii\web\JsonResponseFormatter',
                        'prettyPrint' => true,
                    ],
                ],
                'on beforeSend' => function ($event) {
                    /* @var $response \yii\web\Response */
                    $response = $event->sender;

                    if (!$response->getIsSuccessful()) {
                        $exception = Yii::$app->getErrorHandler()->exception;

                        if ($exception === null) {
                            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
                            $exception = new yii\web\HttpException(404, 'Page not found.');
                        }

                        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
                            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
                            $exception = new yii\web\HttpException(404, 'Page not found');
                        }

                        if ($exception instanceof yii\web\HttpException) {
                            $status = $exception->statusCode;
                            $message = $exception->getName();
                        } else {
                            $status = $exception->getCode();
                            $message = $exception->getMessage();
                        }

                        $response->data = [
                            'error' => 1,
                            'code' => $status,
                            'http_status' => $status,
                            'message' => $message,
                            'timestamp' => time(),
                        ];

                    } else {
                        $response->data = [
                            'data' => $response->data,
                            'timestamp' => time(),
                        ];
                        $response->statusCode = 200;
                    }
                }
            ],
        ]);
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'class' => 'gybe\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'gybe\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],



        ]);
    }

}