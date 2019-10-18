<?php

/**

 * Created by yayue__god 

 * User: GOD

 * Date: 2019-04-27 15:30:00

 * Desc: 币价管理模型

 */

namespace app\model;



class coin_order extends BaseModel

{

    public $title = 'coin_order';

    

	/**

     * 模型查找id数据是否存在

     * @param id 数字

     * @return bool 布尔值

     */

    public function is_find($id){

		$data=$this->has($this->title,["AND"=>['id'=>$id]]);

        return $data;   
    }



    

    

	/**

     * 模型查找id数据

     * @param id 数字

     * @return data 返回一条数据

     */

    public function edit($id){

    	$data=$this->get($this->title,'*',["AND"=>['id'=>$id]]);

        return $data;      

    }





    /**

     * 模型查找id数据

     * @param  id 数字 field 字段名

     * @return data 返回某个字段的值

     */

    public function find_one($id,$field='*'){

        $data=$this->get($this->title, $field, ["AND"=>['id'=>$id]]);

        return $data;      

    }







    /**

     * 模型添加数据规则

     * @param data 数据

     * @return 当前操作ID

     */

    public function save($data){

        $data['created_time'] = time();

        $data['upgrade_time'] = time();

        $this->insert($this->title,$data);            

        return $this->id();  

    }





    /**

     * 模型修改数据规则

     * @param  id 数字 data 数据

     * @return BOOL

     */

    public function up($id,$data){

        $data['upgrade_time'] = time();

		$this->update($this->title,$data,['id'=>$id]);

		return $this->doo(); 

    }





    /**

     * 模型删除数据规则

     * @param  id 数字

     * @return BOOL

     */

    public function del($id){

        $this->delete($this->title,['id'=>$id]);

        return $this->doo();

    }



    /**

     * 根据用户查找数量

     * @param  id 数字

     * @return BOOL

     */

    public function user_count($uid){

        $rs=$this->count($this->title,['uid'=>$uid,'status'=>1]);

        return $rs;

    }



      /**

     * 模型列表数据

     * @param page 页码

     * @return data 返回数据集

     */

    public function lists($page=1,$number=10,$where_base=[]){        

        $startRecord=($page-1)*$number;        

        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];

        $where = array_merge($where_base,$where_other);

        $data=$this->select($this->title,'*', $where);        

        return $data;

    }





    /**

     * 获取某一类条数，不传值返回总条数

     * @param cate_id 类别id

     * @return int 条数

     */

    public function new_count($where_base=[]){    

        $data=$this->count($this->title,$where_base);

        $data = $data ? $data : 0;

        return $data;

    }

   



}

