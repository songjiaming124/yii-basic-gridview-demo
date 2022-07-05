<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\base\Model;
use app\models\Supplier;

class DemoController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        //不存在的action进入这里处理
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 首页
     * @Author   ziming
     * @DateTime 2022-07-04
     * @return   [type]     [description]
     */
    public function actionIndex()
    {
        $params       = Yii::$app->request->queryParams;
        $filterModel  = new Supplier();
        $dataProvider = $filterModel->search($params);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel
        ]);
    }

    /**
     * 导出
     * @Author   ziming
     * @DateTime 2022-07-04
     * @return   [type]     [description]
     */
    public function actionExport()
    {
        $params        = Yii::$app->request->queryParams;
        $supplierModel = new Supplier();

        $query = $supplierModel->getQuery($params);
        //判断下数据库的总数，太大又是全部导出给出提示，最多控制在5万条
        $maxLength = 50000;
        $count = $query->count();

        if ($count > $maxLength) 
        {
            //如果超过5万条还是要导出此处可以在此render一个中间页面分批次自动请求到处
            $query->limit($maxLength);
        }

        $data = $query->asArray()->all();
        return $this->exportCSV('suppliercsvfile',['id' => 'ID', 'name' => 'Name', 'code' => 'Code', 't_status' => 'Status'], $data, $maxLength);
    }

    /**
     * 导出文件核心函数
     * @Author   ziming
     * @DateTime 2022-07-04
     * @param    string     $fileName 文件名
     * @param    array     $expTitle [
     *                               'title1' => '标题1',
     *                               'title2' => '标题2',
     *                               
     * ]
     * @param    [type]     $expData  [
     *                                ['title1' => 'xxxxx', 'title2' => 'ddddd']
     *                                ['title1' => 'xxxxx1', 'title2' => 'ddddd2']
     * ]
     * @param    integer    $perSize  每个文件数据的最大条数
     * @return                  
     */
    private function exportCSV(string $fileName, $expTitle, $expData, $perSize = 1000)
    {
        if (empty($expTitle) || empty($expData) || empty($fileName) || !is_array($expTitle) || !is_array($expData))
        {
            return -1;
        }

        set_time_limit(0);
        $delimiter = ','; //分隔符
        $fileName = $fileName.date('YmdHis');
        $perSize  = $perSize > 0 ? ceil($perSize) : 1000;

        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8');
        header('Content-Disposition: attachment;filename=' . $fileName . '.csv');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a'); //打开output流

        $csvTitle = chr(0xEF). chr(0xBB). chr(0xBF);

        $csv_fields = array_keys($expTitle); //显示的字段

        foreach ($expTitle as $title)
        {
            $csvTitle .= '"'.$title.'"'.$delimiter;
        }

        $csvTitle = substr($csvTitle, 0, -1);
        $csvTitle .= PHP_EOL;
        //输出标题缓冲到浏览器
        fputs($fp, $csvTitle, strlen($csvTitle));

        $dataNum = count($expData);
        $pages   = ceil($dataNum / $perSize);

        //过滤非显示字段
        $new_expData = [];
        foreach ($expData as $key => $val)
        {
            if (empty($val)) continue;

            foreach ($csv_fields as $field)
            {
                $new_expData[$key][$field] = isset($val[$field]) ?
                    (is_array($val[$field]) ? implode('、', $val[$field]) : $val[$field]) : '';
            }
        }
        $expData = array_values($new_expData);
        unset($new_expData);

        for ($i = 1; $i <= $pages; $i++)
        {
            $step = ($i * $perSize) - 1;
            $csvData = '';
            foreach ($expData as $key => $item)
            {
                if ($key > $step || $key <= $step - $perSize) continue;

                array_map(function($val) use(&$csvData,$delimiter) {
                    //替换逗号,防止导致csv格式错乱
                    $val = (string)$val;
                    if (!empty($val) && (strpos($val,',') !== false || strpos($val,'，') !== false))
                    {
                        $val = str_replace([',','，'],[' ',' '],$val);
                    }
                    $csvData .= '"'.$val.'"'.$delimiter;
                    return $val;
                }, $item);
                $csvData = substr($csvData, 0, -1);
                $csvData .= PHP_EOL;
            }

            //刷新输出缓冲到浏览器
            fputs($fp, $csvData, strlen($csvData));

            //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲
            ob_flush();
            flush();
        }
        fclose($fp);
        unset($csvData, $expData);
        exit;
    }


}
