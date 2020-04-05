<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use GuzzleHttp;

class User extends Base
{
    protected $table = 'user';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';

    public function edit($user_id, $param)
    {
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        $user_info = $this->where('id', $user_id)
            ->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不能存在');
        }

        $data = [];
        if ($param['tel']) {
            $data['tel'] = $param['tel'];
        }
        if ($param['name']) {
            $data['name'] = $param['name'];
        }
        if ($param['department']) {
            $data['department'] = $param['department'];
        }

        if (!$data) {
            return $this->buildReturn(1, '操作成功');
        }
        $this->where('id', $user_id)
            ->update($data);
        return $this->buildReturn(1, '操作成功');
    }
}