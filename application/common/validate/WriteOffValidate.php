<?php
/**
 * 会员核销验证器
 */

namespace app\common\validate;

class WriteOffValidate extends Validate
{
    protected $rule = [
            'member_id|用户' => 'require',
    'change|核销金额' => 'require',

    ];

    protected $message = [
            'member_id.require' => '用户不能为空',
    'change.require' => '核销金额不能为空',

    ];

    protected $scene = [
        'add'  => ['member_id','change',],
'edit' => ['member_id','change',],

    ];

    

}
