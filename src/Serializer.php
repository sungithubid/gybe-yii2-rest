<?php
/**
 * Created by PhpStorm.
 * Date: 2018/11/26
 * Time: 下午2:12
 */

namespace gybe\rest;

use yii\web\Link;
use yii;

class Serializer extends \yii\rest\Serializer
{
    /**
     * 添加的links地址
     * @var array
     */
    public $addLinks;

    protected function serializePagination($pagination)
    {
        $link = Link::serialize($pagination->getLinks(true));
        if ($this->addLinks && is_array($this->addLinks)) {
            $link = array_merge($link, $this->addLinks);
        }
        
        return [
            $this->linksEnvelope => $link,
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ],
        ];
    }

}