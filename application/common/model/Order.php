<?php
/**
 * 订单模型
 */

namespace app\common\model;

use think\model\concern\SoftDelete;

class Order extends Model
{
    // 自定义选择数据
    // 订单状态列表
    const ORDER_STATUS_LIST = [
        0 => '未支付',
        1 => '已支付',
    ];


    use SoftDelete;

    public $softDelete = true;
    protected $name = 'order';
    protected $autoWriteTimestamp = true;

    //可搜索字段
    protected $searchField = ["nickname", "mobile"];

    //可作为条件的字段
    protected $whereField = [];

    //可做为时间
    protected $timeField = [];

    //[FORM_NAME]获取器
    public function getOrderStatusNameAttr($value, $data)
    {
        return self::ORDER_STATUS_LIST[$data['order_status']];
    }


    //关联会员
    public function member()
    {
        return $this->belongsTo(Member::class);
    }


}
