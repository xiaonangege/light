<?php
/**
 * Description：
 *
 * Date: 2020/1/20
 * Email: <xiaoxiaonan@sc-edu.com>
 * author:: xiaoxiaonan
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Reader\Xls\MD5;
use GuzzleHttp;

class ArticleCategory extends Base
{
    protected $table = 'category';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';

    public function getList($page = 1, $limit = 10)
    {
        $map = [
            'del' => 0
        ];
        $offset = ($page - 1) * $limit;

        $article = new Article();

        $total = $this->where($map)->count();

        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $list = $this->where($map)
            ->offset($offset)
            ->limit($limit)
            ->get()->toArray();
        if (!$list) {
            return $this->buildReturn(1, '数据获取成功', []);
        }

        $data = [
            'count' => $total,
            'page_total' => $page_total
        ];
        foreach ($list as $v) {
            $data['list'][] = [
                'id' => $v['id'],
                'title' => $v['title'],
                'add_time' => $v['add_time']
            ];
        }
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function add($title)
    {
        if (!$title) {
            return $this->buildReturn(0, '缺少参数title');
        }
        $category_info = $this->where('del', 0)
            ->where('title', $title)
            ->first();
        if ($category_info) {
            return $this->buildReturn(0, '已经有相同的类型');
        }
        $data = [
            'title' => $title,
            'add_time' => date('Y-m-d H:i:s')
        ];

        $id = $this->insertGetId($data);
        if (!$id) {
            return $this->buildReturn(0, '添加失败');
        }
        return $this->buildReturn(1, '添加成功');
    }

    public function edit($id, $title)
    {
        if (!$id) {
            return $this->buildReturn(0, '缺少参数id');
        }

        if (!$title) {
            return $this->buildReturn(0,'缺少参数title');
        }

        $category_info = $this->where('del', 0)
            ->where('id', $id)
            ->first();
        if (!$category_info) {
            return $this->buildReturn(0, '参数不合法');
        }

        $category_info = $this->where('del', 0)
            ->where('title', $title)
            ->where('id', '!=',$id)
            ->first();
        if ($category_info) {
            return $this->buildReturn(0, '已经有相同的类型');
        }

        $data = [
            'title' => $title
        ];
        $this->where('id', $id)->update($data);
        return $this->buildReturn(1, '修改成功');
    }

    public function deleteCategory($id)
    {
        if (!$id) {
            return $this->buildReturn(0, '缺少参数id');
        }
        if ($id == 1) {
            return $this->buildReturn(0, '该分类不能删除');
        }
        $category_info = $this->where('del', 0)
            ->where('id', $id)
            ->first();
        if (!$category_info) {
            return $this->buildReturn(0, '参数不合法');
        }

        $data = [
            'del' => 1
        ];
        $this->where('id', $id)->update($data);
        return $this->buildReturn(1, '删除成功');
    }
}