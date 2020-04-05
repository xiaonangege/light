<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Budget;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use test\Mockery\MockingVariadicArgumentsTest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;


class BudgetController extends BaseController
{
    public function commit(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        # 判断用户是否存在
        $user  = new User();
        $user_info = $user->where('del', 0)
            ->where('id', $user_id)->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，信息提交错误');
        }

        $title = $request->input('title', '');
        $param_list = ['title', 'no', 'abbreviation', 'funding_sources', 'charge', 'department', 'cycle', 'tel', 'budget', 'final_accounts'];
        $param = [
            'user_name' => $user_info->name,
            'user_department' => $user_info->department,
            'add_time' => date('Y-m-d H:i:s'),
            'user_id' => $user_id
        ];
        foreach ($param_list as $v) {
            $param[$v] = $request->input($v, '');
            if (!$param[$v]) {
                return $this->buildReturn(0, '缺少参数'.$v);
            }
        }

        $budget = new Budget();
        $budget_id = $budget->insertGetId($param);
        if ($budget_id) {
            return $this->buildReturn(1, '添加成功', ['budget_id' => $budget_id]);
        }
        return $this->buildReturn(0, '添加失败');
    }

    public function saveForm(Request $request)
    {
        $budget_id = $request->input('budget_id', 0);
        if (!$budget_id) {
            return $this->buildReturn(0, '缺少参数budget_id');
        }
        $user_id = $request->input('user_id', 0);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        $content = $request->input('content', 0);
        if (!$content) {
            return $this->buildReturn(0, '缺少参数content');
        }

        # 判断用户是否存在
        $user  = new User();
        $user_info = $user->where('del', 0)
            ->where('id', $user_id)->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，信息获取错误');
        }

        $budget = new Budget();
        $budget_info = $budget->where('id', $budget_id)
            ->where('del', 0)
            ->first();
        if (!$budget_info) {
            return $this->buildReturn(0, '表单不存在');
        }

        $budget_info = $budget_info->toArray();
        if ($budget_info['state'] != 0) {
            return $this->buildReturn(0, '提交审核状态无法编辑');
        }
        $form_data = [
            'content_temporary' => $content,
            'update_time' => date('Y-m-d H:i:s'),
        ];
        # 保存表单内容
        $form_info = DB::table('form')->where('budget_id', $budget_id)->where('del', 0)->first();
        if ($form_info) {
            DB::table('form')->where('id', $form_info->id)->update($form_data);
        } else {
            $form_data['budget_id'] = $budget_id;
            $form_data['add_time'] =  date('Y-m-d H:i:s');
            DB::table('form')->insert($form_data);
        }
        return $this->buildReturn(1, '操作成功');
    }

    public function commitForm(Request $request)
    {
        $budget_id = $request->input('budget_id', 0);
        if (!$budget_id) {
            return $this->buildReturn(0, '缺少参数budget_id');
        }
        $user_id = $request->input('user_id', 0);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        $content = $request->input('content', 0);
        if (!$content) {
            return $this->buildReturn(0, '缺少参数content');
        }

        # 判断用户是否存在
        $user  = new User();
        $user_info = $user->where('del', 0)
            ->where('id', $user_id)->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，信息获取错误');
        }

        $budget = new Budget();
        $budget_info = $budget->where('id', $budget_id)
            ->where('del', 0)
            ->first();
        if (!$budget_info) {
            return $this->buildReturn(0, '表单不存在');
        }
        $budget_info = $budget_info->toArray();
        if ($budget_info['state'] != 0) {
            return $this->buildReturn(0, '提交审核状态无法编辑');
        }
        
        $form_data = [
            'content_temporary' => $content,
            'content' => $content,
            'update_time' => date('Y-m-d H:i:s'),
        ];
        # 更新表单状态
        $budget_data = [
            'state' => 1,
            'update_time' => date('Y-m-d H:i:s'),
        ];
        $budget->where('id', $budget_id)->update($budget_data);
        # 保存表单内容
        $form_info = DB::table('form')->where('budget_id', $budget_id)->where('del', 0)->first();
        if ($form_info) {
            DB::table('form')->where('id', $form_info->id)->update($form_data);
        } else {
            $form_data['budget_id'] = $budget_id;
            $form_data['add_time'] =  date('Y-m-d H:i:s');
            DB::table('form')->insert($form_data);
        }
        return $this->buildReturn(1, '操作成功');
    }
    public function getUserCommitList(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        # 判断用户是否存在
        $user  = new User();
        $user_info = $user->where('del', 0)
            ->where('id', $user_id)->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，信息获取错误');
        }

        $map = [
            'user_id' => $user_id,
            'del' => 0
        ];
        $budget = new Budget();
        $total = $budget->where($map)->count();
        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $offset = ($page - 1) * $limit;
        $list =  $budget->where($map)
            ->offset($offset)
            ->limit($limit)
            ->select('id as budget', 'title', 'add_time', 'state')
            ->get()->toArray();

        $data = [
            'list' => $list,
            'count' => $total,
            'page_total' => $page_total
        ];
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function getDetail(Request $request)
    {
        $budget_id = $request->input('budget_id', 0);
        if (!$budget_id) {
            return $this->buildReturn(0, '缺少参数budget_id');
        }
        $user_id = $request->input('user_id', 0);
        if (!$user_id) {
            return $this->buildReturn(0, '缺少参数user_id');
        }
        # 判断用户是否存在
        $user  = new User();
        $user_info = $user->where('del', 0)
            ->where('id', $user_id)->first();
        if (!$user_info) {
            return $this->buildReturn(0, '用户不存在，信息获取错误');
        }

        $budget = new Budget();
        $budget_info = $budget->where('id', $budget_id)
            ->where('del', 0)
            ->first();
        if (!$budget_info) {
            return $this->buildReturn(0, '表单不存在');
        }
        $budget_info = $budget_info->toArray();
        if ($budget_info['user_id'] != $user_id) {
            return $this->buildReturn(0, '该表单无权限查看');
        }
        # 获取预算表单列表
        $form_info = DB::table('form')->where('del', 0)->where('budget_id', $budget_id)->select('content_temporary')->first();

        if ($form_info) {
            $budget_info['content'] = $form_info->content_temporary;
        } else {
            $budget_info['content'] = '';
        }
        unset($budget_info['del']);
        unset($budget_info['del_time']);
        unset($budget_info['update_time']);
        return $this->buildReturn(1, '', ['budget_info' => $budget_info]);
    }

    public function getBudgetDetail(Request $request)
    {
        $budget_id = $request->input('budget_id', 0);
        if (!$budget_id) {
            return $this->buildReturn(0, '缺少参数budget_id');
        }

        $budget = new Budget();
        $budget_info = $budget->where('id', $budget_id)
            ->where('del', 0)
            ->first();
        if (!$budget_info) {
            return $this->buildReturn(0, '表单不存在');
        }
        $budget_info = $budget_info->toArray();

        unset($budget_info['del']);
        unset($budget_info['del_time']);
        unset($budget_info['update_time']);
        return $this->buildReturn(1, '', ['budget_info' => $budget_info]);
    }

    public function getBudgetList(Request $request)
    {
        $key_word = $request->input('key_word', 0);
        $name_key_word = $request->input('name_key_word', 0);
        $user_id = $request->input('user_id');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $map = [
            'del' => 0
        ];
        if ($user_id) {
            $map['user_id'] = $user_id;
        }

        $budget = new Budget();
        $total = $budget->where($map);
        if ($key_word) {
            $total = $total->where('title', 'like', "%{$key_word}%");
        }
        if ($name_key_word) {
            $total = $total->where('user_name', 'like', "%{$name_key_word}%");
        }
        $total = $total->count();
        $page_total = intval($total / $limit);
        $page_total = ($total % $limit) > 0 ? $page_total + 1 : $page_total;

        $offset = ($page - 1) * $limit;

        $list = $budget->where($map);
        if ($key_word) {
            $list = $list->where('title', 'like', "%{$key_word}%");
        }
        if ($name_key_word) {
            $list = $list->where('user_name', 'like', "%{$name_key_word}%");
        }
        $list = $list->offset($offset)->limit($limit)
            ->select('id as budget_id', 'title', 'user_id', 'add_time', 'user_name', "user_department")
            ->get()->toArray();

        $data = [
            'count' => $total,
            'page_total' => $page_total,
            'list' => $list
        ];
        return $this->buildReturn(1, '数据获取成功', $data);
    }

    public function exportExcel(Request $request)
    {
        $budget_id = $request->input('budget_id', 0);
        if (!$budget_id) {
            return $this->buildReturn(0, '缺少参数budget');
        }

        $budget = new Budget();
        $budget_info = $budget->where('id', $budget_id)->first();
        if (!$budget_info) {
            return $this->buildReturn(0, '预算信息不存在');
        }

        $budget_info = $budget_info->toArray();
        $file_name = $budget_info['title']."-决算预算申请表".time().".xlsx";
        $this->export($file_name, $budget_info);
    }
    public function export($fileName, $data)
    {

        // 告诉浏览器输出07Excel文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// 告诉浏览器输出浏览器名称
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
// 禁止缓存
        header('Cache-Control: max-age=0');

        $style_array = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('sheet1');
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A3:H3');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('E2:H2');
        $sheet->mergeCells('B4:D4');
        $sheet->mergeCells('B5:D5');
        $sheet->mergeCells('B6:D6');
        $sheet->mergeCells('B7:D7');
        $sheet->mergeCells('B8:D8');
        $sheet->mergeCells('F4:H4');
        $sheet->mergeCells('F5:H5');
        $sheet->mergeCells('F6:H6');
        $sheet->mergeCells('F7:H7');
        $sheet->mergeCells('F8:H8');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('黑体')
            ->setSize(20);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setName('宋体')
            ->setSize(14);
        $sheet->getStyle('A4:A8')->getFont()->setBold(true)->setName('宋体')
            ->setSize(12);
        $sheet->getStyle('E4:E8')->getFont()->setBold(true)->setName('宋体')
            ->setSize(12);
        $sheet->getStyle('B4:B8')->getFont()->setName('宋体')
            ->setSize(12);
        $sheet->getStyle('F4:F8')->getFont()->setName('宋体')
            ->setSize(12);

        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->getStyle('A4:F8')->applyFromArray($styleArray);

        $template = [
            [
                'col' => 1,
                'row' => 4,
                'value' => "项目名称"
            ],
            [
                'col' => 1,
                'row' => 5,
                'value' => "项目简称"
            ],
            [
                'col' => 1,
                'row' => 6,
                'value' => "项目负责人"
            ],
            [
                'col' => 1,
                'row' => 7,
                'value' => "项目周期"
            ],
            [
                'col' => 1,
                'row' => 8,
                'value' => "项目预算".PHP_EOL."总额（元）"
            ],
            [
                'col' => 5,
                'row' => 4,
                'value' => "项目编号"
            ],
            [
                'col' => 5,
                'row' => 5,
                'value' => "经费来源"
            ],
            [
                'col' => 5,
                'row' => 6,
                'value' => "承办处室"
            ],
            [
                'col' => 5,
                'row' => 7,
                'value' => "联系电话"
            ],
            [
                'col' => 5,
                'row' => 8,
                'value' => "项目决算\r\n 总额（元）"
            ],
        ];
        $sheet->setCellValueByColumnAndRow(1, 1, '预算、决算申请表');
        $sheet->setCellValueByColumnAndRow(1, 3, '■ 基本信息');

        foreach ($template as $v) {
            $sheet->setCellValueByColumnAndRow($v['col'], $v['row'], $v['value']);
        }

        # 数据填充
        $param_tamplate = [
            [
                'col' => 2,
                'row' => 4,
                'key' => "title"
            ],
            [
                'col' => 2,
                'row' => 5,
                'key' => "abbreviation"
            ],
            [
                'col' => 2,
                'row' => 6,
                'key' => "charge"
            ],
            [
                'col' => 2,
                'row' => 7,
                'key' => "cycle"
            ],
            [
                'col' => 2,
                'row' => 8,
                'key' => "title"
            ],
            [
                'col' => 6,
                'row' => 4,
                'key' => "no"
            ],
            [
                'col' => 6,
                'row' => 5,
                'key' => "funding_sources"
            ],
            [
                'col' => 6,
                'row' => 6,
                'key' => "department"
            ],

            [
                'col' => 6,
                'row' => 7,
                'key' => "tel"
            ],
            [
                'col' => 6,
                'row' => 8,
                'key' => "final_accounts"
            ],
        ];
        foreach ($param_tamplate as $k => $v) {
            $param_tamplate[$k]['value'] = $data[$v['key']] ? : '';
        }
        foreach ($param_tamplate as $v) {
            $sheet->setCellValueByColumnAndRow($v['col'], $v['row'], $v['value']);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }


}