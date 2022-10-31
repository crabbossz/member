<?php
/**
 * 资金变动验证器
 */

namespace app\common\validate;

class FundsChangeValidate extends Validate
{
    protected $rule = [
        'member_id|用户id' => 'require',
        'amount|流动金额' => 'require',
        'description|描述' => 'require',

    ];

    protected $message = [
        'member_id.require' => '用户id不能为空',
        'amount.require' => '流动金额不能为空',
        'description.require' => '描述不能为空',

    ];

    protected $scene = [
        'add' => ['member_id', 'amount', 'description',],
        'edit' => ['member_id', 'amount', 'description',],

    ];


}
