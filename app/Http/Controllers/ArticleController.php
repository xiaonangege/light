<?php
/**
 * Description：
 *
 * Date: 2020/1/20
 * Email: <xiaoxiaonan@sc-edu.com>
 * author:: xiaoxiaonan
 */
namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\ArticleCategory;
use App\Models\Article;
use DeepCopy\f006\A;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use test\Mockery\MockingVariadicArgumentsTest;
use App\Models\Wechat;

class ArticleController extends BaseController
{
    public function addArticle(Request $request)
    {
        $type = $request->input('type', 0);
        if (!$type || !in_array($type, [2,1])) {
            return $this->buildReturn(0, 'type 不合法');
        }
        $category_id = 0;
        if ($type == 2) {
            $category_id = $request->input('category_id', 0);
            if (!$category_id) {
                return $this->buildReturn(0, '缺少参数category_id');
            }
            # 判断文章类型是否存在
            $category_info = ArticleCategory::where('id', $category_id)
                ->where('del', 0)->first();
            if (!$category_info) {
                return $this->buildReturn(0, '文章类型不合法');
            }
        }
        $title = $request->input('title', '');
        $content = $request->input('content', '');
        $img = $request->input('img', '');
        if (!$title) {
            return $this->buildReturn(0, '缺少参数title');
        }
        if (!$content) {
            return $this->buildReturn(0, '缺少参数content');
        }

        $data = [
            'type' => $type,
            'category_id' => $category_id,
            'title' => $title,
            'content' => $content,
            'img' => $img,
            'add_time' =>date('Y-m-d H:i:s')
        ];
        $article = new Article();
        $article_id = $article->insertGetId($data);
        if ($article_id) {
            return $this->buildReturn(1, '添加成功', ['article_id' => $article_id]);
        }
        return $this->buildReturn(0, '添加失败');
    }

    public function articleList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $key_word = $request->input('key_word', '');
        $type = $request->input('type', 0);
        $category_id = $request->input('category_id', 0);

        $map = [
            'del' => 0
        ];
        if ($type) {
            if (!in_array($type, [1,2])) {
                return $this->buildReturn(0, 'type不合法');
            }
            if ($type == 2) {
                if ($category_id) {
                    $map['category_id'] = $category_id;
                }
            }
            $map['type'] = $type;
        }
        $offset = ($page - 1) * $limit;

        $article = new Article();

        $total = $article->where($map);
        if ($key_word) {
            $total = $total->where('title', 'like', "%{$key_word}%");
        }
        $total = $total->count();
        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $list = $article->where($map);
        if ($key_word) {
            $list = $list->where('title', 'like', "%{$key_word}%");
        }
        $list = $list
            ->offset($offset)
            ->limit($limit)
            ->select('id as article_id', 'title', 'content', 'type', 'category_id')
            ->get()->toArray();
        $data = [
            'page' => $page,
            'page_total' => $page_total,
            'total' => $total,
            'list' => $list
        ];
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function editArticle(Request $request)
    {
        $id = $request->input('article_id', 0);
        if (!$id) {
            return $this->buildReturn(0, '缺少参数article_id');
        }

        $article = new Article();
        $param = [
            'title' => $request->input('title', ''),
            'content' => $request->input('content', ''),
            'img' => $request->input('img', ''),
            'category_id' => $request->input('category_id', ''),
        ];
        $edit_info = $article->edit($id, $param);
        return $this->buildReturn($edit_info['state'], $edit_info['message'], $edit_info['data']);
    }

    public function delArticle(Request $request)
    {
        $article_id = $request->input('article_id', 0);
        if (!$article_id) {
            return $this->buildReturn(0, '缺少参数article_id');
        }

        $article = new Article();
        $article_info = $article->where('id', $article_id)
            ->where('del', 0)->first();
        if (!$article_info) {
            return $this->buildReturn(0, '文章不存在');
        }

        $data = [
            'del' => 1
        ];
        $article->where('id', $article_id)->update($data);
        return $this->buildReturn(1,'操作成功');
    }
    public function categoryList(Request $request)
    {
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 10);

        $category = new ArticleCategory();
        $list_info = $category->getList($page, $limit);
        return $this->buildReturn($list_info['state'], $list_info['message'], $list_info['data']);
    }

    public function addCategory(Request $request)
    {
        $title = $request->input('title', '');
        if (!$title) {
            return $this->buildReturn(0, '缺少参数title');
        }
        $category = new ArticleCategory();
        $add_info = $category->add($title);
        return $this->buildReturn($add_info['state'], $add_info['message'], $add_info['data']);
    }

    public function editCategory(Request $request)
    {
        $title = $request->input('title', '');
        if (!$title) {
            return $this->buildReturn(0, '缺少参数title');
        }
        $id = $request->input('id', '');
        if (!$id) {
            return $this->buildReturn(0, '缺少参数id');
        }

        $category = new ArticleCategory();
        $update_info = $category->edit($id, $title);
        return $this->buildReturn($update_info['state'], $update_info['message'], $update_info['data']);
    }

    public function deleteCategory(Request $request)
    {
        $id = $request->input('id', '');
        if (!$id) {
            return $this->buildReturn(0, '缺少参数id');
        }
        $article = new Article();
        $article->where('category_id', $id)->where('type', 2)->where('del', 0)
            ->update(['category_id' => 1]);
        $category = new ArticleCategory();
        $del_info = $category->deleteCategory($id);
        return $this->buildReturn($del_info['state'], $del_info['state'], $del_info['message']);
    }

    public function userGetList(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $type = $request->input('type', 1);
        $map = [
            'del' => 0
        ];
        $article = new Article();

        if (!in_array($type, [1,2])) {
            return $this->buildReturn(0, 'type不合法');
        } else {
            if ($type == 2) {
                $category_id = $request->input('category_id', 0);
                if (!$category_id) {
                    return $this->buildReturn(0, '缺少参数category_id');
                }
                $category_info = ArticleCategory::where('del', 0)
                    ->where('id', $category_id)->first();
                if (!$category_info) {
                    return $this->buildReturn(0, 'category_id错误');
                }
                $map['category_id'] = $category_id;
                $list = $article->where($map)->select('id as article_id', 'title', 'type', 'category_id', 'content', 'add_time')
                    ->get()->toArray();
                $data = [
                    'list' => $list
                ];
                return $this->buildReturn(1, '数据获取成功', $data);
            }
        }
        $map['type'] = $type;

        $offset = ($page - 1) * $limit;

        $total = $article->where($map);

        $total = $total->count();
        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $list = $article->where($map);

        $list = $list
            ->offset($offset)
            ->limit($limit)
            ->select('id as article_id', 'title', 'type', 'category_id', 'content', 'add_time')
            ->get()->toArray();
        $data = [
            'page' => $page,
            'page_total' => $page_total,
            'list' => $list
        ];
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function getDetail(Request $request)
    {
        $article_id = $request->input('article_id', '');

        if (!$article_id) {
            return $this->buildReturn(0, '缺少参数article_id');
        }

        $article_info = Article::where('del', 0)
            ->where('id', $article_id)
            ->first();
        if (!$article_info) {
            return $this->buildReturn(0, '文章不存在');
        }

        $article_info = $article_info->toArray();
        $category = new ArticleCategory();
        if ($article_info['type'] == 2) {
            $category = $category->where('id', $article_info['category_id'])->value('title') ? : '';
            $article_info['category_title'] = $category;
        }
        return $this->buildReturn(1, '数据获取成功', $article_info);
    }

    public function search(Request $request)
    {
        $key_word = $request->input('key_word', 0);

        $map = [
            'del' => 0,
        ];
        $article = new Article();
        $list = $article->where($map);
        if ($key_word) {
            $list = $list->where('title', "like", "%{$key_word}%");
        }

        $list = $list->limit(10)
            ->orderBy('add_time')
            ->select("id as article_id", 'title')
            ->get()->toArray();

        return $this->buildReturn(1, '数据获取成功', ['list' => $list]);
    }

    public function getCategoryTitleList(Request $request)
    {
        $key_word = $request->input('key_word' ,'');

        $category = new ArticleCategory();
        $map = [
            'del' => 0
        ];
        $list = $category->where($map);
        if ($key_word) {
            $list = $list->where('title', 'like', "%{$key_word}%");
        }
        $list = $list
            ->limit(10)
            ->select('id as category_id', 'title')
            ->get()->toArray();
        return $this->buildReturn(1, '数据获取成功', ['list' => $list]);
    }
}