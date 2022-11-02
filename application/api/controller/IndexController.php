<?php

namespace app\api\controller;

use app\common\model\FundsChange;
use app\common\model\Member;
use app\common\model\MemberLevel;
use app\common\model\Order;
use app\common\model\RechargeSetting;
use app\common\model\Setting;
use EasyWeChat\Factory;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use phone\wxBizDataCrypt;
use think\Db;
use think\Model;
use think\Request;

class IndexController
{

    /**
     * @var Request
     */
    protected $request;

    //当前页码
    protected $page;

    //每页数据量
    protected $limit;

    //当前请求的参数，get/post都在其中
    protected $param;

    //公众号配置
    protected $config;

    //微信支付配置
    protected $payConfig;


    public function __construct(Request $request)
    {
        cors_html();

        $this->request = $request;

        // 初始化基本数据
        $this->param = $request->param();

        $this->page = $this->param['page'] ?? 1;
        $this->limit = $this->param['limit'] ?? 10;
        $this->limit = $this->limit <= 100 ? $this->limit : 100;

        $this->config = [
            'app_id' => 'wx802e3ed9f3bbbcdc',
            'secret' => '097a3d5e1c96e3c407d387acd51dc1c4',
        ];

        // 微信支付参数
        $this->payConfig = [
            // 必要配置
            'app_id' => 'wx802e3ed9f3bbbcdc',
            'mch_id' => '1633844167',
            'key' => 'hu8y7yh3bfolu7ytgbedsdefcw3ed8ed',   // API v2 密钥 (注意: 是v2密钥 是v2密钥 是v2密钥)
            'notify_url' => config('app.app_host') . '/api/index/wx_notify',     // 你也可以在下单时单独设置来想覆盖它
        ];

    }


    // index
    public function index()
    {
        return api_success('index');
    }

    // 获取会员卡轮播
    public function banner()
    {
        $id = 2;
        $data = Setting::where('setting_group_id', $id)->select()->toArray()[0]["content"][0]["content"];
        $banner = [
            "banner" => config('app.app_host') . $data
        ];
        return api_success($banner);
    }

    // 会员说明
    public function description()
    {
        $id = 2;
        $data = Setting::where('setting_group_id', $id)->select()->toArray()[0]["content"][1]["content"];
        $replace = config('app.app_host') . '/uploads/ueditor';
        $search = '/uploads/ueditor';
        $data = str_ireplace($search, $replace, $data);
        $banner = [
            "description" => $data
        ];
        return api_success($banner);
    }

    // 充值协议
    public function agreement()
    {
        $id = 2;
        $data = Setting::where('setting_group_id', $id)->select()->toArray()[0]["content"][2]["content"];
        $replace = config('app.app_host') . '/uploads/ueditor';
        $search = '/uploads/ueditor';
        $data = str_ireplace($search, $replace, $data);
        $banner = [
            "agreement" => $data
        ];
        return api_success($banner);
    }

    // 获取openid
    public function getOpenId()
    {

        $code = $this->param["code"];
        $app = Factory::miniProgram($this->config);
        //获取userid
        $data = $app->auth->session($code);
        return api_success($data);
    }

    // 获取用户手机号
    public function getUserPhone()
    {
        $param = $this->request->param(false);
        $appid = $this->config['app_id'];
        $sessionKey = $param['session_key'];
        $encryptedData = $param['encryptedData'];
        $iv = $param['iv'];
        $pc = new wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) {
            return api_error();
        }
        $data = json_decode($data);
        return api_success($data);
    }

    // 获取用户信息
    public function getUserInfo()
    {
        $user = Member::where('openid', '=', $this->param['openid'])->find();
        if (!$user) {
            return api_error();
        }
        $user["integral"] = $user["integral"] / 100;
        $user["balance"] = $user["balance"] / 100;
        $user["code"] = config('app.app_host') . $user["code"];
        $userLevel = MemberLevel::where('id', $user['member_level_id'])->find();
        $user["level"] = $userLevel['name'];
        $user["level_image"] = config('app.app_host') . $userLevel['img'];
        return api_success($user);
    }

    // 创建用户
    public function createUser()
    {
        $param = $this->request->param(false);
        $avatar = $param['avatar'];
        $openid = $param['openid'];
        $nickname = $param['nickname'];
        $mobile = $param['mobile'];
        $permitted_chars = '0123456789abcdefghjkmnpqrstuvwxy';
        $codeValue = substr(str_shuffle($permitted_chars), 0, 8);

        $url = $this->generate($codeValue, $codeValue);
        // birthday
        $user = Member::where('openid', '=', $openid)->find();
        if ($user) {
            Member::where('mobile', '=', $param['mobile'])
                ->update([
                    'avatar' => $avatar,
                    'nickname' => $nickname,
                ]);
            return api_success();
        }
        // 判断
        $user = new Member([
            'avatar' => $avatar,
            'openid' => $openid,
            'mobile' => $mobile,
            'nickname' => $nickname,
            'code_value' => $codeValue,
            'code' => $url
        ]);
        $user->save();
        return api_success();
    }

    // 获取充值设置
    public function getRechargeSetting()
    {
        $data = RechargeSetting::order("sort asc")->all()->toArray();
        foreach ($data as $k => $v) {
            $data[$k]["recharge"] = $v["recharge"] / 100;
            $data[$k]["giving"] = $v["giving"] / 100;
        }
        return api_success($data);
    }

    // 充值
    public function recharge()
    {
        $rechargeSetting = RechargeSetting::where("id", $this->param['rechargeSettingId'])->find();
        $member = Member::where("openid", $this->param['openid'])->find();
        Db::startTrans();
        try {
            // 创建订单
            $out_trade_no = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . rand(1000, 9999);
            $data = [
                "order_no" => $out_trade_no,
                "member_id" => $member['id'],
                "recharge" => $rechargeSetting["recharge"],
                "giving" => $rechargeSetting["giving"],
                "mobile" => $member['mobile'],
                "nickname" => $member['nickname'],
            ];
            Order::create($data);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $msg = $e->getMessage();
            Db::rollback();
            return api_error($msg);
        }

        $app = Factory::payment($this->payConfig);

        $result = $app->order->unify([
            'body' => "会员卡充值",
            'out_trade_no' => $out_trade_no,
            'total_fee' => $rechargeSetting['recharge'],
            'notify_url' => $this->payConfig["notify_url"],
            'trade_type' => 'JSAPI',
            'openid' => $this->param['openid'],
        ]);

        if ($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS') {
            $prepayId = $result['prepay_id'];
            $jssdk = $app->jssdk;
            $config = $jssdk->sdkConfig($prepayId);
            $config['timeStamp'] = $config['timestamp'];
            unset($config['timestamp']);
        } else {
            return api_error();
        }

        return api_success($config);
    }

    // 资金变动记录
    public function changeRecord()
    {
        $member_id = Member::where("openid", $this->param['openid'])->value('id');
        $data = FundsChange::where("member_id", $member_id)
            ->order("id desc")
            ->page($this->page, $this->limit)
            ->select()
            ->toArray();
        foreach ($data as $k => $v) {
            $data[$k]['type'] = false;
            if ($v['amount'] > 0) {
                $data[$k]['type'] = true;
            }
            $amount = $v['amount'] / 100;
            $data[$k]['amount'] = $amount > 0 ? "+" . $amount : $amount . "";
            $data[$k]['current'] = $v['current'] / 100;
        }
        return api_success($data);
    }

    // 充值回调
    public function wx_notify()
    {
        $payment = Factory::payment($this->payConfig);

        $result = $payment->handlePaidNotify(function ($message, $fail) {
            if ($message['return_code'] === 'SUCCESS' && $message['result_code'] === 'SUCCESS') {
                Db::startTrans();
                try {
                    $order = new Order();
                    $order = $order->where('order_no', $message['out_trade_no'])
                        ->find();
                    if ($order["order_status"] == 0) {
                        // 更改订单状态
                        Order::where('order_no', $message['out_trade_no'])
                            ->update(['order_status' => 1]);
                        // 更改用户信息
                        $member = Member::where("id", $order["member_id"])->find();
                        $memberUpdate = [
                            "integral" => $member["integral"] + $order["recharge"],
                            "balance" => $member["balance"] + $order["recharge"] + $order["giving"]
                        ];
                        Member::where('id', $member['id'])
                            ->update($memberUpdate);
                        // 添加资金变动
                        $recharge = $order["recharge"] / 100;
                        $giving = $order["giving"] / 100;
                        $fundsChangeData = [
                            "member_id" => $member["id"],
                            "amount" => $order["recharge"] + $order["giving"],
                            "mobile" => $member['mobile'],
                            "nickname" => $member['nickname'],
                            "current" => $member["balance"] + $order["recharge"] + $order["giving"],
                            "description" => "充值" . $recharge . "元,赠送" . $giving . "元"
                        ];
                        FundsChange::create($fundsChangeData);
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
                return true;
            } else {
                return $fail('失败');
            }
        });
        return $result;
    }

    // 创建二维码
    public function generate($data = 'data', $fileName = 'qrcode')
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $result = $writer->write($qrCode);
        $path = config('attachment.path') . 'qr-code/' . $fileName . '.png';
        $url = config('attachment.url') . 'qr-code/' . $fileName . '.png';
        $result->saveToFile($path);
        return $url;
    }

    public function update()
    {
        $member = Member::all();
        foreach ($member as $k => $v) {
            $permitted_chars = '0123456789';
            $codeValue = substr(str_shuffle($permitted_chars), 0, 10);
            $url = $this->generate($codeValue, $codeValue);
            Member::where('id', '=', $v['id'])
                ->update([
                    'code' => $url,
                    'code_value' => $codeValue,
                ]);
        }
    }
}