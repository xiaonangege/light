<?php
/**
 * Created by PhpStorm.
 * User: xiaoxiaonan
 * Date: 2019/7/16
 * Time: 11:15
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use GuzzleHttp;

class Wechat extends Base
{
    # 微信授权地址
    protected $oauth_url = "https://api.weixin.qq.com/sns/jscode2session";

    # 统一下单地址
    protected $unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    # 回调地址
    protected $call_back = 'https://wx.sc-edu.com/nbg/public/callback';

    # 获取支付状态
    protected $pay_status = 'https://api.mch.weixin.qq.com/pay/orderquery';

    /**
     * 用户授权
     *
     * @param $code
     */
    public function oauth($code)
    {

        $app_id = "wxbd1d4a2a7b2d03d2";
        $app_secret = "2a225dddb8de1df11e3974539a6e8a6c";
        # 获取用户授权信息
        $url = $this->oauth_url."?appid=".$app_id."&secret=".$app_secret."&js_code=$code&grant_type=authorization_code";
        $result = json_decode(file_get_contents($url), 1);
        if (array_key_exists('errcode',$result)) {
            return $this->buildReturn(0,'授权失败');
        }
		$user = new User();
		$user_info = $user->where('open_id', $result['openid'])
            ->where('del', 0)
            ->first();
        $data = [
            'o_id' => $result['openid']
        ];
		if (!$user_info) {
            return $this->buildReturn(1,'登录成功', $data);
        }
		$data['user_info'] = [
            'id' => $user_info->id,
            'name' => $user_info->name,
            'department' => $user_info->department
        ];
		return $this->buildReturn(1, '登录成功', $data);
    }

}
