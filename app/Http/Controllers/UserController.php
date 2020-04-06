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
}
