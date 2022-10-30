<?php
/**
 * 会员验证器
 */

namespace app\common\validate;

class MemberValidate extends Validate
{
    protected $rule = [
            'nickname|昵称' => 'require',
    'business|业务员' => 'require',

    ];

    protected $message = [
            'nickname.require' => '昵称不能为空',
    'business.require' => '业务员不能为空',

    ];

    protected $scene = [
        'add'  => ['nickname','business',],
'edit' => ['nickname','business',],

    ];

    

}
