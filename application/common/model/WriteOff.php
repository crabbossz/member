<?php
/**
 * 会员核销模型
 */

namespace app\common\model;

use app\admin\model\AdminUser;
use think\model\concern\SoftDelete;

class WriteOff extends Model
{
    // 自定义选择数据
    use SoftDelete;

    public $softDelete = true;
    protected $name = 'write_off';
    protected $autoWriteTimestamp = true;

    //可搜索字段
    protected $searchField = ['nickname', 'mobile'];

    //可作为条件的字段
    protected $whereField = [];

    //可做为时间
    protected $timeField = [];


    //关联会员
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function admin()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
