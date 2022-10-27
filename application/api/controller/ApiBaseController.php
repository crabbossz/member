<?php
namespace app\api\controller;

use think\Request;

class ApiBaseController
{
    //当前页码
    protected $page;

    //每页数据量
    protected $limit;

    /**
     * @var Request
     */
    protected $request;


    //当前请求的参数，get/post都在其中
    protected $param;

    //当前请求数据的ID
    protected $id;

    public function __construct(Request $request)
    {
        $this->request = $request;

        // 初始化基本数据
        $this->param = $request->param();
        $this->page  = $this->param['page'] ?? 1;
        $this->limit = $this->param['limit'] ?? 10;
        $this->id    = $this->param['id'] ?? 0;

        // limit防止过大处理
        $this->limit = $this->limit <= 100 ? $this->limit : 100;
    }
}
