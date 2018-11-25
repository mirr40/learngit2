<?php
/**PHP+Jquery+MYSQL+Slim架设RESTful服务器*/

//导入类库
require 'Slim/Slim.php';
require 'Common/Kd.Db.php';
require_once 'Common/Kd.Log.php';
require_once 'Common/Kd.Common.php';
require_once 'Common/Kd.DumpHTTPRequestToFile.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL);

session_cache_limiter(false);
session_start();
/////////////////////////Global Configuration///////////////////////////
$isCheckHeader = true;  // this is the switch to turn on/off the function of checking the header.
////////////////////////////////////////////////////////////////////////
$andoridLatestVersion = "1.0.4";
$androidCompatibleVersion = "1.0.4";
$andoridLatestVersionURL = "http://kedoulangdu.com/release/kedoulangdu.apk";
$iOSLatestVersion = "1.0.4";
$iOSCompatibleVersion = "1.0.4";
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
$kktAndoridLatestVersion = "1.0.0";
$kktAndroidCompatibleVersion = "1.0.0";
$kktAndoridLatestVersionURL = "http://kedoulangdu.com/release/kedoulangdu.apk";
$kktIOSLatestVersion = "1.0.0";
$kktIOSCompatibleVersion = "1.0.0";
////////////////////////////////////////////////////////////////////////

//初始化日志
$logHandler= new CLogFileHandler("./logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 1);
$log->__setLevel(15);   //1: debug only  2: info only 3: debug+info 4: warn  8: error 12: warn+error 15: all

$log->INFO("Hello from index.php" );

//注册Slim框架自带的自动加载类
\Slim\Slim::registerAutoloader();


//创建实例
$app = new \Slim\Slim();


$headers = $app->request->headers;
$sessionIdFromClient = $app->request->headers->get('Session-Id');
$appVersion = $app->request->headers->get('App-Version');
$serverVersion = $app->request->headers->get('Server-Version');
$appId = $app->request->headers->get('App-Id');
$userIdFromClient = $app->request->headers->get('User-Id');
$keyDate = $app->request->headers->get('Key-Date');
$encrypted = $app->request->headers->get('Encrypted');


$log->INFO("SessionId: ".$sessionIdFromClient." appId: ".$appId." appVersion: ".$appVersion." serverVersion: ".$serverVersion." UserId: ".$userIdFromClient." KeyDate: ".$keyDate." encrypted: ".$encrypted);




$log->DEBUG("[Debug]========= Dump Incoming Request Begin ===========");
$requestBody=$app->request->getBody();
$log->DEBUG("[Debug]Dump Request Body : ".$requestBody);

$log->DEBUG("[Debug]========= Dump Incoming Request End =============");

(new DumpHTTPRequestToFile)->execute('./dumprequest.txt',$requestBody);
/*
Session-Id: 0
App-Version:1.2.1
User-Id:0
*/

//显示
$app->get('/','showHandle');
$app->get('/audios','showHandle');

$app->get('/audios/:id','getHandle');


//添加
$app->post('/','addHandle');


//删除
//$app->delete('/:id','deleteHandle');


//设置Response里的headers
$app->response->headers->set('User-Id',$userIdFromClient);
$app->response->headers->set('Session-Id',$sessionIdFromClient);
$app->response->headers->set('Server-Version',"1.0.0");
$app->response->headers->set('Content-Type',"application/json");


function checkHeader($app,$isCheckUser) 
{
	global $isCheckHeader;
	if(!$isCheckHeader)
		return;
	
	$appVersion = $app->request->headers->get('App-Version');
	$appId = $app->request->headers->get('App-Id');	
	
	$isPassed = true;

	
	if($appVersion == NULL || ("com.mycoreedu.langduapp.ios" != $appId && "com.mycoreedu.langduapp.android" != $appId && "com.mycoreedu.keketingapp.ios" != $appId && "com.mycoreedu.keketingapp.android" != $appId))
	{
		Log::INFO("checkHeader failed : appVersion is NULL or appId is not OK");
		$isPassed = false;
	}
	else
		if($isCheckUser)
		{
			Log::INFO("checkHeader : checking session id...");
			$sessionIdFromClient = $app->request->headers->get('Session-Id');
			$userIdFromClient = $app->request->headers->get('User-Id');	
			if($sessionIdFromClient == NULL && $userIdFromClient == NULL)
				$isPassed = true;
			else
				$isPassed = isSessionIdValid($userIdFromClient, $sessionIdFromClient,$appId);
		}

	if(!$isPassed)
	{
		$response = '{"statusCode":444,"statusMsg":"请求错误，请重新登录"}';
		sendResponse('checkHeader',$response);
		
		exit; //exit the application
	}

}

function isSessionIdValid($userId, $sessionId,$appId)
{
	if($userId == NULL || $sessionId == NULL)
	{
		Log::INFO("isSessionIdValid : userId or sessionId is NULL ");
		return false;
	}

	

	$sessionIdStored = Db::getSessionId($userId,$appId);

	Log::INFO("isSessionIdValid : get SessionId from DB = ".$sessionIdStored);
	
	if($sessionIdStored != $sessionId)
	{	
		unset($sessionIdStored);
		Log::INFO("isSessionIdValid : comparing SessionId failed: carried SessionId = ".$sessionId);
		return false;
	}
	
	$diff = strtotime($sessionIdStored) - time();
	if($diff < -60*60*24*60)  //expires in 24hrs*60
	{	
		unset($sessionIdStored,$diff);
		Log::INFO("isSessionIdValid : SessionId too old: carried SessionId = ".$sessionId);
		return false;
	}
	Log::DEBUG("isSessionIdValid : SessionId check sucessfully : carried SessionId = ".$sessionId." stored SessionId = ".$sessionIdStored);
	unset($sessionIdStored,$diff);
	return true;	
}



function getValue($value, $key)
{
	if(isset( $value[$key]))
		return $value[$key];	
	else
		return NULL;
}

function sendResponse($intefaceName, $response)
{
	Log::DEBUG($intefaceName." : ".$response);
	echo $response;
}

//显示
function showHandle(){
    //$sql="select * from tk_city order by CityID desc limit 10";
	$sql="select * from audios order by id desc limit 10";
    try{
        $pdo=getConnect();
        $stmt=$pdo->query($sql);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo=null; 
        unset($stmt, $pdo);  
        //json不支持中文,使用前先转码         
        foreach($data as $key=>$value){  
            foreach ($value as $k=>$v){
                $data[$key][$k]=urlencode($v);
            }
        }  
        echo urldecode(json_encode($data));
    }catch(PDOException $e){
        echo '{"err":'.$e->getMessage().'}';
    } 
    unset($sql);      
}


//显示单个
function getHandle($id){
	$sql="select * from audios where id = :id";
	
    try{
        
        $pdo=getConnect();
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam("id",$id);
        $stmt->execute();
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($stmt, $pdo);
         //json不支持中文,使用前先转码         
        foreach($data as $key=>$value){  
            foreach ($value as $k=>$v){
                $data[$key][$k]=urlencode($v);
            }
        }  
        echo urldecode(json_encode($data));

    }catch(PDOException $e){
        echo '{"err":'.$e->getMessage().'}'; 
    }
    unset($sql);
}


// 添加
function addHandle(){
    //$sql="insert into tk_city(CityName,ZipCode,letter,abbr) values(:CityName,:ZipCode,:letter,:abbr)";
	
	$sql="insert into tk_city(CityName,ZipCode,letter,abbr) values(:CityName,:ZipCode,:letter,:abbr)";
	
    try{
        $pdo=getConnect();
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam("CityName",$_POST['CityName']);
        $stmt->bindParam("ZipCode",$_POST['ZipCode']);
        $stmt->bindParam("letter",$_POST['letter']);
        $stmt->bindParam("abbr",$_POST['abbr']);
        $stmt->execute();
        $id=$pdo->lastInsertId();
        $pdo=null;
        unset($pdo, $stmt);
        echo '{"id":'.$id.'}';
    }catch(PDOException $e){
        echo '{"err":'.$e->getMessage().'}';
    }
    unset($sql);
}

// 初始化同步请求
 
$app->post('/sync', function() use($app, $log){
	
		checkHeader($app,false);
        //获取前台传过来的数据
	
		Common::getKey($key,$keyDate);
		
		$appId = $app->request->headers->get('App-Id');	
		
		if("com.mycoreedu.langduapp.ios" == $appId || "com.mycoreedu.langduapp.android" == $appId)
		{
			global $andoridLatestVersion;
			global $androidCompatibleVersion;
			global $andoridLatestVersionURL;
			global $iOSLatestVersion;
			global $iOSCompatibleVersion;
			
			$response = '{"data":{"andoridLatestVersion":"'.$andoridLatestVersion.'","andoridLatestVersionURL":"'.$andoridLatestVersionURL.'","androidCompatibleVersion":"'.$androidCompatibleVersion.'","iOSLatestVersion":"'.$iOSLatestVersion.'","iOSCompatibleVersion":"'.$iOSCompatibleVersion.'","key":"'.$key.'","keyDate":"'.$keyDate.'"},"statusCode":200,"statusMsg":"请求成功"}';
		}
		else
		{
			global $kktAndoridLatestVersion;
			global $kktAndroidCompatibleVersion;
			global $kktAndoridLatestVersionURL;
			global $kktIOSLatestVersion;
			global $kktIOSCompatibleVersion;
			
			$response = '{"data":{"andoridLatestVersion":"'.$kktAndoridLatestVersion.'","andoridLatestVersionURL":"'.$kktAndoridLatestVersionURL.'","androidCompatibleVersion":"'.$kktAndroidCompatibleVersion.'","iOSLatestVersion":"'.$kktIOSLatestVersion.'","iOSCompatibleVersion":"'.$kktIOSCompatibleVersion.'","key":"'.$key.'","keyDate":"'.$keyDate.'"},"statusCode":200,"statusMsg":"请求成功"}';
		}		
		
		sendResponse('/sync',$response);
		unset($response, $appId);
    }
);

//心跳消息，用于确保客户端使用正确的session id
$app->post('/heartbeat', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		$log->INFO("dump request : ".$data);
						
		Db::getUserAuthorityState($app->request->headers->get('User-Id'),$authorityStateKkt);
		if(1 == $authorityStateKkt)
		{
			$response = '{"statusCode":201,"statusMsg":"权限变更，请更新权限"}';
			
		}
		else
			$response = '{"statusCode":200,"statusMsg":"OK"}';
		
		sendResponse('/heartbeat',$response);
		
    }
);


// 发送sms
$app->post('/smsCode', function() use($app, $log){
	
		checkHeader($app,false);
			
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		$mobile = $value["mobile"];
		
		$log->INFO("request to send SMS to : ".$mobile);
		if(strlen($mobile) != 11)
			 $response = '{"statusCode":400,"statusMsg":"发送验证短信失败，无效的手机号码"}';
		else
		{
			$errCode = NULL;
			
			$appId = $app->request->headers->get('App-Id');	

			$appName = "课课听";
			if("com.mycoreedu.langduapp.ios" == $appId || "com.mycoreedu.langduapp.android" == $appId)
				$appName = "蝌蚪朗读";
			
			
			if(Common::sendSmsCode($mobile,$errCode,$appName))
				$response = '{"statusCode":200,"statusMsg":"发送验证短信成功"}';
			else
			{
				if($errCode != NULL && $errCode == -42)
					$response = '{"statusCode":401,"statusMsg":"发送验证短信失败，请求发送短信太频繁"}';
				else
					$response = '{"statusCode":500,"statusMsg":"发送验证短信失败，服务器错误"}';
			}
			unset($appId, $appName, $errCode);
		}
		sendResponse('/smsCode',$response);
		unset($data, $value, $mobile, $response);
    }
);

// LoginMobile 手机号码登录
$app->post('/loginMobile', function() use($app, $log){
	
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
			
		$value=json_decode($data,true);
		
		Common::decryptAESEncryptedData($app,$value,$decryptedJson);
			
		$value = $decryptedJson;
				
		$passwordStored = NULL; $userId = NULL;$nickname = NULL; $gender = NULL;  $grade = NULL;  $province = NULL;  $city = NULL;  $county = NULL; $balance = NULL; $signature = NULL; $urlAvatar = NULL;$birthday = NULL;
		
		$mobile = $value["mobile"];
		$passwordInput = $value["password"];
		
		/*
		Common::decryptAES($app,$passwordInput,$decrypted);
		$passwordInput = $decrypted;
		$log->INFO("decrypted password: ".$passwordInput);
		*/

		
		
		$log->INFO("loginMobile - ".$mobile." is trying to login from APP... ");
		
		if(strlen($mobile)!= 11)
			$response = '{"statusCode":406,"statusMsg":"嗯...网络开小差了...请重新登录一次"}';
		else
			if($passwordInput == NULL)
				$response = '{"statusCode":406,"statusMsg":"登录失败，密码输入错误"}';
			else
			{
				Db::getUserProfile($mobile,$passwordStored,$userId,$nickname,$gender,$grade,$province,$city,$county,$urlAvatar,$signature,$balance,$birthday );
				
				if($userId == NULL)
				{
					$response = '{"statusCode":404,"statusMsg":"登录失败，该手机号码还没有注册"}';
				}
				else
				{
					if($passwordStored != $passwordInput)
						$response = '{"statusCode":401,"statusMsg":"登录失败，密码错误"}';
					else
					{
						$appId = $app->request->headers->get('App-Id');
						$newSessionId = Db::updateSessionId($userId,$appId);
												
						if($newSessionId)
						{
							$userLikeIds = Db::getUserLike($userId);
							
							
							//$_SESSION['Session-Id'] = $newSessionId;
							$app->response->headers->set('Session-Id',$newSessionId);
							$app->response->headers->set('User-Id',$userId);
							$response = '{"data":{"userId":"'.$userId.'","mobile":"'.$mobile.'","nickname":"'.$nickname.'","gender":'.$gender.',"grade":"'.$grade.'","province":"'.$province.'","city":"'.$city.'","county":"'.$county.'","urlAvatar":"'.$urlAvatar.'","signature":"'.$signature.'","balance":'.$balance.',"birthday":"'.$birthday.'","likeWorkIds":"'.$userLikeIds.'"},"statusCode":200,"statusMsg":"登录成功"}';
							$log->INFO("loginMobile - ".$mobile." logged in from APP...");
							
						}
						else
						{
							$response = '{"statusCode":500,"statusMsg":"登录失败，服务器错误"}';
						}
						unset($appId, $newSessionId);
					}
				}
		
			}
		sendResponse('/loginMobile',$response);
		unset($data, $value, $mobile, $passwordStored, $passwordInput, $userId, $nickname, $gender, $grade, $province, $city, $county, $signature, $urlAvatar, $birthday, $balance, $userLikeIds);	
    }
);

// userProfile 修改用户基本信息
$app->put('/userProfile', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
		$data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$nickname = NULL; $gender = -1;  $grade = -1;  $province = NULL;  $city = NULL;  $county = NULL;  $signature = NULL; $urlAvatar = NULL; 

		$nickname = getValue($value,"nickname");
		$gender = getValue($value,"gender");
		$grade = getValue($value,"grade");
		$province = getValue($value,"province");
		$city = getValue($value,"city");
		$county = getValue($value,"county");
		$signature = getValue($value,"signature");
		$urlAvatar = getValue($value,"urlAvatar");
		$birthday = getValue($value,"birthday");	
		
		if(strpos($birthday,'年') !== false)
		{
			$table_change = array('年'=>'-');
			$table_change += array('月' => '-');
			$table_change += array('日' => '');
			
 			$birthdayNew = strtr($birthday,$table_change);
			$log->INFO("userProfile - Changing wrong format date ".$birthday." to correct format date ".$birthdayNew);
			$birthday =	$birthdayNew;
			unset($table_change, $birthdayNew);
		}

		
		$userIdFromClient = $app->request->headers->get('User-Id');
		$appId = $app->request->headers->get('App-Id');	
		$log->INFO("userProfile - userId ".$userIdFromClient." is trying to update user profile...");
		
		if($userIdFromClient == NULL)
		{
			$response = '{"statusCode":406,"statusMsg":"修改失败，错误的请求，用户号为空"}';
		}
		else
			if(Db::updateUserProfile($userIdFromClient,$nickname, $gender, $grade, $province, $city, $county, $signature,$urlAvatar,$birthday,$appId))
			{
				Db::getUserProfileByUserId($userIdFromClient,$passwordStored, $mobileStored,$nicknameStored, $genderStored, $gradeStored, $provinceStored, $cityStored, $countyStored, $urlAvatarStored, $signatureStored,$birthdayStored );
				
				$response = '{"data":{"nickname":"'.$nicknameStored.'","gender":'.$genderStored.',"grade":"'.$gradeStored.'","province":"'.$provinceStored.'","city":"'.$cityStored.'","county":"'.$countyStored.'","urlAvatar":"'.$urlAvatarStored.'","signature":"'.$signatureStored.'","birthday":"'.$birthdayStored.'"},"statusCode":200,"statusMsg":"修改成功"}';
			}
			else	
				$response = '{"statusCode":406,"statusMsg":"修改失败，错误的请求"}';
			
		sendResponse('/userProfile',$response);
		unset($data, $value, $nickname, $gender, $grade, $province, $city, $county, $signature, $urlAvatar, $userIdFromClient, $response);
    }
);

//获取用户相关信息
$app->post('/userInfo', function() use($app, $log){
		checkHeader($app,true);
		$data = $app->request->getBody();
		$value = json_decode($data,true);

		$id=getValue($value, "userId");
		$log->INFO("request to get userInfo with id : ".$id);
		if(Db::getUserInfo($id, $nickname, $gender, $grade, $province, $city, $county, $urlAvatar, $signature, $birthday))
			{
				$response = '{"data":{"userId":"'.$id.'","nickname":"'.$nickname.'","gender":"'.$gender.'","grade":"'.$grade.'","province":"'.$province.'","city":"'.$city.'","county":"'.$county.'","urlAvatar":"'.$urlAvatar.'","siganture":"'.$signature.'","birthday":"'.$birthday.'"},"statusCode":200,"statusMsg":"请求成功"}';
				unset($nickname, $gender, $grade, $province, $city, $county, $urlAvatar, $signature, $birthday);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
		
		sendResponse('/userInfo',$response);
		unset($data ,$value, $id, $response);
		Log::INFO("index.php: /userInfo mem usage size:". (memory_get_usage() / 1024 / 1024));
});

//获取用户信息
// $app->get('/users',function() use($app, $log){
// 		$data=$app->request->getBody();
// 		// $userId, $mobile,$nickname, $gender, $grade, $province, $city, $county, $urlAvatar, $signature, $birthday, $balance, $counter1, $counter2, $counter3, $counter4
// 		$value=json_decode($data,true);
// 		$thisArray  = array(array());
// 		if(Db::getUser($thisArray)){
// 			$thisJson = urldecode(json_encode($thisArray));

// 			$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"更改成功"}';
// 			// $response = '{"data":{"userId":"'.$userId.'","mobile":"'.$mobile.'","nickname":"'.$nickname.'","gender":"'.$gender.'","grade":"'.$grade.'","province":"'.$province.'","city":"'.$city.'","county":"'.$county.'","urlAvatar":"'.$urlAvatar.'","signature":"'.$signature.'","birthday":"'.$birthday.'","balance":"'.$balance.'","counter1":"'.$counter1.'","counter2":"'.$counter2.'","counter3":"'.$counter3.'","counter4":"'.$counter4.'"},"statusCode":200,"statusMsg":"更改成功"}';
// 		}else{
// 			$response = '{"statusCode":500,"statusMsg":"修改失败，系统错误"}';
// 		}
// });

// 修改密码
$app->put('/userPassword', function() use($app, $log){
	
		checkHeader($app,true);
			
        //获取前台传过来的数据
		$data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$oldPassword = NULL; $newPassword = NULL;  

		$oldPassword = getValue($value,"oldPassword");
		$newPassword = getValue($value,"newPassword");	
				
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("userPassword - userId ".$userIdFromClient." is trying to update user password...");
		
		$storedPassword = NULL;
		Db::getUserProfileByUserId($userIdFromClient,$storedPassword, $mobile,$nickname, $gender, $grade, $province, $city, $county, $urlAvatar, $signature ,$birthday);
		if($storedPassword == NULL)
			$response = '{"statusCode":401,"statusMsg":"修改失败，无效用户标识"}';
		else
			if($storedPassword != $oldPassword)
				$response = '{"statusCode":400,"statusMsg":"修改失败，原密码错误"}';
		else
			if(Db::updateUserPassword($userIdFromClient,$oldPassword, $newPassword))
			{
				$response = '{"statusCode":200,"statusMsg":"修改成功"}';
			}
			else	
				$response = '{"statusCode":500,"statusMsg":"修改失败，系统错误"}';
			
		sendResponse('/userPassword',$response);
		unset($data, $value, $response, $oldPassword, $newPassword, $userIdFromClient, $storedPassword, $mobile,$nickname, $gender, $grade, $province, $city, $county, $urlAvatar, $signature ,$birthday);
    }
);

// 因忘记密码而通过短信验证来修改密码
$app->put('/userPasswordWithSms', function() use($app, $log){
	
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$mobile = getValue($value,"mobile");
		$password = getValue($value,"password");
		$smsCode = getValue($value,"smsCode");		
		
		
		$log->INFO("userPasswordWithSMS - ".$mobile." is trying to update password from APP...");
		
		if(strlen($mobile)!= 11)
			$response = '{"statusCode":400,"statusMsg":"修改失败，无效的手机号码"}';
		else
			if($password == NULL)
				$response = '{"statusCode":406,"statusMsg":"修改失败，密码格式错误"}';
			else
				if($smsCode == NULL)
					$response = '{"statusCode":402,"statusMsg":"修改失败，短信验证码错误"}';				
			else
			{
				$userIdStored = NULL;
				Db::getUserProfile($mobile, $passwordStored, $userIdStored, $nicknameStored, $genderStored, $gradeStored, $provinceStored, $cityStored, $countyStored, $urlAvatarStored, $signatureStored, $balanceStored, $birthdayStored );
				
				if($userIdStored == NULL)
					$response = '{"statusCode":401,"statusMsg":"修改失败，该手机号码还没注册"}';	
				else
				{
					$smsCodeSent = NULL;
					Db::getSmsCode($mobile,$smsCodeSent);
					if($smsCodeSent == "expired")
						$response = '{"statusCode":403,"statusMsg":"修改失败，短信验证码过期，请重新请求"}';
					else
						if($smsCodeSent != $smsCode)
							$response = '{"statusCode":402,"statusMsg":"修改失败，短信验证码错误"}';
						else
						{
							if(Db::updateUserPasswordWithMobile($mobile, $password))
							{
								$response = '{"statusCode":200,"statusMsg":"修改成功"}';
							}
							else	
								$response = '{"statusCode":500,"statusMsg":"修改失败，服务器错误"}';				
						}
						unset($smsCodeSent);
				}
				unset($userIdStored);
			}
		sendResponse('/userPasswordWithSms',$response);
		unset($data, $value, $response, $mobile, $passwordStored, $nicknameStored, $genderStored, $gradeStored, $provinceStored, $cityStored, $countyStored, $urlAvatarStored, $signatureStored, $balanceStored, $birthdayStored, $smsCode);
    }
);
// 修改手机号码
$app->put('/userMobile', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$mobile = getValue($value,"mobile");

		$smsCode = getValue($value,"smsCode");
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("userMobile - ".$userIdFromClient." is trying to update mobile from APP...");
		
		if(strlen($mobile)!= 11)
			$response = '{"statusCode":400,"statusMsg":"修改失败，无效的手机号码"}';
		else
			if($smsCode == NULL)
				$response = '{"statusCode":402,"statusMsg":"修改失败，短信验证码错误"}';				
			else
			{
				$userIdStored = NULL;
				Db::getUserProfile($mobile, $passwordStored, $userIdStored, $nicknameStored, $genderStored, $gradeStored, $provinceStored, $cityStored, $countyStored, $urlAvatarStored, $signatureStored, $balanceStored, $birthdayStored );
				
				if($userIdStored != NULL)
					$response = '{"statusCode":401,"statusMsg":"修改失败，该手机号码已与另一个账号绑定"}';		
				else
				{			
				
				
					$smsCodeSent = NULL;
					Db::getSmsCode($mobile,$smsCodeSent);
					if($smsCodeSent == "expired")
						$response = '{"statusCode":403,"statusMsg":"修改失败，短信验证码过期，请重新请求"}';
					else
						if($smsCodeSent != $smsCode)
							$response = '{"statusCode":402,"statusMsg":"修改失败，短信验证码错误"}';
						else
						{
							if(Db::updateUserMobile($userIdFromClient, $mobile))
							{
								$response = '{"statusCode":200,"statusMsg":"修改成功"}';
							}
							else	
								$response = '{"statusCode":500,"statusMsg":"修改失败，服务器错误"}';						
								
						}
						unset($smsCodeSent);
					}
					unset($userIdStored, $mobile, $passwordStored, $nicknameStored, $genderStored, $gradeStored, $provinceStored, $cityStored, $countyStored, $urlAvatarStored, $signatureStored, $balanceStored, $birthdayStored );
				}
		sendResponse('/userMobile',$response);
		unset($data, $value, $response, $smsCode, $userIdFromClient );
	}
   
);
// LoginMobile 手机号码退出登录
$app->post('/logout', function() use($app, $log){
	    $app->response->headers->set('Session-Id','0');
		$response = '{"statusCode":200,"statusMsg":"成功"}';
		sendResponse('/logout',$response);
		unset($response);
    }
);


// LoginMobile 手机号码注册
$app->post('/registerMobile', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$passwordStored = NULL; $userId = NULL;
		
		$mobile = $value["mobile"];
		$password = $value["password"];
		$smsCode = $value["smsCode"];
		
		$log->INFO("RegisterMobile - ".$mobile." is trying to register from APP...");
		
		if(strlen($mobile)!= 11)
			$response = '{"statusCode":400,"statusMsg":"注册失败，无效的手机号码"}';
		else
			if($password == NULL)
				$response = '{"statusCode":406,"statusMsg":"注册失败，密码格式错误"}';
			else
				if($smsCode == NULL)
					$response = '{"statusCode":401,"statusMsg":"注册失败，短信验证码错误"}';				
			else
			{
				$smsCodeSent = NULL;
				Db::getSmsCode($mobile,$smsCodeSent);
				if($smsCodeSent == "expired")
					$response = '{"statusCode":402,"statusMsg":"注册失败，短信验证码过期，请重新请求。"}';
				else
					if($smsCodeSent != $smsCode)
						$response = '{"statusCode":401,"statusMsg":"注册失败，短信验证码错误"}';
					else
					{
						$userId = NULL;
						if(Db::addUserInRegistation($mobile,$password,$userId))
						{
							if($userId == NULL)
								$response = '{"statusCode":500,"statusMsg":"注册失败，请重试"}';
							else
							{
								$app->response->headers->set('User-Id',$userId);
								$response = '{"data":{"userId":"'.$userId.'"},"statusCode":200,"statusMsg":"注册成功"}';
								$log->INFO("RegisterMobile - ".$mobile." registered from APP...");
							}
						}
						else
						{
							$response = '{"statusCode":409,"statusMsg":"注册失败，该手机号码已经注册"}';
						}
						unset($userId);
					}
					unset($smsCodeSent);
			}
		sendResponse('/registerMobile',$response);
		unset($data, $value, $passwordStored, $mobile, $password, $smsCode, $response);
    }
);


// 获取专辑列表
$app->post('/albums', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get albums with where : ".$where." and pageSize :".$pageSize." page :".$page);
		
		$albumsArray  = array(array());

		if(Db::getAlbums($where,$pageSize,$page,$albumsArray,$pageCount,$app->request->headers->get('App-Id')))
		{
		
			$albumsJson = urldecode(json_encode($albumsArray));	
			
			$authorities = Db::getAuthorities($app->request->headers->get('User-Id'));
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$albumsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"authorities":"'.$authorities.'","statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$albumsJson.'},"authorities":"'.$authorities.'","statusCode":200,"statusMsg":"成功"}';
			unset($albumsJson, $authorities);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/albums',$response);
		unset($data, $value, $where, $pageSize, $page, $albumsArray, $pageCount, $response);
    }
);

//获取我的课程
$app->post('/myalbum', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$albumId = array();$where = NULL;$chinesesJson="";
		$authorities = Db::getAuthorities($app->request->headers->get('User-Id'));

		$log->INFO("request to get authorities  : ".$authorities);
		$albumsJson  = array(array());
		 $where = "LOCATE(id,'".$authorities."')>0";
		if(Db::getAlbumsByAuth($where, $albumsArray)){
			$albumsJson = urldecode(json_encode($albumsArray));
			$response = '{"data":{"dataSet":'.$albumsJson.'},"statusCode":200,"statusMsg":"成功"}';
		}
		else{
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
		}
		  sendResponse('/myalbums',$response);
}
);
// $app->post('/myalbum', function() use($app, $log){
// 		checkHeader($app,false);
//         //获取前台传过来的数据
//         $data=$app->request->getBody();
		
// 		$value=json_decode($data,true);
// 		$albumId = array();$where = NULL;$chinesesJson="";
// 		$authorities = Db::getAuthorities($app->request->headers->get('User-Id'));

// 		$log->INFO("request to get authorities  : ".$authorities);
// 		$albumsJson  = array(array());
// 		 $where = "LOCATE(id,'".$authorities."')>0";
// 		if(Db::getAlbumsByAuth($where, $albumArray)){
				
			// if(sizeof($albumsArray)>1){
			// 	$count = sizeof($albumsArray);
			// 	for($i = 0;$i<sizeof($albumsArray);$i++){
			// 		$albumsJson="{";
			// 		$size = sizeof($albumsArray[$i]);
			// 		$count = 0;
			// 		foreach($albumsArray[$i] as $key=>$value){
			// 			$albumsJson.='"'.$key.'":"'.$value.'"';
			// 			$count++;
			// 			if($count<$size)
			// 				$albumsJson.=",";
			// 		}
			// 		$albumsJson.="}";
			// 	}
			// }
				
			// $albumsJson = urldecode(json_encode($albumArray));
			// 	$chinesesJson = urldecode($chinesesJson);	
			// 	$mathsJson = urldecode(json_encode($mathsArray));
			// 	$englishsJson = urldecode(json_encode($englishsArray));

			// 	$response = '{"data":{"dataSet":[{"type":chinese,"dataSet":['.$chinesesJson.']},{"type":math,"dataSet":['.$mathsJson.']},{"type":english,"dataSet":['.$englishsJson.']}},"statusCode":200,"statusMsg":"成功"}';
// 			$response = '{"data":{"dataSet":'.$albumsJson.'},"statusCode":200,"statusMsg":"成功"}';
// 			}
// 			else{
// 			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
// 			}
// 		 	 print_r($albumsArray);
// 		  sendResponse('/myalbums',$response);
// }
// );


// 获取精选/推荐专题专辑列表
$app->post('/suggestedAlbums', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
	
		
		$log->INFO("/suggestedAlbums - request to get suggestedAlbums with where : ".$where);
		
		$albumsArray  = array(array());

		if(Db::getSuggestedAlbums($where,$albumsArray,$app->request->headers->get('App-Id')))
		{
		
			$jsonStr = "[";
			$currentSubCatagory = " ";
			
			$log->INFO("/suggestedAlbums - The number of albums found is : ".sizeof($albumsArray));
			if(sizeof($albumsArray) > 1)
			{
			
				for ($i = 0; $i < sizeof($albumsArray); $i++) 
				{
					if($albumsArray[$i]["subCategory"] != $currentSubCatagory)
					{	
						$currentSubCatagory = $albumsArray[$i]["subCategory"];
						
						
						if($i == 0)
							$jsonStr .= '{"subCatagoryName":"'.$currentSubCatagory.'","dataSet":[';
						else
							$jsonStr .= ']},{"subCatagoryName":"'.$currentSubCatagory.'","dataSet":[';
				
					}
					else
						$jsonStr .= ",";	
					
					$jsonStr .= "{"; 
					
					$size = sizeof($albumsArray[$i]);
					$count = 0;
					foreach ($albumsArray[$i] as $key => $value) 
					{
						if($key == "product")
							$jsonStr .='"'.$key.'":'.urldecode(json_encode($value));
						else
							$jsonStr .='"'.$key.'":"'.$value.'"';
						
						$count++;
						if($count < $size) 
							$jsonStr .=",";
					
					}
					
					$jsonStr .= "}"; 
					unset($size, $count);
				}
				
				if(strlen($jsonStr) > 3) 
					$jsonStr .= "]}";
				
				$jsonStr .= "]";
				
				$jsonStr = urldecode($jsonStr);
				
				$authorities = Db::getAuthorities($app->request->headers->get('User-Id'));
				
				$response = '{"data":{"dataSet":'.$jsonStr.'},"authorities":"'.$authorities.'","statusCode":200,"statusMsg":"成功"}';
				unset($authorities);
			}
			else
				$response = '{"data":{"dataSet":[]},"authorities":"'.$authorities.'","statusCode":200,"statusMsg":"成功"}';
			unset($jsonStr, $currentSubCatagory);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/albums',$response);
		unset($data, $value, $where, $albumsArray, $albumsJson);
		
    }
);


// 获取课课听课程(含专题栏目)列表
$app->post('/albums/:id/kktItems', function($id) use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$type = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get album items with id : ".$id." and pageSize :".$pageSize." page :".$page);
		
		
		
		$itemsArray  = array(array());

		if(Db::getItems($id, "kkt", $pageSize,$page,$itemsArray,$pageCount))
		{

			$itemsJson = "[";
			$currentUnit = -1;
			$arraySize = sizeof($itemsArray);
			$log->INFO("The number of items found is : ".$arraySize);
			if($arraySize >= 1)
			{
			
				for ($i = 0; $i < $arraySize; $i++) 
				{
					
					
					$itemsJson .= "{"; 

					
					$size = sizeof($itemsArray[$i]);
					$count = 0;
					foreach ($itemsArray[$i] as $key => $value) 
					{
						if($key != "content")
							$itemsJson .='"'.$key.'":"'.$value.'"';
						else
							$itemsJson .='"'.$key.'":'.$value;
						$count++;
						if($count < $size) 
							$itemsJson .=",";
					
					}
					
					if($i == $arraySize-1)
						$itemsJson .= "}"; 
					else
						$itemsJson .= "},"; 
				}
				unset($size, $count);
				
				
				$itemsJson .= "]";
				
				$itemsJson = urldecode($itemsJson);
				
				if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
					$response = '{"data":{"dataSet":'.$itemsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
				else
					$response = '{"data":{"dataSet":'.$itemsJson.'},"statusCode":200,"statusMsg":"成功"}';
			}
			else
				$response = '{"data":{"dataSet":[]},"statusCode":200,"statusMsg":"成功"}';
			unset($itemsJson, $currentUnit, $arraySize);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';		
		

		sendResponse('/albums/:id/kktItems',$response);
		unset($data, $value, $response, $itemsArray, $type ,$pageSize, $page, $pageCount);
    }
);

// 获取专辑课程列表，按单元分组
$app->post('/albums/:id/itemsInUnits', function($id) use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$type = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
	
		$log->INFO("request to get album items in units with id : ".$id);
		
		$itemsArray  = array(array());
		
		if(Db::getItems($id, NULL, NULL,NULL,$itemsArray,$pageCount))
		{

			$itemsJson = "[";
			$currentUnit = -1;
			$currentUnitTitle = " ";
			
			$log->INFO("The number of items found is : ".sizeof($itemsArray));
			if(sizeof($itemsArray) > 1)
			{
				for ($i = 0; $i < sizeof($itemsArray); $i++) 
				{
					if($itemsArray[$i]["unit"] != $currentUnit)
					{
						$currentUnit = $itemsArray[$i]["unit"];
						$currentUnitTitle = $itemsArray[$i]["unitTitle"];
						
						/////////////temp solution to fix the current com.mycoreedu.langduapp.ios problem/////////////////
						$appId = $app->request->headers->get('App-Id');	
						if("com.mycoreedu.langduapp.ios" == $appId)
						{
							if($i == 0)
								$itemsJson .= '{"unit":'.$currentUnit.',"unitTitle":"'.$currentUnitTitle.'","dataSet:":[';
							else
								$itemsJson .= ']},{"unit":'.$currentUnit.',"unitTitle":"'.$currentUnitTitle.'","dataSet:":[';
						}
						else
						{
						//////////////////////the above to be removed when langdu ios has next upgrad //////////////////						
						
						if($i == 0)
							$itemsJson .= '{"unit":'.$currentUnit.',"unitTitle":"'.$currentUnitTitle.'","dataSet":[';
						else
							$itemsJson .= ']},{"unit":'.$currentUnit.',"unitTitle":"'.$currentUnitTitle.'","dataSet":[';
						}
						unset($appId);
					}
					else
						$itemsJson .= ",";	
					
					$itemsJson .= "{"; 
					/*				
					for ($j = 0; $j < sizeof($itemsArray[$i]); $j++)
					{
						$itemsJson .=json_encode($itemsArray[$i][$j]);
						if($j != sizeof($itemsArray[$i])-1)
							$itemsJson .= ",";					
					}
					*/
					
					$size = sizeof($itemsArray[$i]);
					$count = 0;
					foreach ($itemsArray[$i] as $key => $value) 
					{
						if($key != "content")
							$itemsJson .='"'.$key.'":"'.$value.'"';
						else
							$itemsJson .='"'.$key.'":'.$value;
						$count++;
						if($count < $size) 
							$itemsJson .=",";
					
					}
					unset($size, $count);
					$itemsJson .= "}"; 
				}
				
				if(strlen($itemsJson) > 3) 
					$itemsJson .= "]}";
				
				$itemsJson .= "]";
				
				$itemsJson = urldecode($itemsJson);
				$response = '{"data":{"dataSet":'.$itemsJson.'},"statusCode":200,"statusMsg":"成功"}';
			}
			else
				$response = '{"data":{"dataSet":[]},"statusCode":200,"statusMsg":"成功"}';
			unset($itemsJson, $currentUnit);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
		
		Common::encryptAES($app,$response,$encrypted);
	    $log->INFO("encrypted: ".$encrypted);
		
		$response = '{"encryptedData":"'.$encrypted.'"}';
		
		sendResponse('/albums/:id/itemsInUnits',$response);
		unset($data, $value, $type, $pageSize, $page, $pageCount, $itemsArray, $response);
    }
);

// 获取专辑列表
$app->post('/articleAlbums', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get article albums with where : ".$where." and pageSize :".$pageSize." page :".$page);
		
		$albumsArray  = array(array());

		if(Db::getArticleAlbums($where,$pageSize,$page,$albumsArray,$pageCount))
		{
		
			$albumsJson = urldecode(json_encode($albumsArray));	
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$albumsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$albumsJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($albumsJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/articleAlbums',$response);
		unset($data, $value, $where, $pageSize, $page, $pageCount, $albumsArray, $response);
    }
);

// 获取某专辑的朗读列表
$app->post('/articleAlbums/:id/articles', function($id) use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$type = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get album items with id : ".$id." and pageSize :".$pageSize." page :".$page);
		
		$where = " album_id = '".$id."'";
		
		$thisArray  = array(array());

		if(Db::getArticles($where,$pageSize,$page,$thisArray,$pageCount))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			$thisJson = str_replace('"pages":"[','"pages":[',$thisJson);   
			$thisJson = str_replace(']",','],',$thisJson);   
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/articleAlbums/:id/articles',$response);
		unset($data, $value, $type, $pageSize, $page, $pageCount, $where, $thisArray);
	}
);

// 获取某个朗读专辑的文章列表
$app->post('/articleAlbums/:id/articlesInUnits', function($id) use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
	    $pageCount = NULL;
		
		$where = " album_id = '".$id."'";
	
		$log->INFO("request to get articleAlbums in units with id : ".$id);
		
		$itemsArray  = array(array());

		//if(Db::getItems($id, NULL, NULL,NULL,$itemsArray,$pageCount))
		if(Db::getArticles($where,NULL,NULL,$itemsArray,$pageCount))
		{

			$itemsJson = "[";
			$currentUnit = -1;
			
			$log->INFO("The number of items found is : ".sizeof($itemsArray));
			if(sizeof($itemsArray) > 1)
			{
			
				for ($i = 0; $i < sizeof($itemsArray); $i++) 
				{
					
					
					if($itemsArray[$i]["unit"] != $currentUnit)
					{
						$currentUnit = $itemsArray[$i]["unit"];

						if($i == 0)
							$itemsJson .= '{"unit":'.$currentUnit.',"dataSet":[';
						else
							$itemsJson .= ']},{"unit":'.$currentUnit.',"dataSet":[';

						
					}
					else
						$itemsJson .= ",";	
					
					$itemsJson .= "{"; 
					/*				
					for ($j = 0; $j < sizeof($itemsArray[$i]); $j++)
					{
						$itemsJson .=json_encode($itemsArray[$i][$j]);
						if($j != sizeof($itemsArray[$i])-1)
							$itemsJson .= ",";					
					}
					*/
					
					$size = sizeof($itemsArray[$i]);
					$count = 0;
					foreach ($itemsArray[$i] as $key => $value) 
					{
						if($key != "pages")
							$itemsJson .='"'.$key.'":"'.$value.'"';
						else
							$itemsJson .='"'.$key.'":'.$value;
						$count++;
						if($count < $size) 
							$itemsJson .=",";
					
					}
					unset($size, $count);
					$itemsJson .= "}"; 
				}
				
				if(strlen($itemsJson) > 3) 
					$itemsJson .= "]}";
				
				$itemsJson .= "]";
				
				$itemsJson = urldecode($itemsJson);
				$response = '{"data":{"dataSet":'.$itemsJson.'},"statusCode":200,"statusMsg":"成功"}';
			}
			else
				$response = '{"data":{"dataSet":[]},"statusCode":200,"statusMsg":"成功"}';
			unset($itemsJson, $currentUnit);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/articleAlbums/:id/articlesInUnits',$response);
		unset($data, $value, $pageCount, $where, $itemsArray, $response);
    }
);

// 获取可读文章列表
$app->post('/articles', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get articles with where : ".$where." and pageSize :".$pageSize." page :".$page);
		
		$thisArray  = array(array());

		if(Db::getArticles($where,$pageSize,$page,$thisArray,$pageCount))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			$thisJson = str_replace('"pages":"[','"pages":[',$thisJson);   
			$thisJson = str_replace(']",','],',$thisJson);   
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/articles',$response);
		unset($data, $value, $where, $pageSize, $page, $pageCount, $thisArray, $response);
    }
);

// 以文章id获取文章
$app->post('/article/:id', function($id) use($app, $log){
		checkHeader($app,false);
		$log->INFO("request to get article with id : ".$id);

		if(Db::getArticle($id, $title, $subTitle, $urlThumbImage, $tags, $urlDefaultBgMusic, $urlDefaultBgImage,  $pages,$unit, $urlExampleAudio, $exampleAuthorUserId,	$state, $weight, $counter1, $counter2, $counter3, $counter4))
		{
			
					
			$response = '{"data":{"id":"'.$id.'","title":"'.$title.'","subTitle":"'.$subTitle.'","urlThumbImage":"'.$urlThumbImage.'","tags":"'.$tags.'","urlDefaultBgMusic":"'.$urlDefaultBgMusic.'","urlDefaultBgImage":"'.$urlDefaultBgImage.'","pages":'.$pages.',"unit":'.$unit.',"urlExampleAudio":"'.$urlExampleAudio.'","exampleAuthorUserId":"'.$exampleAuthorUserId.'","state":'.$state.',"weight":'.$weight.',"counter1":'.$counter1.',"counter2":'.$counter2.',"counter3":'.$counter3.',"counter4":'.$counter4.'},"statusCode":200,"statusMsg":"请求成功"}';
			unset($title, $subTitle, $urlThumbImage, $tags, $urlDefaultBgMusic, $urlDefaultBgImage,  $pages,$unit, $urlExampleAudio, $exampleAuthorUserId, $state, $weight, $counter1, $counter2, $counter3, $counter4);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/article/:id',$response);
		unset($response);
    }
);



// 获取朗读作品列表
$app->post('/works', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get works with where : ".$where." and pageSize :".$pageSize." page :".$page);

		$thisArray  = array(array());

		if(Db::getWorks($where,$pageSize,$page,$thisArray,$pageCount))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			$thisJson = str_replace('"pages":"[','"pages":[',$thisJson);   
			$thisJson = str_replace(']",','],',$thisJson);   
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response =  '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
			
		sendResponse('/works',$response);
		unset($data, $value, $where, $pageSize, $page, $pageCount, $thisArray, $response);
    }
);

//以作品id获取作品
$app->post('/work/:id', function($id) use($app, $log){
		checkHeader($app,false);
		$log->INFO("request to get work with id : ".$id);
		if(Db::getWork($id,$articleId, $authorUserId, $urlWork, $urlBgImage, $urlAudio, $pages, $createTime, $publishTime, $score, $state, $counter1, $counter2, $counter3, $counter4))
			{	
				$response = '{"data":{"id":"'.$id.'","authorUserId":"'.$authorUserId.'","urlWork":"'.$urlWork.'","urlBgImage":"'.$urlBgImage.'","urlAudio":"'.$urlAudio.'","pages":'.$pages.',"createTime":"'.$createTime.'","publishTime":"'.$publishTime.'","score":"'.$score.'","state":"'.$state.'","counter1":'.$counter1.',"counter2":'.$counter2.',"counter3":'.$counter3.',"counter4":'.$counter4.'},"statusCode":200,"statusMsg":"请求成功"}';
				unset($articleId, $authorUserId, $urlWork, $urlBgImage, $urlAudio, $pages, $createTime, $publishTime, $score, $state, $counter1, $counter2, $counter3, $counter4);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/work/:id',$response);
		unset($response);
});

// 获取最新朗读作品列表
$app->post('/latestWorks', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get latest works with where : ".$where." and pageSize :".$pageSize." page :".$page);

		$thisArray  = array(array());

		if(Db::getLatestWorks($where,$pageSize,$page,$thisArray,$pageCount))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			$thisJson = str_replace('"pages":"[','"pages":[',$thisJson);   
			$thisJson = str_replace(']",','],',$thisJson);   
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/latestWorks',$response);
		unset($data, $value, $where, $pageSize, $page, $pageCount, $thisArray, $response);
    }
);

// 获取评论列表
$app->post('/comments', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get comments with where : ".$where." and pageSize :".$pageSize." page :".$page);

		$thisArray  = array(array());

		if(Db::getComments($where,$pageSize,$page,$thisArray,$pageCount))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisArray);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/comments',$response);
		unset($data, $value, $where, $pageSize, $page, $pageCount, $thisArray, $response);
    }
);

// 发表新评论
$app->post('/newComment', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$targetId = $value["targetId"];
		$targetType = $value["targetType"];
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$text = $value["text"];
		$log->INFO($userIdFromClient." request to add comment for targetId : ".$targetId." and targetType : ".$targetType);
		
	    if(!Common::isTextAllowed($text))
		{
			$response = '{"statusCode":401,"statusMsg":"评论失败，评论含有非法内容"}';
			$log->INFO("User(UserId: ".$userIdFromClient.") is requesting to add rejected BAD comment: ".$text );
			
		}
		else
		{
		
			$images = isset($value["images"])? $value["images"]: "";
			
			if(!isset($targetId) || !isset($targetType)|| !isset($text)|| !isset($images)  || !isset($userIdFromClient) )
				 $response = '{"statusCode":400,"statusMsg":"评论失败，参数错误"}';
			else
			{
				if(Db::addComment($commentId, $targetId,$targetType,$userIdFromClient,$text,$images))
					$response = '{"data":{"commentId":"'.$commentId.'"},"statusCode":200,"statusMsg":"评论成功"}';
				else
				{
					$response = '{"statusCode":500,"statusMsg":"评论失败，服务器错误"}';
				}
			}
			unset($images);
		}

		sendResponse('/newComment',$response);
		unset($data, $value, $targetType, $targetId, $userIdFromClient, $text, $response);
    }
);

// 删除评论
$app->delete('/comments/:id', function($id)use($app, $log){
		checkHeader($app,true);
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("request to delete comment with commentId : ".$id." by userId : ".$userIdFromClient);
		if(!isset($id) || !isset($userIdFromClient) )
			 $response = '{"statusCode":400,"statusMsg":"删除失败，参数错误"}';
		else
		{
			if(Db::deleteComment($id, $userIdFromClient))
				$response = '{"statusCode":200,"statusMsg":"删除成功"}';
			else
			{
				$response = '{"statusCode":500,"statusMsg":"删除失败，服务器错误"}';
			}
		}

		sendResponse('/comments/:id',$response);
		unset($userIdFromClient, $response);
    }
);


// 增加新订单
$app->post('/newOrder', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);

		$productId = isset($value["productId"])? $value["productId"]: NULL;
		$orderPrice = isset($value["orderPrice"])? $value["orderPrice"]: NULL;
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("request to add order for productId : ".$productId." and orderPrice : ".$orderPrice);
		if(!isset($productId) || !isset($orderPrice)|| !isset($userIdFromClient) )
			 $response = '{"statusCode":400,"statusMsg":"订单失败，参数错误"}';
		else
		{
			Db::updateOrderForProduct($userIdFromClient, $productId, "过期订单");	
			if(Db::addOrder($userIdFromClient, $productId,$orderPrice, $orderId))
				$response = '{"data":{"orderId":"'.$orderId.'"},"statusCode":200,"statusMsg":"订单成功"}';
			else
			{
				$response = '{"statusCode":500,"statusMsg":"订单失败，服务器错误"}';
			}
		}

		sendResponse('/newOrder',$response);
		unset($data, $value, $productId, $orderPrice, $userIdFromClient, $response);
    }
);

// 更改订单状态
$app->post('/updateOrder', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$orderId = $value["orderId"];
		$state = $value["state"];
		
		$transactionReceipt = $value["transactionReceipt"];
		//$transactionReceipt='sdfsdljfljeljrljglfmlsdf';
		$result=Common::validateApplePay($transactionReceipt); 
		
		$log->INFO("Apple transaction validation result = ".$result['status']." AND status message = ".$result['message']);
		
        if(!$result['status']){ 
            // 验证不通过 
			$response = '{"statusCode":402,"statusMsg":"购买失败，请重试或联系客服"}';
        }
		else
		{ 

			$userIdFromClient = $app->request->headers->get('User-Id');
			
			$log->INFO("request to change order for orderId : ".$orderId." and state : ".$state);
			if(!isset($orderId) || !isset($state)|| !isset($userIdFromClient) )
				 $response = '{"statusCode":400,"statusMsg":"更改失败，参数错误"}';
			else
			{
				$result = Db::updateOrder($userIdFromClient, $orderId, $state);
				
				if(1 == $result)
				{
					//Db::updateUserAuthorityState($userIdFromClient,1);  // client will call getMyAuthorities by itself after update order.
					
					$response = '{"statusCode":200,"statusMsg":"更改成功"}';
					unset($authorities);
				}
				else
					if(0 == $result)
						$response = '{"statusCode":401,"statusMsg":"无效操作，请检查参数的有效性"}';
					else
					{
						$response = '{"statusCode":500,"statusMsg":"更改失败，服务器错误"}';
					}
			}
		}

		sendResponse('/updateOrder',$response);
		unset($data, $value, $orderId, $state, $response, $userIdFromClient);
    }
);

// 获取用户的我的订单列表
$app->post('/myOrders', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
	
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$where = isset($value["where"])? $value["where"]: NULL;
		
		$userId = $app->request->headers->get('User-Id');
	
		$log->INFO("request to get my orders with userId : ".$userId." where : ".$where);
		
		$thisArray  = array(array());

		if(Db::getOrders($userId, $where, $thisArray))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			if(count($thisArray) == 1 && count($thisArray[0]) == 0)
				$response = '{"data":{"dataSet":[]},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/myOrders',$response);
		unset($data, $value, $where, $userId, $thisArray, $response);
    }
);


// 获取其他课程列表---需要继续开发
$app->post('/albums/:id/items', function($id) use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$type = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		if(isset( $value["type"]))
			$type = $value["type"];
			
		if(isset( $value["pageSize"]))
			$pageSize = $value["pageSize"];
		
		if(isset( $value["page"]))
			$page = $value["page"];
		
		$log->INFO("request to get album items with id : ".$id." type : ".$type." and pageSize :".$pageSize." page :".$page);
		
		
		
		$itemsArray  = array(array());

		if(Db::getItems($id, $type, $pageSize,$page,$itemsArray,$pageCount))
		{
		
			$itemsJson = urldecode(json_encode($itemsArray));	
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$itemsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"成功"}';
			else
				$response = '{"data":{"dataSet":'.$itemsJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($itemsJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/albums/:id/items',$response);
		unset($data, $value, $type, $pageSize, $page, $pageCount, $itemsArray, $response);
    }
);



//修改计数器的数值
$app->put('/counter', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
		$data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$id = NULL;  $category = NULL; $type = NULL;   $addValue = 0; 
		
		$id = $value["id"];
		
		if(isset($value["category"]))
			$category = $value["category"];
			
		if(isset($value["type"]))
			$type = $value["type"];
			
		$addValue = $value["addValue"];
	
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("counter - userId ".$userIdFromClient." is trying to update counter id=".$id." category=".$category." type=".$type." addValue=".$addValue);
		
		if(Db::updateCounter($id, $category, $type,$addValue))
			$response = '{"statusCode":200,"statusMsg":"更改成功"}';	
		else	
			$response = '{"statusCode":406,"statusMsg":"更改失败，错误的请求"}';
			
		sendResponse('/counter',$response);
		unset($data, $value, $id, $category, $type, $addValue, $userIdFromClient, $response);
    }
);

//修改用户的作品的计数器
$app->put('/userCounter',function() use($app, $log){
		checkHeader($app,true);
		$data=$app->request->getBody();

		$value=json_decode($data,true);
		
		$id = NULL;  $category = NULL; $type = NULL;   $addValue = 0; 
		
		$id = $value["id"];
		
		if(isset($value["category"]))
			$category = $value["category"];
			
		if(isset($value["type"]))
			$type = $value["type"];
			
		$addValue = $value["addValue"];
	
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("userCounter - userId ".$userIdFromClient." is trying to update counter id=".$id." category=".$category." type=".$type." addValue=".$addValue);
		
		if(Db::updateObjectCounter($id, $category, $type,$addValue,$counterValue))
		{
			if($category == "work") //增加人气值
				Db::addUserCounter($userIdFromClient, "popularity",1);
			
			
			if($category == "work" && $type == "like")
			{	
				if($addValue > 0)
					Db::addUserLike($userIdFromClient,$id);
				else	
					if($addValue < 0)
						Db::removeUserLike($userIdFromClient,$id);
				$userLikeIds = Db::getUserLike($userIdFromClient);
				$response = '{"data":{"value":"'.$counterValue.'","likeWorkIds":"'.$userLikeIds.'"},"statusCode":200,"statusMsg":"更改成功"}';
				unset($userLikeIds);	
			}
			else
				$response = '{"data":{"value":"'.$counterValue.'"},"statusCode":200,"statusMsg":"更改成功"}';	
		}
		else	
			$response = '{"statusCode":400,"statusMsg":"更改失败，错误的请求"}';

		sendResponse('/userCounter',$response);
		unset($data, $value, $id, $category, $type, $addValue, $userIdFromClient, $response, $counterValue);
}
);


//修改用户的作品的计数器
$app->put('/workLikeWechat',function() use($app, $log){

		$userAgent = $app->request->headers->get('User-Agent');
		$referer = $app->request->headers->get('Referer');
		
		
		$pos = strpos($userAgent, "MicroMessenger");

		if ($pos === false) 
		{
			$response = '{"statusCode":400,"statusMsg":"错误的请求"}';
			unset($userAgent, $referer, $pos);
		}
		else
		{
			
			$data=$app->request->getBody();
	
			$value=json_decode($data,true);
			
			$openId = NULL;  $verification = NULL;  $workId = NULL;$operation = NULL; $counterValue = NULL;
		
			if(isset($value["verification"]))
				$verification = $value["verification"];
				
			if(isset($value["openId"]))
				$openId = $value["openId"];
				
			if(isset($value["workId"]))
				$workId = $value["workId"];

			if(isset($value["operation"]))
				$operation = $value["operation"];
								
			
			$log->INFO("/workLikeWechat - openId ".$openId." is trying to update like with operation ".$operation." to workId=".$workId);
		
			if($openId == NULL || $workId == NULL || $operation == NULL)
				$response = '{"statusCode":400,"statusMsg":"错误的请求"}';
			else
			{
				$userLikeIds = Db::getUserLikeWechat($openId);
			    if($operation == "ADD")
				{
					if(!Db::isUserLikedWechat($openId,$workId))
					{
						Db::updateObjectCounter($workId, "work", "like",1,$counterValue);
						Db::addUserLikeWechat($openId,$workId);
						$response = '{"data":{"value":"'.$counterValue.'"},"statusCode":200,"statusMsg":"更改成功"}';	
					}
					else
						$response = '{"statusCode":401,"statusMsg":"非法操作"}';	
				}
				else
				{
					if(Db::isUserLikedWechat($openId,$workId)) 
					{
						Db::updateObjectCounter($workId, "work", "like",-1,$counterValue);
						Db::removeUserLikeWechat($openId,$workId);
						$response = '{"data":{"value":"'.$counterValue.'"},"statusCode":200,"statusMsg":"更改成功"}';	
					}
					else
						$response = '{"statusCode":401,"statusMsg":"非法操作"}';	
				}
				
				unset($userLikeIds);
			}
			unset($userAgent, $referer, $pos, $data, $value, $openId, $verification, $workId, $operation, $counterValue);

		}
	
		sendResponse('/workLikeWechat',$response);
		unset($response);
}
);


// 获取计数器
$app->post('/counters', function() use($app, $log){
	
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		/*
		$key = 'oScGU3fj8m/tDCyvsbEhwI91M1FcwvQqWuFpPoDHlFk='; //$response = base64_encode(openssl_random_pseudo_bytes(32));
		$iv = 'w2wJCnctEG09danPPI7SxQ=='; //$response = base64_encode(openssl_random_pseudo_bytes(16));
		$response = '内容: '.$data."\n";
		$encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA,base64_decode(Common::$iv));
		$response = '加密: '.base64_encode($encrypted)."\n";
		
		$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA,base64_decode(Common::$iv));
		$response = '解密: '.$decrypted."\n";
		*/
		
		Common::encryptAES($app,$data,$encrypted);
	    $log->INFO("encrypted: ".$encrypted);
		Common::decryptAES($app,$data,$decrypted);
		$log->INFO("decrypted: ".$decrypted);
		sendResponse('/counters',$encrypted.":".$decrypted);
	
		/*
		$value=json_decode($data,true);
		//$value=json_decode($decrypted,true);
		
		$counters = NULL;
		$ids = NULL;
		if(isset( $value["ids"]))
			$ids = $value["ids"];
	
		$log->INFO("request to get counter with ids: ".$ids);
		
		// convert abcd|efg|hijk ==> 'abcd','efg','hijk' :
		$ids = "'".str_replace("|","','",$ids)."'";
	
		$thisArray  = array();

		if(Db::getCounters($ids,$thisArray))
		{
			
			$thisJson = urldecode(json_encode($thisArray));	
			$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/counters',$response);*/
    }
);
// 上传文件
$app->post('/uploadFiles', function() use($app, $log){
	
	  //checkHeader($app,true);  //Gordon: remove this check because of ios app is not ready.
	

	
		//if(!isset($_FILES['files']) ||$_FILES['files']['error'] == UPLOAD_ERR_NO_FILE)
		//	{
		//		$response = '{"statusCode":406,"statusMsg":"更改失败，错误的请求"}';
		//		$log->INFO("uploadFiles - no file specified, uploade failure.");
		//	}else
		//	{
				$log->INFO("uploadFiles - step1.");
				
				if(Common::multiFileUpload("../upload/",$_FILES, $result, false))
				{
					$log->INFO("uploadFiles - upload succeed!!");
					$thisJson = urldecode(json_encode($result));
					$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"上传成功"}';
					unset($thisJson);
				}
				else
				{
					$log->INFO("uploadFiles - upload failed!!");
					$response = '{"statusCode":500,"statusMsg":"上传失败，服务器错误"}';
					$log->INFO("uploadFiles - server failure.");
				}
				
				$log->INFO("uploadFiles - upload completed!!");
				sendResponse('/uploadFiles',$response);
				unset($response);
				
		//	}
	}
);

// 上传私有文件，比如头像等
$app->post('/uploadPrivateFiles', function() use($app, $log){
	
		if(Common::multiFileUpload("../private/",$_FILES, $result,true))
		{
			$thisJson = urldecode(json_encode($result));
			$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"上传成功"}';
			unset($thisJson);
		}
		else
		{
			$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":500,"statusMsg":"上传失败，服务器错误"}';
			$log->INFO("uploadPrivateFiles - server failure.");
		}
		sendResponse('/uploadPrivateFiles',$response);
		unset($response);

	}
);

// 保存作品
$app->post('/newWork', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$data = urldecode($data);
		$log->INFO("dump request after URL decode: ".$data);
		
		$value=json_decode($data,true);
		
		$workId = NULL;
		if(isset($value["workId"]))
			$workId = $value["workId"];		
			
		
		$articleId = $value["articleId"];
		//$pages = urlencode(json_encode($value["pages"]));
		$pages = $value["pages"];
		/*
		for ($i = 0; $i < sizeof($pages); $i++) 
		{		
			$pages[$i]['text'] = json_decode( '"'.$pages[$i]['text'].'"');

		}  	*/
		$pages = Common::jsonFormat($pages);
				
		
		$urlAudio = $value["urlAudio"];
		$urlBgImage = $value["urlBgImage"];
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("request to add or save work for articleId : ".$articleId);
		if(!isset($pages) || !isset($urlAudio) || !isset($userIdFromClient) ){
			 $response = '{"statusCode":400,"statusMsg":"保存失败，参数错误"}';
			}
		else if(strlen($pages)<3){
			$response = '{"statusCode":400,"statusMsg":"保存失败，参数错误"}';
			}
		else{
			if($workId == NULL)
			{
				if(Db::addWork($workId,$articleId,$userIdFromClient,$urlBgImage,$urlAudio,$pages))
				{
					Db::addUserCounter($userIdFromClient, "productivity",1); //增加作品数
					$response = '{"data":{"workId":"'.$workId.'"},"statusCode":200,"statusMsg":"保存成功"}';
				}
				else
				{
					$response = '{"statusCode":500,"statusMsg":"保存失败，服务器错误"}';
				}
			}
			else
			{
				if(Db::updateWork($workId,$articleId,$userIdFromClient,$urlBgImage,$urlAudio,$pages))
				{
					$response = '{"data":{"workId":"'.$workId.'"},"statusCode":200,"statusMsg":"保存成功"}';
				}
				else
				{
					$response = '{"statusCode":500,"statusMsg":"保存失败，服务器错误"}';
				}				
			}
			
		}
		sendResponse('/newWork',$response);
		unset($data, $value, $workId, $articleId, $pages, $urlAudio, $urlBgImage, $userIdFromClient, $response);
    }
);

// 修改作品状态，如发表作品，私藏作品等。
$app->put('/workState', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$workIds = $value["workIds"];
		
		$state = $value["state"];
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("request to change work state for workId : ".$workIds." to state:".$state);
		
		if(!isset($workIds) || !isset($state) )
			 $response = '{"statusCode":400,"statusMsg":"操作失败，参数错误"}';
		else
		{
			$result = Db::updateWorkState($workIds,$state,$userIdFromClient);
			if(1 == $result )
				$response = '{"statusCode":200,"statusMsg":"操作成功"}';
			else
				if(0 == $result )
					$response = '{"statusCode":401,"statusMsg":"无效操作，系统没有改动任何作品。"}';
				else // -1
				{
					$response = '{"statusCode":500,"statusMsg":"操作失败，服务器错误"}';
				}
		}
		sendResponse('/workState',$response);
		unset($data, $value, $workId, $state, $userIdFromClient, $response);
    }
);


// 获取用户排名
$app->post('/userWorkBoard', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$period = NULL;$province = NULL;$city = NULL;$county = NULL;$limit = NULL;
			
		if(isset( $value["period"]))
			$period = $value["period"];
		if(isset( $value["type"]))
			$type = $value["type"];			
			
		if(isset( $value["province"]))
			$province = $value["province"];	
		if(isset( $value["city"]))
			$city = $value["city"];	
		if(isset( $value["county"]))
			$county = $value["county"];	
		if(isset( $value["limit"]))
			$limit = $value["limit"];											
			
		$log->INFO("request to get user board with period : ".$period." and province : ".$province." city : ".$city." county : ".$county." limit : ".$limit);
		
		$thisArray  = array(array());

		if(Db::getUserWorkBoard($period, $type, $province, $city, $county, $limit, $thisArray))
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($thisJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/userWorkBoard',$response);
		unset($data, $value, $period, $province, $city, $county, $limit, $thisArray, $response);
    }
);

//修改用户蝌蚪币余额
$app->put('/balance', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
		$data=$app->request->getBody();
		
		$value=json_decode($data,true);
		
		$addBalance = 0; 
		$type = "";
		$descrptn = "";
					
		if(isset( $value["addBalance"]))
			$addBalance = $value["addBalance"];	
		if(isset( $value["type"]))
			$type = $value["type"];	
		if(isset( $value["descrptn"]))
			$descrptn = $value["descrptn"];
	
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("balance - userId ".$userIdFromClient." is trying to update balance with addBalance=".$addBalance);
		
		$result = Db::updateBalance($userIdFromClient, $addBalance);
		if(1 === $result)
		{
			$currentBalance = Db::getBalance($userIdFromClient);
			
			Db::addTransaction($userIdFromClient, $addBalance, "蝌蚪币", $type, $descrptn, $currentBalance);
			
			$response = '{"data":{"balance":"'.$currentBalance.'"},"statusCode":200,"statusMsg":"更改成功"}';
			unset($currentBalance);	
		}
		else	
			if(0 === $result)
			{
				$response = '{"statusCode":400,"statusMsg":"更改失败，错误的请求"}';
			}			
			else
			{
				$currentBalance = Db::getBalance($userIdFromClient);
				'{"data":{"balance":"'.$currentBalance.'"},"statusCode":500,"statusMsg":"更改失败，服务器错误"}';
			}
			
		sendResponse('/balance',$response);
		unset($data, $value, $addBalance, $type, $descrptn, $result, $response);
    }
);

// 获取消费记录
$app->post('/transactions', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$pageSize = NULL; $page = NULL; $pageCount = NULL;
		
		$pageSize = getValue($value,"pageSize");
		$page = getValue($value,"page");
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("request to get transactions for user id : ".$userIdFromClient." and pageSize :".$pageSize." page :".$page);
		
		
		
		$trasactionsArray  = array(array());

		if(Db::getTrasactions($userIdFromClient, $pageSize,$page,$trasactionsArray,$pageCount))
		{
		
			$trasactionsJson = urldecode(json_encode($trasactionsArray));	
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$trasactionsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"请求成功"}';
			else
				$response = '{"data":{"dataSet":'.$trasactionsJson.'},"statusCode":200,"statusMsg":"请求成功"}';
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/transactions',$response);
		unset($data, $value, $pageSize, $page, $pageCount, $userIdFromClient, $trasactionsArray, $response);
    }
);



// 获取我的使用权限字符串
$app->post('/myAuthorities', function() use($app, $log){
		checkHeader($app,true);
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO("authorities - userId ".$userIdFromClient." is trying to get its authorities...");
		
		$authorities = Db::getAuthorities($userIdFromClient);
		if(NULL == $authorities)
			$response = '{"statusCode":500,"statusMsg":"获取权限失败，服务器错误"}';
		else
		{
			Db::updateUserAuthorityState($userIdFromClient,0);
			$response = '{"data":{"authorities":"'.$authorities.'"},"statusCode":200,"statusMsg":"获取成功"}';
		}

		sendResponse('/myAuthorities',$response);
		unset($response, $userIdFromClient, $authorities);
    }
);

// 获取广告列表
$app->post('/advertisements', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$where = NULL;
		if(isset( $value["where"]))
			$where = $value["where"];
	
		$log->INFO("request to get advertisements with where : ".$where);
			
		$advsArray  = array(array());

		if(Db::getAdvertisements($where,$advsArray))
		{
		
			$advsJson = urldecode(json_encode($advsArray));	
			$response = '{"data":{"dataSet":'.$advsJson.'},"statusCode":200,"statusMsg":"成功"}';
			unset($advsJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/advertisements',$response);
		unset($data, $value, $where, $advsArray, $response);
    }
);

// 发表新动态
$app->post('/newMoment', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$workId = getValue($value,"workId");
		$message = getValue($value,"message");
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		

		$log->INFO($userIdFromClient." request to add moment for workId : ".$workId);
		
	    if(!Common::isTextAllowed($message))
		{
			$response = '{"statusCode":401,"statusMsg":"发布失败，因为含有非法内容。请修改后重试。"}';
			$log->INFO("User(UserId: ".$userIdFromClient.") is requesting to add rejected BAD moment: ".$message );
			
		}
		else
		{
			
		
			if(!isset($workId) || !isset($userIdFromClient) )
				 $response = '{"statusCode":400,"statusMsg":"发布失败，参数错误"}';
			else
			{
				$message = Common::removeIllegalChars($message);
				
				if(Db::addMoment($momentId, $userIdFromClient,$message,$workId))
					$response = '{"data":{"momentId":"'.$momentId.'"},"statusCode":200,"statusMsg":"发布成功"}';
				else
				{
					$response = '{"statusCode":500,"statusMsg":"发布失败，服务器错误"}';
				}
			}
		}
		sendResponse('/newMoment',$response);
		unset($data, $value, $workId, $message, $userIdFromClient, $response);
    }
);

// 修改动态状态，如发表动态，私藏动态等。
$app->put('/momentState', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$momentIds = $value["momentIds"];
		
		$state = $value["state"];

		$userIdFromClient = $app->request->headers->get('User-Id');
		

		$log->INFO("request to change moment state for momentId : ".$momentIds." to state:".$state);
		
		if(!isset($momentIds) || !isset($state) )
			 $response = '{"statusCode":400,"statusMsg":"操作失败，参数错误"}';
		else
		{
			$result = Db::updateMomentState($momentIds,$state,$userIdFromClient);
			if(1 == $result )
				$response = '{"statusCode":200,"statusMsg":"操作成功"}';
			else
				if(0 == $result )
					$response = '{"statusCode":401,"statusMsg":"无效操作，系统没有改动任何动态。"}';
				else // -1
				{
					$response = '{"statusCode":500,"statusMsg":"操作失败，服务器错误"}';
				}
		}
		sendResponse('/momentState',$response);
		unset($data, $value, $momentIds, $state, $userIdFromClient, $response);
    }
);

// 获取动态列表
$app->post('/moments', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$pageSize = NULL; $page = NULL; $pageCount = NULL;
		
		$where = getValue($value,"where");
		$pageSize = getValue($value,"pageSize");
		$page = getValue($value,"page");
		
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$log->INFO($userIdFromClient." is requesting to get moments with pageSize :".$pageSize." page :".$page);

		$momentsArray  = array(array());
		if(Db::getDetailMoments($where,$pageSize,$page,$momentsArray,$pageCount))
		{
		
			$momentsJson = urldecode(json_encode($momentsArray));	
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$momentsJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"请求成功"}';
			else
				$response = '{"data":{"dataSet":'.$momentsJson.'},"statusCode":200,"statusMsg":"请求成功"}';
			unset($momentsJson);
		}
		else
			$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';

		sendResponse('/moments',$response);				
		unset($data, $value, $pageSize, $where, $page, $userIdFromClient, $momentsArray, $response);
    }
);

// 新举报问题
$app->post('/newReport', function() use($app, $log){
		checkHeader($app,false);
        //获取前台传过来的数据
        $data=$app->request->getBody();
	
		$log->INFO("dump request : ".$data);
		
		$value=json_decode($data,true);
		
		$targetId = $value["targetId"];
		$targetType = $value["targetType"];
		$userIdFromClient = $app->request->headers->get('User-Id');
		
		$text = $value["text"];
		$log->INFO("request to add report for targetId : ".$targetId." and targetType : ".$targetType);
		
		$images = isset($value["images"])? $value["images"]: "";
			
		if(!isset($targetId) || !isset($targetType)|| !isset($text)|| !isset($images)  || !isset($userIdFromClient) )
			 $response = '{"statusCode":400,"statusMsg":"报告失败，参数错误"}';
		else
		{
			if(Db::addReport($reportId, $targetId,$targetType,$userIdFromClient,$text,$images))
				$response = '{"data":{"reportId":"'.$reportId.'"},"statusCode":200,"statusMsg":"报告成功"}';
			else
			{
				$response = '{"statusCode":500,"statusMsg":"报告失败，服务器错误"}';
			}
		
		}
		sendResponse('/newReport',$response);
		unset($data, $value, $targetType, $targetId, $userIdFromClient, $text, $images, $response);
    }
);


// 获取评论列表
$app->post('/myMessages', function() use($app, $log){
		checkHeader($app,true);
        //获取前台传过来的数据
        $data=$app->request->getBody();
		
		$value=json_decode($data,true);
		$messageType = NULL;$pageSize = NULL; $page = NULL; $pageCount = NULL;
		
		
		$targetType = getValue($value,"targetType"); 
		
		if("Yes" == getValue($value,"isFromMe"))
			$isFromMe = true;
		else
			$isFromMe = false;
				
		$pageSize = getValue($value,"pageSize");
		$page = getValue($value,"page");
		$userIdFromClient = $app->request->headers->get('User-Id');
	
		$log->INFO($userIdFromClient." request to get message with isFromMe : ".$isFromMe." and pageSize :".$pageSize." page :".$page);


		$thisArray  = array(array());

		$result = Db::getMessages($userIdFromClient, $targetType, $isFromMe, $pageSize, $page, $thisArray, $pageCount);
		if($result > 0)
		{
		
			$thisJson = urldecode(json_encode($thisArray));	
			
			if($pageSize != NULL && $page != NULL && $pageCount != NULL)			
				$response = '{"data":{"dataSet":'.$thisJson.',"pageSize":'.$pageSize.',"pageCount":'.$pageCount.',"page":'.$page.'},"statusCode":200,"statusMsg":"请求成功"}';
			else
				$response = '{"data":{"dataSet":'.$thisJson.'},"statusCode":200,"statusMsg":"请求成功"}';
			unset($thisJson);
		}
		else
			if($result == -1)
			{
				$response = '{"statusCode":400,"statusMsg":"请求失败，请求错误"}';
			}
			else
			{
				$response = '{"statusCode":500,"statusMsg":"请求失败，服务器错误"}';
			}
		sendResponse('/myMessages',$response);
		unset($data, $value, $messageType, $pageSize, $page, $pageCount, $targetType, $isFromMe, $userIdFromClient, $thisArray, $result, $response);
			
    }
);

// 更新
$app->put(
    '/:id', 
    function($id) use($app){
        //获取前台传过来的数据
        $data=$app->request->put();
        $sql="update tk_city set CityName=:CityName,ZipCode=:ZipCode,letter=:letter,abbr=:abbr where CityID=:CityID";
        try{
            $pdo=getConnect();
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam("CityName",$data['CityName']);
            $stmt->bindParam("ZipCode",$data['ZipCode']);
            $stmt->bindParam("letter",$data['letter']);
            $stmt->bindParam("abbr",$data['abbr']);
            $stmt->bindParam("CityID",$id);
            $stmt->execute();
            $pdo=null;
            $response = json_encode($data);
        }catch(PDOException $e){
            echo 'hello';//'{"err":'.$e->getMessage().'}';
        }
        sendResponse('/works',$response);
    }
);

// 删除
function deleteHandle($id){
    $sql="delete from tk_city where CityID=:id";
    try{
        $pdo=getConnect();
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam("id",$id);
        $stmt->execute();
        $pdo=null;
    }catch(PDOException $e){
        echo '{"err":'.$e->getMessage().'}'; 
    }
}

//条件查询
$app->get("/:search","searchHandle");

//条件查询
function searchHandle($search){
    $sql="select * from tk_city where CityName like :search";
    try{
        $search="%".$search."%";
        $pdo=getConnect();
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam("search",$search);
        $stmt->execute();
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
         //json不支持中文,使用前先转码         
        foreach($data as $key=>$value){  
            foreach ($value as $k=>$v){
                $data[$key][$k]=urlencode($v);
            }
        }  
        echo urldecode(json_encode($data));

    }catch(PDOException $e){
       echo '{"err":'.$e->getMessage().'}'; 
    }
}


//连接数据库

function getConnect($h='localhost',$u="root",$p="HdiFswGNKoFs",$db="langdu"){
    $pdo = new PDO("mysql:host=$h;dbname=$db",$u,$p,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"set names utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

//运行应用
$app->run();