<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use GuzzleHttp;

class Article extends Base
{
    protected $table = 'article';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';

    public function edit($id, $param)
    {
        if (!$id) {
            return $this->buildReturn(0, '缺少参数');
        }

        # 判断文章是否存在
        $article_info = $this->where('id', $id)
            ->where('del', 0)
            ->first();
        if (!$article_info) {
            return $this->buildReturn(0,'文章不存在');
        }

        if ($param['category_id']) {
            if (!$article_info->type == 2) {
                return $this->buildReturn(0, '工作流程不能修改类型');
            }
        }
        # 判断修改后的title是否重复

        $data = [];
        if ($param['title']) {
            $article_info = $this->where('title', $param['title'])
                ->where('del', 0)
                ->where('id', '!=', $id)
                ->first();
            if ($article_info) {
                return $this->buildReturn(0, '文章名称重复');
            }
            $data['title'] = $param['title'];
        }
        if ($param['content']) {
            $data['content'] = $param['content'];
        }
        if ($param['img']) {
            $data['img'] = $param['img'];
        }
        if ($param['category_id']) {
            $data['category_id'] = $param['category_id'];
        }
        if (!$data) {
            return $this->buildReturn(1, '修改成功');
        }

        $this->where('id', $id)->update($data);
        return $this->buildReturn(1, '修改成功');
    }
}