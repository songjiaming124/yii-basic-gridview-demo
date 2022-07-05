<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Supplier;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        $feild = ['name', 'code', 't_status'];
        $insertData = [];
        $tmp_random = [];
        for ($i=0; $i < 100; $i++) { 
            $tmpcode = $this->random(3);
            if (isset($tmp_random[$tmpcode])) 
            {
                continue; 
            }

            $tmp_random[$tmpcode] = 1;

            $insertData[] = [
                'name' => 'test0000' . $i,
                'code' => $tmpcode,
                't_status' => 'ok',
            ];
        }


        $result = Yii::$app->db->createCommand()->batchInsert(Supplier::tableName(),$feild , $insertData )->execute();
        if(!$result)
        {
            echo "fail".PHP_EOL;
        }else{
            echo "success".PHP_EOL;
        }

        return ExitCode::OK;
    }

    function random($length, $numeric = false)
    {
        // $_SERVER['DOCUMENT_ROOT'] 可以换成uuid
        $seed = base_convert(md5(microtime()), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
             $hash = '';
        } else {
             $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
             $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{
            mt_rand(0, $max)};
        }
        return $hash;
    }
}