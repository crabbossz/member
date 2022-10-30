<?php
/**
 * 资金变动模型
 */

namespace app\common\model;

use think\model\concern\SoftDelete;

class FundsChange extends Model
{
    // 自定义选择数据
    use SoftDelete;

    public $softDelete = true;
    protected $name = 'funds_change';
    protected $autoWriteTimestamp = true;

    //可搜索字段
    protected $searchField = [];

    //可作为条件的字段
    protected $whereField = [];

    //可做为时间
    protected $timeField = [];


    //关联会员
    public function member()
    {
        return $this->belongsTo(Member::class);
    }


}
