<?php
/**
 * Description：
 *
 * Date: 2020/1/17
 * Email: <xiaoxiaonan@sc-edu.com>
 * author:: xiaoxiaonan
 */
namespace App\Http\Controllers;

use App\Models\Base;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use test\Mockery\MockingVariadicArgumentsTest;
use App\Models\Wechat;
use function Composer\Autoload\includeFile;
use App\Models\User;
use App\Models\Comment;

class UserController extends BaseController
{
    public function getUserInfo(Request $request)
    {
        $name = $request->input('name', '');
        if (!$name) {
            return $this->buildReturn(0, '缺少参数');
        }
        $user = new User();
        $user_info = $user->where('name', $name)->first();

        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在');
        }
        $user_info = $user_info->toArray();
        return $this->buildReturn(1,'成功',['user_info' => $user_info]);
    }
    public function assist(Request $request)
    {
        $name = $request->input('name', '');
        if (!$name) {
            return $this->buildReturn(0, '缺少参数');
        }
        $user = new User();
        $user_info = $user->where('name', $name)->first();

        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在');
	}
	$user->where('name', $name)->update(['assist' => $user_info->assist + 1]);
        return $this->buildReturn(1,'成功');
    }
    public function comment(Request $request)
    {
        $comment = new Comment();
        $list = $comment->get()->toArray();
        $total = $comment->count();

	$data = [];
	for ($i = 0; $i < 10;$i++) {
	    $data[] = $list[mt_rand(0,$total)];
	}
        return $this->buildReturn(1,'成功', $data);
    }
    public function oauth(Request $request)
    {
        $token = $request->input('token', '');
        if ($token != '56506nice') {
            return $this->buildReturn(0, 'token错误');
        }
        $data = [
            'time' => time(),
            'token' => 'xxnlovefmy&dswSb'
        ];
        $token = \Illuminate\Support\Facades\Crypt::encrypt(json_encode($data));
        return $this->buildReturn(1,'授权成功',['token' => $token]);
    }

    /**
     * 微信小程序用户登录
     *
     * @param Request $request
     */
    public function mini_login(Request $request)
    {
        $code = $request->input('code', '');
        if (!$code) {
            return $this->buildReturn(0,'缺少参数code');
        }
        $wx = new Wechat();
        $oauth_info = $wx->oauth($code);

        return $this->buildReturn($oauth_info['state'], $oauth_info['message'], $oauth_info['data']);
    }

    public function adminLogin(Request $request)
    {
        $no = $request->input('no', '');
        $secret = $request->input('secret', '');
        if (!$no) {
            return $this->buildReturn(0, '缺少参数no');
        }
        if (!$secret) {
            return $this->buildReturn(0, '缺少参数secret');
        }
        $user_list = [
            'jhy162' => 'xjyy1703'
        ];

        if (!key_exists($no, $user_list)) {
            return $this->buildReturn(0, '用户不存在');
        }
        $secret_service = md5($no."xxnlovefmy".$user_list[$no]);
        if ($secret != $secret_service) {
            return $this->buildReturn(0, '密码错误');
        }
        $data = [
            'time' => time(),
            'no' => $no,
            'client' => "backend"
        ];
        $token = \Illuminate\Support\Facades\Crypt::encrypt(json_encode($data));
        return $this->buildReturn(1,'授权成功',['token' => $token]);
    }

    public function addUser(Request $request)
    {
        $name = $request->input('name', '');
        $tel = $request->input('tel', '');
        $department = $request->input('department', '');

        if (!$name) {
            return $this->buildReturn(0, '缺少参数name');
        }
        if (!$tel) {
            return $this->buildReturn(0, '缺少参数tel');
        }

        $data = [
            'name' => $name,
            'tel' => $tel,
            'department' => $department,
            'add_time' => date('Y-m-d H:i:s')
        ];
        $user = new User();

        $map = [
            'tel' => $tel,
            'del' => 0
        ];
        $user_info = $user->where($map)->first();
        if ($user_info) {
            return $this->buildReturn(0, '手机号重复');
        }
        $user_id = $user->insertGetId($data);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user');
        }
        return $this->buildReturn(1, '添加成功');
    }

    public function getUserList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $name_key_word = $request->input('name_key_word', '');
        $dep_key_word = $request->input('dep_key_word', '');

        $user = new User();
        $map = [
            'del' => 0
        ];
        $total = $user->where($map);
        if ($name_key_word) {
            $total = $total->where('name', 'like', "%{$name_key_word}%");
        }
        if ($dep_key_word) {
            $total = $total->where('department', 'like', "%{$dep_key_word}%");
        }

        $total = $total->count();
        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $offset = ($page - 1) * $limit;

        $list = $user->where($map);
        if ($name_key_word) {
            $list = $list->where('name', 'like', "%{$name_key_word}%");
        }
        if ($dep_key_word) {
            $list = $list->where('department', 'like', "%{$dep_key_word}%");
        }
        $list = $list->select('id as user_id', 'name', 'department', 'avatar', 'tel')
            ->offset($offset)
            ->limit($limit)
            ->get()->toArray();

        $data = [
            'page' => $page,
            'page_total' => $page_total,
            'total' => $total,
            'list' => $list
        ];
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function editUserInfo(Request $request)
    {
        $name = $request->input('name', 0);
        $department = $request->input('department', 0);
        $tel = $request->input('tel', 0);
        $user_id = $request->input('user_id', 0);

        if (!$user_id) {
            return $this->buildReturn(0,'缺少参数user_id');
        }

        $param = [
            'name' => $name,
            'department' => $department,
            'tel' => $tel,
        ];
        $user = new User();
        $map = [
            'tel' => $tel,
            'del' => 0
        ];
        $user_info = $user->where($map)->where('id', '!=', $user_id)
            ->first();
        if ($user_info) {
            return $this->buildReturn(0, '手机号重复');
        }
        $edit_info = $user->edit($user_id, $param);
        return $this->buildReturn($edit_info['state'], $edit_info['message'], $edit_info['data']);
    }

    public function delUser(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        if (!$user_id) {
            return $this->buildReturn(0, "缺少参数user_id");
        }

        $user = new User();
        $user_info = $user->where('id', $user_id)->where('del', 0)
            ->first();

        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在');
        }

        $data = [
            'del' => 1
        ];
        $user->where('id', $user_id)->update($data);
        return $this->buildReturn(1, '操作成功');
    }

    public function getUserNameList(Request $request)
    {
        $key_word = $request->input('key_word', '');
        $map = [
            'del' => 0
        ];

        $user = new User();
        $list = $user->where($map);
        if ($key_word) {
            $list = $list->where('name', "like", "%{$key_word}%");
        }
        $list = $list->select('id as user_id', 'name')->get()->toArray();
        return $this->buildReturn(1, '数据获取成功', $list);
    }

    public function telBind(Request $request)
    {
        $tel = $request->input('tel', '');
        if (!$tel) {
            return $this->buildReturn(0, '缺少参数tel');
        }

        $o_id = $request->input('o_id', '');
        if (!$o_id) {
            return $this->buildReturn(0, '缺少参数o_id');
        }

        $nick_name = $request->input('nick_name', '');
        $avatar = $request->input('avatar', '');

        $user = new User();
        $user_info = $user->where('del', 0)
            ->where('tel', $tel)
            ->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，请联系系统管理员');
        }
        # 更新用户信息
        $user_data = [
            'open_id' => $o_id
        ];
        if ($nick_name) {
            $user_data['nick_name'] = $nick_name;
        }
        if ($avatar) {
            $user_data['avatar'] = $avatar;
        }

        $user->where('id', $user_info->id)->update($user_data);

        $data = [
            'user_info' => [
                'id' => $user_info->id,
                'name' => $user_info->name,
                'department' => $user_info->department
            ]
        ];
        return $this->buildReturn(1, '绑定手机号成功', $data);
    }
}
