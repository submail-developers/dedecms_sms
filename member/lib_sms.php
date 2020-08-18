<?php
/**
 * SmsBao实现类
 * @category   dedeCMS V5.7
 * @subpackage  Sms
 * @author    linf
 */

require_once(dirname(__FILE__)."/config.php");
require_once DEDEINC.'/membermodel.cls.php';

function sendsms($mobile, $content){

		$sendSmsUrl='https://api.mysubmail.com/message/send';


		$values=explode(',',$mobile);
        $param['to']    =   trim($values[3]);
        $param['appid'] =   $values[0];
        $param['signature'] =   $values[1];
        $param['content']   =  trim( '【'.$values[2].'】'.$content);
        $ret = http($sendSmsUrl, [],$param,'POST');
        return $ret;
}

function ismobile($mobile){
	return (strlen($mobile) == 11 || strlen($mobile) == 12) && (preg_match("/^13\d{9}$/", $mobile) || preg_match("/^14\d{9}$/", $mobile) || preg_match("/^15\d{9}$/", $mobile) || preg_match("/^18\d{9}$/", $mobile) || preg_match("/^0\d{10}$/", $mobile) || preg_match("/^0\d{11}$/", $mobile));
}

function getverifycode() {
	$length = 6;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	return $hash;
}

   /**
     * 发送http请求
     * @access protected
     * @param string $url  请求地址
     * @param string $param  get方式请求内容，数组形式，post方式时无效
     * * @param string $data  post请求方式时的内容，get方式时无效
     * @param string $method  请求方式，默认get
     */
 function http($url, $param, $data = '', $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);

        if(strtoupper($method) == 'POST'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;

            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //发生错误，抛出异常
        if($error) throw new \Exception('请求发生错误：' . $error);

        return  $data;
    }

?>
