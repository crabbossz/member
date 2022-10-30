<?php
/**
 * 会员控制器
 */

namespace app\admin\controller;

use think\Request;
use app\common\model\Member;

use app\common\validate\MemberValidate;

class MemberController extends Controller
{

    //列表
    public function index(Request $request, Member $model)
    {
        $param = $request->param();
        $model = $model->scope('where', $param);

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
    public function add(Request $request, Member $model, MemberValidate $validate)
    {
        if ($request->isPost()) {
            $param = $request->param();
            $validate_result = $validate->scene('add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            //处理头像上传
            $attachment_avatar = new \app\common\model\Attachment;
            $file_avatar = $attachment_avatar->upload('avatar');
            if ($file_avatar) {
                $param['avatar'] = $file_avatar->url;
            } else {
                return admin_error($attachment_avatar->getError());
            }

            //处理会员码上传
            $attachment_code = new \app\common\model\Attachment;
            $file_code = $attachment_code->upload('code');
            if ($file_code) {
                $param['code'] = $file_code->url;
            } else {
                return admin_error($attachment_code->getError());
            }


            $result = $model::create($param);

            $url = URL_BACK;
            if (isset($param['_create']) && $param['_create'] == 1) {
                $url = URL_RELOAD;
            }

            return $result ? admin_success('添加成功', $url) : admin_error();
        }


        return $this->fetch();
    }

    //修改
    public function edit($id, Request $request, Member $model, MemberValidate $validate)
    {

        $data = $model::get($id);
        if ($request->isPost()) {
            $param = $request->param();
            $validate_result = $validate->scene('edit')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            //处理头像上传
            if (!empty($_FILES['avatar']['name'])) {
                $attachment_avatar = new \app\common\model\Attachment;
                $file_avatar = $attachment_avatar->upload('avatar');
                if ($file_avatar) {
                    $param['avatar'] = $file_avatar->url;
                }
            }

            //处理会员码上传
            if (!empty($_FILES['code']['name'])) {
                $attachment_code = new \app\common\model\Attachment;
                $file_code = $attachment_code->upload('code');
                if ($file_code) {
                    $param['code'] = $file_code->url;
                }
            }


            $result = $data->save($param);
            return $result ? admin_success() : admin_error();
        }

        $this->assign([
            'data' => $data,

        ]);
        return $this->fetch('add');

    }

    //删除
    public function del($id, Member $model)
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
