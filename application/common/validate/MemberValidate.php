<?php
/**
 * 会员验证器
 */

namespace app\common\validate;

class MemberValidate extends Validate
{
    protected $rule = [
        'nickname|昵称' => 'require',

    ];

    protected $message = [
        'nickname.require' => '昵称不能为空',

    ];

    protected $scene = [
        'add' => ['nickname'],
        'edit' => ['nickname'],

    ];


}
