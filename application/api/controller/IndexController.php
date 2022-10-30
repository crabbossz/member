<?php

namespace app\api\controller;

use app\common\model\FundsChange;
use app\common\model\Member;
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

class IndexController extends ApiBaseController
{
    //公众号配置
    protected $config;
    //微信支付配置
    protected $payConfig;

    public function __construct()
    {
        cors_html();
        $this->config = [
            'app_id' => 'wx3cf0f39249eb0exx',
            'secret' => 'f1c242f4f28f735d4687abb469072axx',
        ];

        // 微信支付参数
        $this->payConfig = [
            // 必要配置
            'app_id' => 'wx3cf0f39249eb0exx',
            'mch_id' => '1627663237',
            'key' => 'e8ujh4y7yhbg5trytfv6789y6tgrftgd',   // API v2 密钥 (注意: 是v2密钥 是v2密钥 是v2密钥)
            'notify_url' => 'https://pay.easychip.net/api/order/wx_notify',     // 你也可以在下单时单独设置来想覆盖它
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
            "banner" => $data
        ];
        return api_success($banner);
    }

    // 获取openid
    public function getOpenId()
    {
        $code = $this->param['code'] ?? "";
        $app = Factory::miniProgram($this->config);
        //获取userid
        $data = $app->auth->session($code);
        return api_success($data);
    }

    // 获取openid
    public function getOpenId2()
    {
        $param = $this->request->param(false);
        $code = $param['code'];
        $app_id = 'wx3cf0f39249eb0exx';
        $app_secret = 'f1c242f4f28f735d4687abb469072axx';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $app_id . "&secret=" . $app_secret . "&js_code=" . $code . "&grant_type=authorization_code";

        function httpRequest($url, $data = null)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }

        $result = httpRequest($url);
        return api_success(json_decode($result));
    }

    // 获取用户手机号
    public function getUserPhone()
    {
        $param = $this->request->param(false);
        $appid = 'wx3cf0f39249eb0exx';
        $sessionKey = $param['session_key'];
        $encryptedData = $param['encryptedData'];
        $iv = $param['iv'];
        $pc = new wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) {
            return error();
        }
        $data = json_decode($data);
        return api_success($data);
    }

    // 获取用户信息
    public function getUserInfo()
    {
        $user = Member::where('openid', '=', $this->param['openid'])->find();
        $user["integral"] = $user["integral"] / 100;
        $user["balance"] = $user["balance"] / 100;
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
        $url = $this->generate($openid, $openid);
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
            '$openid' => $openid,
            'mobile' => $mobile,
            'nickname' => $nickname,
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
//        $member = Member::where("id", $this->param['memberId'])->find();
        Db::startTrans();
        try {
            // 创建订单
            $out_trade_no = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . rand(1000, 9999);
            $data = [
                "order_no" => $out_trade_no,
                "member_id" => $this->param['memberId'],
                "recharge" => $rechargeSetting["recharge"],
                "giving" => $rechargeSetting["giving"],
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
                    if ($order["payment_status"] == 0) {
                        // 更改订单状态
                        Order::where('order_no', $message['out_trade_no'])
                            ->update(['payment_status' => 1]);
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
                            "description" => "充值" . $recharge . "元,赠送" . $giving . "元"
                        ];
                        FundsChange::create($fundsChangeData);
                    }
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
}