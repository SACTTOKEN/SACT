<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 错误类
 */
namespace core\lib;

class Exception extends \Exception
{
    public $code = 500;
    public $msg = '系统级别错误';
	
	/**
     * 构造函数，接收一个关联数组
     * @param array $params 关联数组只应包含code、msg和errorCode，且不应该是空值
     */
    public function __construct($params=[])
    {
        if(!is_array($params)){
            return;
        }
        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
    }

	public function render($e)
     {
		$this->code = $e->code;
		$this->msg = $e->msg;
       
        $request = Request::instance();
        $result = [
			'result' => $this->msg,
			'status' => 0,
            'code' => $this->code
        ];
		
        return $result;
    }
	
	
    /*
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        Log::init([
            'type'  =>  'File',
            'path'  =>  LOG_PATH,
            'level' => ['error']
        ]);
//      Log::record($e->getTraceAsString());
        Log::record($e->getMessage(),'error');
    }
    
}
