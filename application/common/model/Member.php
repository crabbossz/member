<?php
/**
 * 会员模型
 */

namespace app\common\model;

use think\model\concern\SoftDelete;

class Member extends Model
{
    // 自定义选择数据
    use SoftDelete;

    public $softDelete = true;
    protected $name = 'member';
    protected $autoWriteTimestamp = true;

    //可搜索字段
    protected $searchField = [];

    //可作为条件的字段
    protected $whereField = [];

    //可做为时间
    protected $timeField = [];


}
