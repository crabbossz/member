<?php
/**
 * 充值设置验证器
 */

namespace app\common\validate;

class RechargeSettingValidate extends Validate
{
    protected $rule = [
            'recharge|充值金额' => 'require',
    'giving|赠送金额' => 'require',

    ];

    protected $message = [
            'recharge.require' => '充值金额不能为空',
    'giving.require' => '赠送金额不能为空',

    ];

    protected $scene = [
        'add'  => ['recharge','giving',],
'edit' => ['recharge','giving',],

    ];

    

}
