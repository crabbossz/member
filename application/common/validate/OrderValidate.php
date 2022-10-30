<?php
/**
 * 订单验证器
 */

namespace app\common\validate;

class OrderValidate extends Validate
{
    protected $rule = [
            'order_no|订单号' => 'require',
    'member_id|会员' => 'require',
    'recharge|充值金额' => 'require',
    'giving|赠送金额' => 'require',

    ];

    protected $message = [
            'order_no.require' => '订单号不能为空',
    'member_id.require' => '会员不能为空',
    'recharge.require' => '充值金额不能为空',
    'giving.require' => '赠送金额不能为空',

    ];

    protected $scene = [
        'add'  => ['order_no','member_id','recharge','giving',],
'edit' => ['order_no','member_id','recharge','giving',],

    ];

    

}
