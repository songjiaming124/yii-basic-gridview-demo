<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * This is the model class for table "{{%supplier}}".
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property string $t_status
 */
class Supplier extends \yii\db\ActiveRecord
{
    public $id_op;//自定义筛选字段
    public static $id_op_map = ['=', '>=', '>', '<=', '<'];//id_op的操作值
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%supplier}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['t_status'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 3],
            [['id_op'], 'string', 'max' => 2],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            't_status' => 'Status',
        ];
    }

    /**
     * 搜索多条数据
     * @Author   ziming
     * @DateTime 2022-07-04
     * @param    [type]     $params [
     *                              'id' => ,
     *                              'id_op' => ,
     *                              't_status' => ,
     *                              'name' => ,
     *                              'code' => ,
     *                              'limit' => ,//查询的条数
     *                              'ids' => ,//查多个id
     * ]
     * @return   ActiveDataProvider             [description]
     */
    public function search($params)
    {
        //获取query
        $query = $this->getQuery($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        return $dataProvider;
    }

    /**
     * 获取查询的query
     * @Author   ziming
     * @DateTime 2022-07-04
     * @param    [type]     $params [
     *                              'id' => ,
     *                              'id_op' => ,
     *                              't_status' => ,
     *                              'name' => ,
     *                              'code' => ,
     *                              'limit' => ,//查询的条数
     *                              'ids' => ,//查多个id
     *                              
     * ]
     * @return   Query             [description]
     */
    public function getQuery($params)
    {
        $this->load($params);//获取需要的属性
        $query = self::find();
        if (empty($params['sort'])) 
        {
            $query->orderBy('id ASC');
        }

        if (!empty($params['limit'])) 
        {
            $query->limit(intval($params['limit']));
        }

        //如果是ids查询就直接按id查
        if (!empty($params['ids'])) 
        {
            $ids = is_array($params['ids']) ? $params['ids'] : explode(',', $params['ids']);
            if ( !in_array('all', $ids) ) 
            {
                $query->andFilterWhere(['in', 'id', $ids]);
            }

            return $query;
        }

        if (!$this->validate()) 
        {
            return $query;
        }

        //模糊查询数据量起来以后需要注意性能
        if(!empty($this->name))
        {
            $query->andFilterWhere(['like', 'name', $this->name]);
        }

        //模糊查询数据量起来以后需要注意性能
        if (!empty($this->code))
        {
            $query->andFilterWhere(['like', 'code', $this->code]);
        }

        $query->andFilterWhere(['=', 't_status', $this->t_status]);
        //验证下id_op的有效性
        if (!empty($this->id_op) && in_array($this->id_op, self::$id_op_map)) 
        {
            $query->andFilterWhere([$this->id_op, 'id', $this->id]);
        }

        return $query;
    }
}
