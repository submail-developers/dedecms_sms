<?php
/**
 * Submail实现类
 * @category   dedeCMS V5.7
 * @subpackage  Sms
 * @author    linf
 */
//验证验证码
    if(!isset($vdcode)) $vdcode = '';

    session_start();
    require_once(dirname(__FILE__)."/config.php");
    //require_once(DEDEMEMBER."/templets/reg-new2.htm");
	require_once(DEDEMEMBER . '/lib_sms.php');
	//require_once(DEDEMEMBER . '/sms.php');
	// 短信内容
	//$verifycode = getverifycode();
    // 安全校验
    $row_reg = $dsql->GetOne("SELECT * FROM `#@__sysconfig` WHERE varname LIKE 'cfg_mobile_reg' ");
    $mobile_code_info = $dsql->GetOne("SELECT * FROM `#@__mobile_code` WHERE mobile = '".$_POST['mobile_phone']."' order by id desc limit 0,1");
    if(is_array($mobile_code_info)  && $row_reg['value'] == "Y" && (time() - strtotime($mobile_code_info['date_create']) <60))
    {
        echo '请求太频繁，请稍后再试！';
        exit();
    }
    $mobile_code_count = $dsql->GetOne("SELECT count(*) as count FROM `#@__mobile_code` WHERE mobile = '".$_POST['mobile_phone']."' and date_create > '".date('Y-m-d 00:00:00',time())."'");

    if(is_array($mobile_code_info)  && $row_reg['value'] == "Y" && $mobile_code_count['count'] > 5)
    {
        echo '请求发送短信次数过多！';
        exit();
    }

    $vacode=rand('111111','999999');
	PutCookie('mobile_vcode', $vacode, 60*5, '/');
	//setcookie("mobile_vcode",$vacode, 60*5);
    $message="您的验证码是：".$vacode."。请不要把验证码泄露给其他人。";
	$row = $dsql->GetOne("SELECT * FROM `#@__sysconfig` WHERE varname='cfg_smsbao_name' ");
	$row_pwd = $dsql->GetOne("SELECT * FROM `#@__sysconfig` WHERE varname='cfg_smsbao_password' ");
	$row_sign = $dsql->GetOne("SELECT * FROM `#@__sysconfig` WHERE varname='cfg_smsbao_sign' ");
	$infos=$row['value'].','.$row_pwd['value'].','.$row_sign['value'].','.$_POST['mobile_phone'].','.$_POST['v_code'];
	$result = sendsms($infos, $message);
	$result =   json_decode($result,true);
	if($result['status']=='success'){
        $mobile_code = "INSERT INTO `#@__mobile_code` (`mobile`,`code`,`ip`,`date_create`) values ('".$_POST['mobile_phone']."','".$vacode."','".$_SERVER['REMOTE_ADDR']."','".date('Y-m-d H:i:s')."')";
        $dsql->ExecuteNoneQuery($mobile_code);
        echo '验证码已发送，请注意查收';
    }else{
        echo '验证码发送失败，请重试，错误代码：'.$result['status'];
    }
?>
