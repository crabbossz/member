<?php
/**
 * 会员核销控制器
 */

namespace app\admin\controller;

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
        $model  = $model->with('member')->scope('where', $param);
        
        $data = $model->paginate($this->admin['per_page'], false, ['query'=>$request->get()]);
        //关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data'  => $data,
            'page'  => $data->render(),
            'total' => $data->total(),
            
        ]);
        return $this->fetch();
    }

    //添加
    public function add(Request $request, WriteOff $model, WriteOffValidate $validate)
    {
        if ($request->isPost()) {
            $param           = $request->param();
            $validate_result = $validate->scene('add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            
            $result = $model::create($param);

            $url = URL_BACK;
            if(isset($param['_create']) && $param['_create']==1){
               $url = URL_RELOAD;
            }

            return $result ? admin_success('添加成功',$url) : admin_error();
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
            $param           = $request->param();
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
