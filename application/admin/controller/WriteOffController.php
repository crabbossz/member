<?php
/**
 * 会员核销控制器
 */

namespace app\admin\controller;

use app\common\model\FundsChange;
use think\Db;
use think\Request;
use app\common\model\WriteOff;
use app\common\model\Member;

use app\common\validate\WriteOffValidate;

class WriteOffController extends Controller
{

    //列表
    public function index(Request $request, WriteOff $model)
    {
        $param = $request->param();
        $model = $model->with('member')->scope('where', $param);

        $data = $model->paginate($this->admin['per_page'], false, ['query' => $request->get()]);
        //关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data' => $data,
            'page' => $data->render(),
            'total' => $data->total(),

        ]);
        return $this->fetch();
    }

    //添加
    public function add(Request $request, WriteOff $model, WriteOffValidate $validate)
    {
        if ($request->isPost()) {
            $param = $request->param();
            $validate_result = $validate->scene('add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            Db::startTrans();
            try {
                $member = Member::where("id", $param['member_id'])->find();
                // 创建核销
                $param['admin_user_id'] = $this->user['id'];
                $change = $param['change'];
                $param['change'] = $param['change'] * 100;
                $param['nickname'] = $member['nickname'];
                $param['mobile'] = $member['mobile'];
                $result = $model::create($param);
                // 更改用户金额
                if (($member['balance'] - $param['change']) < 0) {
                    return admin_error("用户余额不足");
                }
                $memberUpdate = [
                    "balance" => $member["balance"] - $param['change']
                ];
                Member::where('id', $member['id'])
                    ->update($memberUpdate);
                // 创建消费记录
                $fundsChangeData = [
                    "member_id" => $member["id"],
                    "amount" => -$param['change'],
                    "mobile" => $member['mobile'],
                    "nickname" => $member['nickname'],
                    "description" => "消费" . $change . "元"
                ];
                FundsChange::create($fundsChangeData);
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                $msg = $e->getMessage();
                Db::rollback();
                return admin_error($msg);
            }
            $url = URL_BACK;
            if (isset($param['_create']) && $param['_create'] == 1) {
                $url = URL_RELOAD;
            }

            return $result ? admin_success('添加成功', $url) : admin_error();
        }

        $this->assign([
            'member_list' => Member::all(),

        ]);


        return $this->fetch();
    }

    //修改
    public function edit($id, Request $request, WriteOff $model, WriteOffValidate $validate)
    {

        $data = $model::get($id);
        if ($request->isPost()) {
            $param = $request->param();
            $validate_result = $validate->scene('edit')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            $result = $data->save($param);
            return $result ? admin_success() : admin_error();
        }

        $this->assign([
            'data' => $data,
            'member_list' => Member::all(),

        ]);
        return $this->fetch('add');

    }

    //删除
    public function del($id, WriteOff $model)
    {
        if (count($model->noDeletionId) > 0) {
            if (is_array($id)) {
                if (array_intersect($model->noDeletionId, $id)) {
                    return admin_error('ID为' . implode(',', $model->noDeletionId) . '的数据无法删除');
                }
            } else if (in_array($id, $model->noDeletionId)) {
                return admin_error('ID为' . $id . '的数据无法删除');
            }
        }

        if ($model->softDelete) {
            $result = $model->whereIn('id', $id)->useSoftDelete('delete_time', time())->delete();
        } else {
            $result = $model->whereIn('id', $id)->delete();
        }

        return $result ? admin_success('操作成功', URL_RELOAD) : admin_error();
    }


}
