<?php

namespace app\api\controller;

use app\common\model\Setting;

class IndexController extends ApiBaseController
{
    public function __construct()
    {
        cors_html();
    }

    // index
    public function index()
    {
        return api_success('index');
    }

    // 获取轮播图
    public function banner()
    {
        $id = 2;
        $data = Setting::where('setting_group_id', $id)->select()->toArray()[0]["content"][0]["content"];
        $banner = [
            "banner" => $data
        ];
        return api_success($banner);
    }


}