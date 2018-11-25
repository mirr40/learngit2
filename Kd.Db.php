<?php
ini_set('date.timezone','Asia/Shanghai');
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once 'Kd.Log.php';
require_once 'Kd.Common.php';


class Db
{
	private static $pdo = NULL;
	
	
		

	//连接数据库
	private static function getConnect($h='localhost',$u="root",$p="HdiFswGNKoFs",$db="kedou"){
		//$pdo = new PDO("mysql:host=$h;dbname=$db",$u,$p,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"set names utf8"));
		$pdo = new PDO("mysql:host=$h;dbname=$db",$u,$p,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"set names utf8mb4"));   //必须设置为utf8mb4, 否则emoji不工作。
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $pdo;
		
		Log::INFO("Kd.Db.php: ".__FUNCTION__." mem usage size:". (memory_get_usage() / 1024 / 1024));
	}
	
	public static function initDbConnection()
	{
		Log::Init();
		
		if(Db::$pdo == NULL)
			Db::$pdo = Db::getConnect();
		Log::INFO("Kd.Db.php: ".__FUNCTION__." mem usage size:". (memory_get_usage() / 1024 / 1024));	
		
	}
	
	public static function getUserProfile($mobile,&$password, &$userId,&$nickname, &$gender, &$grade, &$province, &$city, &$county, &$urlAvatar, &$signature,&$balance,&$birthday )
	{
		
		Db::initDbConnection();
		
		$sql="select user_id, password, nickname, gender, grade, province, city, county, url_avatar,signature,balance,birthday from kd_user where mobile = '{$mobile}'";
		
		Log::INFO("getUserProfile - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$userId = $row["user_id"];
				$password = $row["password"];
				$nickname = $row["nickname"];
				$gender = $row["gender"];
				$grade = $row["grade"];
				$province = $row["province"];
				$city = $row["city"];
				$county = $row["county"];
				$urlAvatar = $row["url_avatar"];
				$signature = $row["signature"];
				$balance = $row["balance"];
				$birthday = $row["birthday"];
			}

			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}		
		unset($sql);
		Log::INFO("Kd.Db.php: ".__FUNCTION__." mem usage size:". (memory_get_usage() / 1024 / 1024));
		
	}	

public static function getUserInfo($userId, &$nickname, &$gender, &$grade, &$province, &$city, &$county, &$urlAvatar, &$signature, &$birthday )
	{
		
		Db::initDbConnection();
		
		$sql="select mobile, nickname, gender, grade, province, city, county, url_avatar, signature, birthday from kd_user where user_id = '{$userId}'";
		
		Log::INFO("getUserInfo - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{	
				$userId = $userId;
				$nickname = $row["nickname"];
				$gender = $row["gender"];
				$grade = $row["grade"];
				$province = $row["province"];
				$city = $row["city"];
				$county = $row["county"];
				$urlAvatar = $row["url_avatar"];
				$signature = $row["signature"];
				$birthday = $row["birthday"];
				unset($row,$stmt,$sql);
				Log::INFO("Kd.Db.php: ".__FUNCTION__." mem usage size:". (memory_get_usage() / 1024 / 1024));
				return true;	
			}
			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);	
		Log::INFO("Kd.Db.php: ".__FUNCTION__." mem usage size:". (memory_get_usage() / 1024 / 1024));
		return false;
	}	

	public static function getUserProfileByUserId($userId,&$password, &$mobile,&$nickname, &$gender, &$grade, &$province, &$city, &$county, &$urlAvatar, &$signature, &$birthday )
	{
		
		Db::initDbConnection();
		
		$sql="select mobile, password, nickname, gender, grade, province, city, county, url_avatar, signature,birthday from kd_user where user_id = {$userId}";
		
		Log::INFO("getUserProfileByUserId - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$mobile = $row["mobile"];
				$password = $row["password"];
				$nickname = $row["nickname"];
				$gender = $row["gender"];
				$grade = $row["grade"];
				$province = $row["province"];
				$city = $row["city"];
				$county = $row["county"];
				$urlAvatar = $row["url_avatar"];
				$signature = $row["signature"];
				$birthday = $row["birthday"];
				
			}
			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());	
		}		
		unset($sql);
	}		

	public static function updateUserProfile($userId,$nickname, $gender, $grade, $province, $city, $county, $signature,$urlAvatar,$birthday,$appId )
	{
		
		Db::initDbConnection();
		
		if("com.mycoreedu.keketingapp.ios" == $appId || "com.mycoreedu.keketingapp.android" == $appId)
			$sql="update kd_user set nickname='{$nickname}', gender={$gender}, grade='{$grade}', url_avatar='{$urlAvatar}', birthday='{$birthday}' where user_id = {$userId}";
		else
		$sql="update kd_user set nickname='{$nickname}', gender={$gender}, grade='{$grade}', province='{$province}', city='{$city}', county='{$county}', signature='{$signature}', url_avatar='{$urlAvatar}', birthday='{$birthday}' where user_id = {$userId}";
		
		Log::INFO("updateUserProfile - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			//$affected = $stmt->rowCount();	
									
			//if($affected === 0)
			//{
			//	return 0;
			//}
			//else
			//{
			    unset($sql, $stmt);	
				Log::INFO("updateUserProfile for userId={$userId}");		
				return true;
			//}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;			
	}	

	public static function updateUserPassword($userId,$oldPassword, $newPassword)
	{
		
		Db::initDbConnection();
		
		$sql="update kd_user set password='{$newPassword}' where user_id = {$userId} and password='{$oldPassword}' ";
		
		Log::INFO("updateUserPassword - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();
			unset($sql, $stmt);	
			Log::INFO("updateUserPassword for userId={$userId} successfully");		
				return true;
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;			
	}
	public static function updateUserPasswordWithMobile($mobile,$newPassword)
	{
		
		Db::initDbConnection();
		
		$sql="update kd_user set password='{$newPassword}' where mobile = '{$mobile}' ";
		
		Log::INFO("updateUserPasswordWithMobile - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();
			unset($sql, $stmt);	
			Log::INFO("updateUserPasswordWithMobile for mobile={$mobile} successfully");		
				return true;
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;			
	}
	
	public static function updateUserMobile($userId,$mobile)
	{
		
		Db::initDbConnection();
		
		$sql="update kd_user set mobile='{$mobile}' where user_id = {$userId} ";
		
		Log::INFO("updateUserMobile - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();
			unset($sql, $stmt);	
			Log::INFO("updateUserMobile for userId={$userId} successfully");		
				return true;
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;			
	}	
	public static function updateUserAuthorityState($userId,$state)
	{
		
		Db::initDbConnection();
		
		$sql="update kd_user set authority_state_kkt={$state} where user_id = {$userId} ";
		
		Log::INFO("updateUserAuthorityState - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();
			unset($sql, $stmt);	
			Log::INFO("updateUserAuthorityState for userId={$userId} successfully");		
				return true;
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;			
	}		
	public static function getUserAuthorityState($userId,&$authorityState)
	{
		
		Db::initDbConnection();
		
		$sql="select authority_state_kkt from kd_user where user_id = {$userId}";
		
		Log::INFO("getUserAuthorityState - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$authorityState = $row["authority_state_kkt"];
				
			}
			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());	
		}		
		unset($sql);
	}			
	/*	
	public static function updateSessionId($userId)
	{
	
		Db::initDbConnection();
		$newSessionId = date('Y-m-d H:i:s');
		$sql = "update kd_user set session_id = '{$newSessionId}'  where user_id='{$userId}'";
		
		Log::DEBUG("updateSessionId - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				return NULL;
			}
			else
			{
				Log::INFO("update updateSessionId for userId={$userId} with new session id = {$newSessionId}");		
				return $newSessionId;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}				
		return NULL;			
	}			
	*/
	public static function updateSessionId($userId,$appId)
	{
	
		Db::initDbConnection();
		$newSessionId = date('Y-m-d H:i:s');
		
		if("com.mycoreedu.langduapp.ios" == $appId || "com.mycoreedu.langduapp.android" == $appId)
			$sql = "update kd_user set session_id = '{$newSessionId}'  where user_id='{$userId}'";
		else
			$sql = "update kd_user set kkt_session_id = '{$newSessionId}'  where user_id='{$userId}'";
		
		Log::DEBUG("updateSessionId - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $affected);
				return NULL;
			}
			else
			{
				unset($sql, $stmt, $affected);
				Log::INFO("update updateSessionId for userId={$userId} and appId={$appId} with new session id = {$newSessionId}");		
				return $newSessionId;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return NULL;			
	}	
	
	/*
	public static function getSessionId($userId)
	{
		Db::initDbConnection();
		
		$sql="select session_id from kd_user where user_id = {$userId}";
		
		Log::INFO("getSessionId - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$sessionId = $row["session_id"];
				return $sessionId;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		return NULL;
	}	
	*/
	
	public static function getSessionId($userId, $appId)
	{
		Db::initDbConnection();
		
		if("com.mycoreedu.langduapp.ios" == $appId || "com.mycoreedu.langduapp.android" == $appId)
			$sql="select session_id from kd_user where user_id = {$userId}";
		else
			$sql="select kkt_session_id AS session_id from kd_user where user_id = {$userId}";
		
		Log::INFO("getSessionId - SQL to be executed : ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{	
				
				$sessionId = $row["session_id"];
				unset($sql, $stmt, $row);
				return $sessionId;
			}
			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);
		return NULL;
	}	
	
	public static function addUserInRegistation($mobile,$password,&$userId)
	{

		Db::initDbConnection();
		
		$appId = "";
		$sql = "insert into kd_user(`mobile`,`password`) values('{$mobile}','{$password}')";
		Log::DEBUG("addUserFromApp - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $row);
				return false;
			}
			else
			{
				unset($sql, $stmt, $row);
				$userId=Db::$pdo->lastInsertId();
				
				Db::updateUserProfile($userId,$userId, -1, -1, "未设置", "未设置", "未设置", "蝌蚪朗读，记录成长的声音","http://app.kedoulangdu.com/logo/kedoulangdu_v.jpg","2010-01-01" ,$appId);
				Log::INFO("addUserFromApp - insert kd_user with mobile ".$mobile." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}	
	public static function addSmsCode($mobile,$smsCode)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_sms_verification(`mobile`,`sms_code`) values('{$mobile}','{$smsCode}')";
		Log::DEBUG("addSmsCode - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $row);
				return false;
			}
			else
			{
				unset($sql, $stmt, $row);
				Log::INFO("insert kd_sms_verification with smsCode ".$smsCode." successfully for mobile="."{$mobile}");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;		
	}
	public static function updateSmsCode($mobile,$smsCode)
	{
	
		Db::initDbConnection();
		$sql = "update kd_sms_verification set sms_code = '{$smsCode}', sent_time = now()  where mobile='{$mobile}'";
		
		Log::DEBUG("updateSmsCode - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $row);
				return false;
			}
			else
			{
				unset($sql, $stmt, $row);
				Log::INFO("update kd_sms_verification with smsCode ".$smsCode." successfully for mobile="."{$mobile}");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;			
	}			
	public static function saveSmsCode($mobile,$smsCode)
	{
	
		if(Db::updateSmsCode($mobile,$smsCode))
			return true;
		else
			return Db::addSmsCode($mobile,$smsCode);
	}	
	public static function getSmsCode($mobile, &$smsCode)
	{
		Db::initDbConnection();
		$sql = "select sms_code, sent_time from kd_sms_verification where mobile ='{$mobile}'";
		
		Log::DEBUG("SQL to be executed: ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{	
				
				$smsCode = $row["sms_code"];
				$sentTime = $row["sent_time"];
				if(strtotime("-10 minute") - strtotime($sentTime) > 0)  //valid for 2 days only
					$smsCode = "expired";
				unset($sql, $stmt, $row);
				return true;
			}
			unset($stmt, $row);
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;
		
	}	
	
	
	public static function getPageCount($sql, $pageSize, &$pageCount)
	{
		Db::initDbConnection();
		if($pageSize == NULL || $sql == NULL || $pageSize <= 0)
			return false;
			
		Log::INFO("getPageCount - SQL to be executed : ".$sql);
			
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{	
				$count = $row["thisCount"];
				$pageCount = ceil($count/$pageSize);
				unset($stmt, $row, $count);
				return true;
			}
			unset($stmt, $row);
	
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		return false;						
		
	}
	
	/*
	public static function getAlbums($where, $pageSize, $page, &$albumsArray, &$pageCount)
	{
		Db::initDbConnection();

		$sql = "SELECT cnt_album.*, kd_product.price AS price, 
		kd_product.promotion_price AS promotion_price,kd_product.duration AS duration,kd_product.url_thumb_image AS product_url_thumb_image,kd_product.title AS product_title,kd_product.descrptn AS product_descrptn FROM cnt_album, kd_product WHERE cnt_album.state !=0 AND cnt_album.product_id = kd_product.id";		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM cnt_album, kd_product WHERE cnt_album.state != 0 AND cnt_album.product_id = kd_product.id";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY weight ASC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getAlbums - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$albumsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql, $sqlPageCount);
				return false;
		}
		unset($sql, $sqlPageCount);	
		return true;	
	}	
	*/	
	public static function getAlbums($where, $pageSize, $page, &$albumsArray, &$pageCount, $appId)
	{
		Db::initDbConnection();

		$sql = "SELECT cnt_album.* FROM cnt_album WHERE cnt_album.state !=0 ";		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM cnt_album WHERE cnt_album.state != 0 ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY weight ASC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getAlbums - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				
				$productsArray  = array(array());
				
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						if("productId" == $k)
						{
							$k = "product";
							
							if($appId == "com.mycoreedu.keketingapp.ios")
								Db::getProducts(" LOCATE(id,'".$v."')>0 AND apple_product_id IS NOT NULL ", $productsArray);
							else
								Db::getProducts(" LOCATE(id,'".$v."')>0", $productsArray);
							
							$albumsArray[$key][$k] = $productsArray;
							unset($productsArray);
						}
						else
							$albumsArray[$key][$k]=urlencode($v);
				
					}
				}  		
				unset($stmt, $data,$productsArray);
				
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql, $sqlPageCount);
				return false;
		}
		unset($sql, $sqlPageCount);	
		return true;	
	}	
	// public static function getProductsVip($vip, &$vipArray){
	// 	Db::initDbConnection();

	// 	$sql = "select scope from kd_product where id = '{$vip}'";
	// 	Log::INFO("getProductsVip - SQL to be executed : ".$sql);
	// 	try{
	// 		$stmt = Db::$pdo->query($sql);
	// 		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	// 		$vipArray = $data;
	// 	}catch(PDOException $e){
	// 		Log::ERROR("MySQL Error: ".$e->getMessage());
	// 			return false;
	// 	}
	// 	return true;
	// }


	public static function getAlbumsByAuth($where, &$albumsArray){
		Db::initDbConnection();
		$sql = "SELECT cnt_album.* FROM cnt_album WHERE cnt_album.state !=0 ";		
		$albumsArray = array(array());
		if($where != NULL)
			$sql = $sql." AND ".$where;

		$sql = $sql." ORDER BY weight ASC"; 	
		
		Log::INFO("getAlbums - SQL to be executed : ".$sql);
		try{
			$stmt = Db::$pdo->query($sql);
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
 			$i=0;
			foreach($data as $key=>$value){ 
				
				if($value['tags']!=NULL && strpos($value['tags'],"语文")!=false) {
						
						foreach ($value as $k=>$v){
						
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						if($k == 'subCategory')
						$albumsArray[$i][$k]=urlencode("语文");
						else
						$albumsArray[$i][$k]=urlencode($v);	
					}
					$i++;
				}else if($value['tags']!=NULL && strpos($value['tags'],"英语")!=false) {
					foreach ($value as $k=>$v){
						
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						if($k == 'subCategory')
						$albumsArray[$i][$k]=urlencode("英语");
						else
						$albumsArray[$i][$k]=urlencode($v);	
					}
					$i++;
				}else if($value['tags']!=NULL && strpos($value['tags'],"数学")!=false) {
					foreach ($value as $k=>$v){
						
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						if($k == 'subCategory')
						$albumsArray[$i][$k]=urlencode("数学");
						else
						$albumsArray[$i][$k]=urlencode($v);	
					}
					$i++;
				}
			}
			// foreach($albumsArray as $key=>$value){
			// 	$albumArray[]=$albumsArray[$key];
			// }
				
		}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql);
				return false;
		}
		unset($sql);	
		return true;
	}


/*
	public static function getSuggestedAlbums($where, &$albumsArray)
	{
		Db::initDbConnection();

		$sql = "SELECT cnt_album.*, kd_product.price AS price, 
		kd_product.promotion_price AS promotion_price,kd_product.duration AS duration,kd_product.url_thumb_image AS product_url_thumb_image ,kd_product.title AS product_title,kd_product.descrptn AS product_descrptn FROM cnt_album, kd_product WHERE cnt_album.state !=0 AND cnt_album.product_id = kd_product.id AND cnt_album.tags LIKE '%精选%' ";		
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			
		}
		
		$sql = $sql." ORDER BY cnt_album.sub_category ASC"; 
	
		Log::INFO("getSuggestedAlbums - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$albumsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql);
				return false;
		}	
		
		unset($sql);
		return true;	
	}	
*/
	public static function getSuggestedAlbums($where, &$albumsArray, $appId)
	{
		Db::initDbConnection();

		$sql = "SELECT cnt_album.* FROM cnt_album WHERE cnt_album.state !=0 AND cnt_album.tags LIKE '%精选%' ";		
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			
		}
		
		$sql = $sql." ORDER BY cnt_album.sub_category ASC"; 
	
		Log::INFO("getSuggestedAlbums - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				$productsArray  = array(array());
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						if("productId" == $k)
						{
							$k = "product";
							if($appId == "com.mycoreedu.keketingapp.ios")
								Db::getProducts(" LOCATE(id,'".$v."')>0 AND apple_product_id IS NOT NULL ", $productsArray);
							else
								Db::getProducts(" LOCATE(id,'".$v."')>0", $productsArray);
								
							$albumsArray[$key][$k] = $productsArray;
							unset($productsArray);
						}
						else
							$albumsArray[$key][$k]=urlencode($v);						
					
						
					}
				}  		
				unset($stmt, $data,$productsArray);
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql);
				return false;
		}	
		
		unset($sql);
		return true;	
	}	


	public static function getItems($albumId, $type, $pageSize, $page, &$itemsArray, &$pageCount)
	{
		Db::initDbConnection();
		
		$sql = "SELECT * FROM cnt_item WHERE state != 0 AND album_id = '".$albumId."'";
		$sqlPageCount = "SELECT count(*) AS thisCount FROM cnt_item WHERE state != 0 AND album_id = '".$albumId."'";
		/*
		if($type == "kkt")
		{
			$sql = "SELECT * FROM cnt_kkt WHERE album_id = '".$albumId."'";
			$sqlPageCount = "SELECT count(*) AS thisCount FROM cnt_kkt WHERE album_id = '".$albumId."'";
		}
		*/

		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY weight ASC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getItems - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$itemsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}	
		unset($sql, $sqlPageCount);
		return true;	
	}			

	public static function getProducts($where, &$productsArray)
	{
		Db::initDbConnection();

		$sql = "SELECT kd_product.id as product_id, kd_product.apple_product_id as apple_product_id, kd_product.price AS product_price, 
		kd_product.promotion_price AS product_promotion_price,kd_product.duration AS product_duration,kd_product.url_thumb_image AS product_url_thumb_image,kd_product.title AS product_title,kd_product.descrptn AS product_descrptn ,kd_product.state AS product_state ,kd_product.counter_total_sold AS product_counter_total_sold,kd_product.counter_total_order AS product_counter_total_order,kd_product.counter_monthly_sold AS product_counter_monthly_sold,kd_product.counter_monthly_order AS product_counter_monthly_order FROM kd_product WHERE kd_product.state > 0 ";		
	
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
		}
	
		$sql = $sql." ORDER BY weight ASC"; 
				
		Log::INFO("getProducts - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$productsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				unset($sql);
				return false;
		}
		unset($sql);	
		return true;	
	}		

	public static function getArticleAlbums($where, $pageSize, $page, &$albumsArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT * FROM ld_album WHERE ld_album.state != 0 ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM ld_album WHERE ld_album.state != 0 ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}		
		
	
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY weight ASC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getArticleAlbums - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$albumsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql, $sqlPageCount);	
		return true;	
	}		


	public static function getArticles($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT ld_article.*, ld_album.category FROM ld_article,ld_album WHERE ld_article.state != 0 AND ld_album.id = ld_article.album_id ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM ld_article,ld_album WHERE ld_article.state != 0 AND ld_album.id = ld_article.album_id ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY unit, weight ASC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getArticles - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						
						
						
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql);	
		return true;	
	}	

public static function getWork($workId, &$articleId, &$authorUserId, &$urlWork, &$urlBgImage, &$urlAudio, &$pages, &$createTime, &$publishTime, &$score, &$state, &$counter1, &$counter2, &$counter3, &$counter4)
	{
		Db::initDbConnection();

		$sql = "select 	article_id, author_user_id,url_work,url_bg_image,url_audio,pages,create_time,publish_time,score,state,counter1,counter2,counter3,counter4 from ld_work where id='{$workId}'";

		Log::DEBUG("getWork - SQL to be executed: ".$sql);

		try{
			$stmt=DB::$pdo->query($sql);

			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row){
				$authorUserId = $row['author_user_id'];
				$articleId = $row['article_id'];
				$urlWork = $row['url_work'];
				$urlBgImage = $row['url_bg_image'];
				$urlAudio = $row['url_audio'];
				$pages = $row['pages'];
				$createTime = $row['create_time'];
				$publishTime = $row['publish_time'];

				$score = $row['score'];
				$state = $row['state'];

				$counter1 = $row['counter1'];
				$counter2 = $row['counter2'];
				$counter3 = $row['counter3'];
				$counter4 = $row['counter4'];
				unset($stmt, $row, $sql);
				return true;
			}
			unset($stmt, $row);
		}
		catch(PDOException $e){
				Log::ERROR("MYSQL Error: ".$e->getMessage());
		}
		unset($sql);
		return false;
	}
	

	public static function getWorks($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT ld_work.*, ld_article.title AS article_title, ld_article.url_thumb_image FROM ld_work,ld_article WHERE ld_work.article_id = ld_article.id  ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM ld_work,ld_article WHERE ld_work.article_id = ld_article.id ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY update_time DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getWorks - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						
						
						
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
	
			}catch(PDOException $e){
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}	
		return true;	
	}	
	
	public static function getLatestWorks($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
		$whereStmt1 = $where == NULL ? "" : " AND ".$where;
		$whereStmt2 = $where == NULL ? "" : " WHERE ".$where;
				
		$sql = "SELECT ld_work.*, ld_article.title as article_title, ld_article.url_thumb_image, kd_user.nickname as author_nickname FROM ld_work, ld_article, kd_user where ld_work.article_id = ld_article.id and ld_work.author_user_id = kd_user.user_id ".$whereStmt1;
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM ld_work ".$whereStmt2." LIMIT 200" ;
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY publish_time DESC "; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getLastestWorks - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}	
		unset($sql, $sqlPageCount);
		return true;	
	}		
	
	public static function addWork(&$workId,$articleId,$userId,$urlBgImage,$urlAudio,$pages)
	{

		Db::initDbConnection();
		
		$sql = "insert into ld_work(`article_id`,`author_user_id`,`url_bg_image`,`url_audio`,`pages`) values('{$articleId}',{$userId},'{$urlBgImage}','{$urlAudio}','{$pages}')";
		Log::DEBUG("addWork - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{	
				unset($stmt, $affected, $sql);
				return false;
			}
			else
			{
				$workId=Db::$pdo->lastInsertId();
				unset($stmt, $affected, $sql);
				if(($workId >0 ) && Db::updateUrlWork($workId,$userId,'http://app.kedoulangdu.com/h5/l.php?id='.$workId))
				{
					Log::INFO("addWork - insert kd_work with workId ".$workId." successfully");		
					return true;
				}
				else
					return false;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}	
	

	public static function updateWork($workId,$articleId,$userId,$urlBgImage,$urlAudio,$pages)
	{

		Db::initDbConnection();
		
		$sql="update ld_work set article_id = '{$articleId}', url_bg_image = '{$urlBgImage}',url_audio ='{$urlAudio}', pages = '{$pages}', update_time= now() where id = {$workId} and author_user_id = {$userId}";
		
		Log::INFO("updateWork - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			unset($stmt, $sql);
			Log::INFO("updateWork for workId={$workId}");		
			return true;
			
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;	
	}	


	
	public static function updateUrlWork($workId,$userId,$urlWork)	
	{
		Db::initDbConnection();
		
		$sql="update ld_work set url_work='{$urlWork}' where id = {$workId} and author_user_id = {$userId}";
		
		Log::INFO("updateUrlWork - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			unset($stmt, $sql);
			Log::INFO("updateUrlWork for workId={$workId}");		
			return true;
			
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;			
	}	
	
		
	public static function getComments($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT kd_comment.*, kd_user.nickname, kd_user.url_avatar AS user_avatar FROM kd_comment,kd_user WHERE kd_comment.author_user_id=kd_user.user_id AND kd_comment.state > 0 ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_comment WHERE kd_comment.state > 0  ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY create_time DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getComments - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						
						$thisArray[$key][$k]=urlencode($v);
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql);	
		return true;	
	}	
	
	public static function addComment(&$commentId,$targetId,$targetType,$userId,$text,$images)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_comment(`target_id`,`target_type`,`author_user_id`,`text`,`images`) values('{$targetId}','{$targetType}',{$userId},'{$text}','{$images}')";
		Log::DEBUG("addComment - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{	
				unset($stmt, $sql, $affected);
				return false;
			}
			else
			{
				unset($stmt, $sql, $affected);
				$commentId=Db::$pdo->lastInsertId();
				Log::INFO("addComment - insert kd_comment with commentId ".$commentId." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;		
	}	
	
	public static function deleteComment($commentId,$userIdFromClient)	
	{
		Db::initDbConnection();
		
		$sql="update kd_comment set state=-1 where id = {$commentId} and author_user_id = {$userIdFromClient}";
		
		Log::INFO("deleteComment - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			unset($stmt, $sql);
			Log::INFO("deleteComment for commentId={$commentId}");		
			return true;
			
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;			
	}	
	
	public static function addMoment(&$momentId, $userId,$message,$workId)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_moment(`author_user_id`,`message`,`work_id`) values({$userId},'{$message}',{$workId})";
		Log::DEBUG("addMoment - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($stmt, $sql, $affected);
				return false;
			}
			else
			{
				unset($stmt, $sql, $affected);
				$momentId=Db::$pdo->lastInsertId();
				Log::INFO("addMoment - insert kd_moment with momentId ".$momentId." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}		
	
		
	public static function addOrder($userIdFromClient, $productId,$orderPrice, &$orderId)	
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_order(`product_id`,`user_id`,`order_price`) values('{$productId}','{$userIdFromClient}',{$orderPrice})";
		Log::DEBUG("addOrder - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($stmt, $sql, $affected);
				return false;
			}
			else
			{
				unset($stmt, $sql, $affected);
				$orderId=Db::$pdo->lastInsertId();
				Log::INFO("addOrder - insert kd_order with orderId ".$orderId." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;		
	}

	public static function updateOrder($userIdFromClient, $orderId, $state)	
	{
		Db::initDbConnection();
		
		if($state == "购买成功")
		{
			$sql="UPDATE kd_order SET state='{$state}', start_time = now(), end_time = now()+ INTERVAL (SELECT duration FROM kd_product where kd_product.id = kd_order.product_id) DAY where id = {$orderId} and user_id = {$userIdFromClient} and state <>'{$state}'";
		}
		else
			if($state == "申请退款")
			{
				$sql="UPDATE kd_order SET state='{$state}', request_refund_time = now() where id = {$orderId} and user_id = {$userIdFromClient} ";
			}
		else
			$sql="update kd_order set state='{$state}' where id = {$orderId} and user_id = {$userIdFromClient}";
		
		Log::INFO("updateOrder - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($stmt, $sql, $affected);
				return 0;
			}
			else
			{
				unset($stmt, $sql, $affected);
				Log::INFO("updateOrder for orderId={$orderId}");		
				return 1;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return -1;			
	}	

	public static function updateOrderForProduct($userIdFromClient, $productId, $state)	
	{
		Db::initDbConnection();
		
		$sql="update kd_order set state='{$state}' where product_id = '{$productId}' and user_id = {$userIdFromClient}";
		
		Log::INFO("updateOrderForProduct - SQL to be executed : ".$sql);
		
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($stmt, $sql, $affected);
				return 0;
			}
			else
			{
				unset($stmt, $sql, $affected);
				Log::INFO("updateOrderForProduct for product_id = {$productId} and user_id = {$userIdFromClient}");		
				return 1;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return -1;			
	}	

		
	public static function getOrders($userId,$where,&$thisArray)
	{
		Db::initDbConnection();
		
		if(NULL != $where)
			$where = " and ".$where;
		
		$sql = "SELECT kd_order.id AS id, kd_order.order_time AS order_time, kd_order.start_time AS start_time, kd_order.end_time AS end_time, kd_order.order_price AS order_price, kd_order.state AS state, kd_product.title AS title, kd_order.product_id AS product_id, kd_product.apple_product_id AS apple_product_id,kd_product.descrptn AS descrptn,  kd_product.type AS type, kd_product.scope AS scope, kd_product.duration AS duration, kd_product.url_thumb_image AS url_thumb_image  FROM kd_order, kd_product WHERE user_id = '".$userId."'".$where." and kd_order.product_id = kd_product.id ORDER BY order_time DESC";

		Log::INFO("getOrders - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}	
		unset($sql);
		return true;	
	}	
	

	public static function updateWorkState($workIds,$state,$userId)
	{
		
		$workIdArr = explode('|',$workIds);
		
		$where = " WHERE author_user_id = {$userId} ";
		$size = count($workIdArr);
		
		if($size == 1)
			 $where = $where." AND id = ".$workIdArr[0];
		else
		{
		
			for($index=0;$index<$size;$index++) 
			{ 
						
				if( 0 == $index)
					$where = $where." AND ((id = ".$workIdArr[$index].") ";
				else
					if(($size-1) == $index)
						$where = $where." OR (id = ".$workIdArr[$index].")) ";
					else
						$where = $where." OR (id = ".$workIdArr[$index].") ";
			} 
		}
	
		Db::initDbConnection();
		if($state > 0)  // set to publish to friends(1) or public(2)
			$sql = "update ld_work set state = {$state}, publish_time = now(), update_time = now() ".$where;
        else			
			$sql = "update ld_work set state = {$state}, update_time = now() ".$where;
			
		Log::DEBUG("updateWorkState - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($stmt, $sql, $where, $size, $workIdArr, $affected);
				return 0;
			}
			else
			{
				unset($stmt, $sql, $where, $size, $workIdArr, $affected);
				Log::INFO("update ld_work state for id ".$workIds." successfully for author_user_id=".$userId);		
				return 1;
			}
		}catch(PDOException $e){
			unset( $sql, $where, $size, $workIdArr);
			Log::ERROR("MySQL Error: ".$e->getMessage());
			return -1;
			
		}
		unset( $sql, $where, $size, $workIdArr);				
		return -1;			
	}			
	
	
	public static function getArticle($articleId, &$title, &$subTitle, &$urlThumbImage, &$tags, &$urlDefaultBgMusic, &$urlDefaultBgImage,  &$pages,  &$unit, &$urlExampleAudio, &$exampleAuthorUserId,	&$state, &$weight, &$counter1, &$counter2, &$counter3, &$counter4)
	{
		Db::initDbConnection();
		$sql = "select title,sub_title,url_thumb_image,tags,url_default_bg_music,url_default_bg_image,pages,unit,
		url_example_audio,example_author_user_id,state,weight,counter1,counter2,counter3,counter4 from ld_article where id ='{$articleId}'";
		
		Log::DEBUG("SQL to be executed: ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$title = $row["title"];
				$subTitle = $row["sub_title"];
				$urlThumbImage = $row["url_thumb_image"];
				$tags = $row["tags"];
				$urlDefaultBgMusic = $row["url_default_bg_music"];
				$urlDefaultBgImage = $row["url_default_bg_image"];
				$pages = $row["pages"];
				$unit = $row["unit"];
				$urlExampleAudio = $row["url_example_audio"];
				$exampleAuthorUserId = $row["example_author_user_id"];
					
				$state = $row["state"];		
				$weight = $row["weight"];	
				
				$counter1 = $row["counter1"];	
				$counter2 = $row["counter2"];	
				$counter3 = $row["counter3"];	
				$counter4 = $row["counter4"];										
				unset( $sql, $stmt, $row);
				return true;
			}

	
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}	
		unset($sql);			
		return false;
		
	}	
	
	public static function getCounters($ids,&$thisArray)
	{
		Db::initDbConnection();
		
		$sql = "SELECT id, value FROM kd_counter WHERE id IN (".$ids.") ORDER BY id ASC";

		Log::INFO("getCounters - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						//$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset( $sql);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset( $sql);	
		return true;	
	}	
	
	
	public static function updateCounter($id, $category, $type,$addValue)
	{
	
		Db::initDbConnection();
		
		$sql = "update kd_counter set value = value + ({$addValue}) where id = '{$id}'";
		
		if($category != NULL)
			$sql .= " and category = '{$category}'";
		if($type != NULL)
		 	$sql .= " and type = '{$type}' ";
		
		Log::DEBUG("updateCounter - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{	
				unset( $sql, $stmt, $affected);
				return Db::createCounter($id, $category, $type,$addValue);
			}
			else
			{
				unset( $sql, $stmt, $affected);
				Log::INFO("update kd_counter for id = '{$id}' and 
		category = '{$category}' and type = '{$type}'");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset( $sql);				
		return false;			
	}	

	//更新对象（如用户作品的）counter
	public static function updateObjectCounter($id,$category,$type,$addValue,&$counterValue){
		Db::initDbConnection();
		$tableName = NULL;
		$counterName = NULL;
		
		if($category != NULL){
			if($category == "work"){
				$tableName = "ld_work";
			}else 
			if($category == "article"){
				$tableName = "ld_article";
			}else
			if($category == "album"){
				$tableName = "ld_album";
			}
		}
		if($type != NULL){
			if($type == "click"){  //点击
				$counterName = "counter1";
			}else 
			if($type == "like"){    //点赞
				$counterName = "counter2";
			}else 
			if($type == "forward"){   //转发
				$counterName = "counter3";
			}else 
			if($type == "collect"){   //收藏
				$counterName = "counter4";
			}
		}
		
		
		if("ld_work" == $tableName)
			$sql ="update {$tableName} set {$counterName} = {$counterName} + {$addValue}, update_time = now() where id = '{$id}'";
		else
			$sql ="update {$tableName} set {$counterName} = {$counterName} + {$addValue} where id = '{$id}'";

		Log::DEBUG("updateObjectCounter - SQL to be executed: ".$sql);

		try{
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();

			if($affected === 0){
				unset( $sql, $stmt, $affected, $tableName, $counterName);
				return false;
			}else{

				Log::INFO("update {$tableName} for id = '{$id}' and {$counterName} with addValue = '{$addValue}'");

				$sql = "select {$counterName} from {$tableName} where id = '{$id}'";
				Log::DEBUG("SQL to be executed: ".$sql);
				$stmt = Db::$pdo->query($sql);
		
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				if(NULL != $row)	
					$counterValue = $row[$counterName];
				
				unset( $sql, $stmt, $affected, $tableName, $counterName);
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}	
		unset( $sql, $tableName, $counterName);			
		return false;	
	}
	
	//获得对象（如用户作品的）counter
	public static function getObjectCounter($id,$category,$type){
		Db::initDbConnection();
		$tableName = NULL;
		$counterName = NULL;
		
		if($category != NULL){
			if($category == "work"){
				$tableName = "ld_work";
			}else 
			if($category == "article"){
				$tableName = "ld_article";
			}else
			if($category == "album"){
				$tableName = "ld_album";
			}
		}
		if($type != NULL){
			if($type == "click"){  //点击
				$counterName = "counter1";
			}else 
			if($type == "like"){    //点赞
				$counterName = "counter2";
			}else 
			if($type == "forward"){   //转发
				$counterName = "counter3";
			}else 
			if($type == "collect"){   //收藏
				$counterName = "counter4";
			}
		}

		try{

				$sql = "select {$counterName} from {$tableName} where id = '{$id}'";
				Log::DEBUG("getObjectCounter - SQL to be executed: ".$sql);
				$stmt = Db::$pdo->query($sql);
		
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
					unset($stmt, $tableName, $sql);
				if(NULL != $row)	
					return $row[$counterName];
	
			
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}	
		unset($counterName, $tableName, $sql);		
		return -1;	
	}	
	
	public static function getUserLike($userId){
		Db::initDbConnection();
		
		$sql = "select like_work_ids from kd_like where user_id = {$userId}";
		Log::DEBUG("SQL to be executed: ".$sql);
		$stmt = Db::$pdo->query($sql);
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		unset($stmt, $sql);
		if(NULL != $row)	
			return $row["like_work_ids"];
	
		return "";

	}		
	//增加用户的点赞记录
	public static function addUserLike($userId,$workId){
		Db::initDbConnection();
	
		$sql ="update kd_like set like_work_ids = concat(like_work_ids,'{$workId}|') where user_id = {$userId}";

		Log::DEBUG("addUserLike - SQL to be executed: ".$sql);

		try{
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();

			if($affected === 0){
				unset($stmt, $sql, $affected);
				return false;
			}else{
				unset($stmt, $sql, $affected);
				Log::INFO("addUserLike for user_id = '{$userId}' with workId = '{$workId}'");
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;	
	}	
	//删除用户的点赞记录
	public static function removeUserLike($userId,$workId){
		Db::initDbConnection();

		$sql ="update kd_like set like_work_ids = replace(like_work_ids,'{$workId}|','') where user_id = {$userId}";

		Log::DEBUG("removeUserLike - SQL to be executed: ".$sql);

		try{
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			
			Log::INFO("removeUserLike for user_id = '{$userId}' with workId = '{$workId}'");
			return true;
		
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}				
		return false;	
	}		
	
	
		
	//新增微信点赞用户
	public static function addWechatUserForUserLike($openId){
		Db::initDbConnection();
	
		$sql = "insert into kd_like_wechat(`open_id`,`like_work_ids`) values('{$openId}','|')";
		Log::DEBUG("addWechatUserForUserLike - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $affected);
				return false;
			}
			else
			{	
				unset($sql, $stmt, $affected);
				Log::INFO("insert kd_like_wechat with openId ".$openId." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}	
	
	public static function getUserLikeWechat($openId)
	{
		Db::initDbConnection();
		$sql = "select like_work_ids from kd_like_wechat where open_id = '{$openId}'";
		
		Log::DEBUG("getUserLikeWechat - SQL to be executed: ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$likeWorkIds = $row["like_work_ids"];
				unset($sql, $stmt, $row);
				return $likeWorkIds;
			}

	
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return NULL;
	}
	
	public static function isUserLikedWechat($openId,$workId)
	{
		$userLikeIds = Db::getUserLikeWechat($openId);
		
		if($userLikeIds != NULL && strpos($userLikeIds,'|'.$workId.'|') !== false) 
			return true;
		else
			return false;
		
	}	
	
	//增加微信用户的点赞记录
	public static function addUserLikeWechat($openId,$workId){
		
		
		if(Db::getUserLikeWechat($openId) == NULL)
			Db::addWechatUserForUserLike($openId);
		
		Db::initDbConnection();
	
		$sql ="update kd_like_wechat set like_work_ids = concat(like_work_ids,'{$workId}|') where open_id = '{$openId}'";

		Log::DEBUG("addUserLikeWechat - SQL to be executed: ".$sql);

		try{
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();

			if($affected === 0){
				unset($sql, $stmt, $affected);
				return false;
			}else{
				unset($sql, $stmt, $affected);
				Log::INFO("addUserLikeWechat for open_id = '{$openId}' with workId = '{$workId}'");
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}	
		unset($sql);			
		return false;	
	}	
	//删除微信用户的点赞记录
	public static function removeUserLikeWechat($openId,$workId){
		Db::initDbConnection();

		$sql ="update kd_like_wechat set like_work_ids = replace(like_work_ids,'{$workId}|','') where open_id = '{$openId}'";

		Log::DEBUG("removeUserLikeWechat - SQL to be executed: ".$sql);

		try{
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			unset($sql, $stmt);
			Log::INFO("removeUserLikeWechat for open_id = '{$openId}' with workId = '{$workId}'");
			return true;
		
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
		}
		unset($sql);				
		return false;	
	}		
			
	public static function createCounter($id, $category, $type,$addValue)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_counter(`id`,`category`,`type`,`value`) values('{$id}','{$category}','{$type}',{$addValue})";
		Log::DEBUG("createCounter - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{	
				unset($sql, $stmt, $affected);
				return false;
			}
			else
			{
				//Log::INFO("insert kd_counter with smsCode ".$smsCode." successfully for mobile="."{$mobile}");	
				unset($sql, $stmt, $affected);	
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}		
	
	
	public static function getUserWorkBoard($period, $type, $province, $city, $county, $limit, &$thisArray)
	{
		
		$counterName = $period == 'month'? 'counter2':'counter1';
		
		if($type == "popularity")
			$counterName = $period == 'month'? 'counter4':'counter3';
		
		
		$whereStmt = " ";
		if($province != NULL)
		{
			$whereStmt = ' where province = "'.$province.'"';
			if($city != NULL)
			{
				$whereStmt = $whereStmt.' AND city = "'.$city.'"';
				if($county != NULL)
				{
					$whereStmt = $whereStmt.' AND county = "'.$county.'"';
				}
			}
		}
		$limitStmt = " LIMIT 100 ";
		if($limit != NULL)
			$limitStmt = " LIMIT ".$limit." ";
		
		Db::initDbConnection();
		
		
		$sql = "select user_id,nickname, gender, province, city, county,  url_avatar, signature, ".$counterName." as counter from kd_user ".$whereStmt." order by ".$counterName." DESC ".$limitStmt;
		
		Log::DEBUG("SQL to be executed: ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						$thisArray[$key][$k]=urlencode($v);
					}
				} 
				unset($sql, $stmt, $data, $whereStmt, $limitStmt, $counterName);
				return true; 		 		
	
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql, $whereStmt, $limitStmt, $counterName);				
		return false;
		
	}	
	
	public static function addUserCounter($userId, $type, $value)
	{
		Db::initDbConnection();
		
		if($type == "popularity") //人气值
			$sql = "UPDATE kd_user SET counter3 = counter3+({$value}), counter4 = counter4+({$value}) WHERE user_id = {$userId}";
        else //作品数量
			$sql = "UPDATE kd_user SET counter1 = counter3+({$value}), counter2 = counter4+({$value}) WHERE user_id = {$userId}";			
		
		Log::DEBUG("addUserCounter - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			unset($sql, $stmt);
			Log::INFO("update kd_user counter for user_id = {$userId} with value {$value}");		
				return 1;
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return -1;			
	}		
	
	public static function getBalance($userId)
	{
		Db::initDbConnection();
		$sql = "select balance from kd_user where user_id ='{$userId}'";
		
		Log::DEBUG("SQL to be executed: ".$sql);
		
		try{
			$stmt = Db::$pdo->query($sql);
		
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if(NULL != $row)
			{
				$balance = $row["balance"];
				unset($sql, $stmt, $row);
				return $balance;
			}
			unset($stmt, $row);
	
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());	
		}
		unset($sql);				
		return -1;
		
	}		


	public static function updateBalance($userId,$addBalance)
	{
		Db::initDbConnection();
		
		$sql = "update kd_user set balance = balance + ({$addBalance}) where user_id = {$userId}";
		
		Log::DEBUG("updateBalance - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
			
			if($affected === 0)
			{	
				unset($sql, $stmt, $affected);	
				Log::INFO("update kd_user balance failed for user_id = {$userId} with addBalance {$addBalance}, possibly the user does not exist");		
				return 0;
			}
			else
			{	
				unset($sql, $stmt, $affected);
				Log::INFO("update kd_user balance for user_id = {$userId} with addBalance {$addBalance}");		
				return 1;
			}
			
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return -1;			
	}	


	public static function addTransaction($userId, $amount, $currency, $type, $descrptn, $balance)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_transaction(`user_id`,`amount`,`unit`,`type`,`descrptn`,`balance`) values({$userId},{$amount},'{$currency}','{$type}','{$descrptn}',{$balance})";
		Log::DEBUG("addTransaction - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $affected);
				return false;
			}
			else
			{
				unset($sql, $stmt, $affected);
				Log::INFO("insert kd_transaction successfully for userId=".$userId);		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}
	
	public static function getAuthorities($userId)
	{
		Db::initDbConnection();
		
		$sql = "SELECT kd_product.scope AS scope  FROM kd_order, kd_product WHERE user_id = '".$userId."' AND kd_order.state = '购买成功' AND kd_order.end_time > now() AND kd_order.product_id = kd_product.id ORDER BY order_time DESC";

		Log::INFO("getAuthorities - SQL to be executed : ".$sql);
		
		$authorities = "|";
		
		try{
						
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
							
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
						
						if($authorities == "|")
							$authorities = $v;
						else
							$authorities = $authorities."|".$v;
												
					}
				}  	
				unset($stmt, $data);	
			}catch(PDOException $e){
				unset($sql);
				Log::INFO("MySQL Error: ".$e->getMessage());
				return NULL;
				
		}	
		unset($sql);
		return $authorities;	
	}		
	
	public static function getAdvertisements($where,&$thisArray)
	{
		Db::initDbConnection();
		
		if($where != NULL)
			$sql = "SELECT * FROM kd_advertisement WHERE ".$where." ORDER BY weight ASC";
		else
			$sql = "SELECT * FROM kd_advertisement ORDER BY weight ASC";

		Log::INFO("getAdvertisements - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql);	
		return true;	
	}	
	
	public static function getTrasactions($userId, $pageSize, $page, &$trasactionsArray, &$pageCount)
	{
		Db::initDbConnection();
		
		$sql = "SELECT * FROM kd_transaction WHERE user_id = '".$userId."'";
		$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_transaction WHERE user_id = '".$userId."'";

		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY id DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getTrasactions - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法
						
						$trasactionsArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}	
		unset($sql, $sqlPageCount);
		return true;	
	}		
	
	public static function updateMomentState($momentIds,$state,$userId)
	{
		
		$momentIdArr = explode('|',$momentIds);
		
		$where = " WHERE author_user_id = {$userId} ";
		$size = count($momentIdArr);
		
		if($size == 1)
			 $where = $where." AND id = ".$momentIdArr[0];
		else
		{
		
			for($index=0;$index<$size;$index++) 
			{ 
						
				if( 0 == $index)
					$where = $where." AND ((id = ".$momentIdArr[$index].") ";
				else
					if(($size-1) == $index)
						$where = $where." OR (id = ".$momentIdArr[$index].")) ";
					else
						$where = $where." OR (id = ".$momentIdArr[$index].") ";
			} 
		}
	
		Db::initDbConnection();

		$sql = "update kd_moment set state = {$state} ".$where;
			
		Log::DEBUG("updateMomentState - SQL to be executed: ".$sql);
		try{		
			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $affected, $momentIdArr, $where, $size);
				return 0;
			}
			else
			{
				Log::INFO("update kd_moment state for id ".$momentIds." successfully for author_user_id=".$userId);	
				unset($sql, $stmt, $affected, $momentIdArr, $size, $where);	
				return 1;
			}
		}catch(PDOException $e){
			unset($sql, $where, $momentIdArr, $size);
			Log::ERROR("MySQL Error: ".$e->getMessage());
			return -1;
			
		}	
		unset($sql, $where, $momentIdArr, $size);			
		return -1;	
				
	}
	
	public static function getMoments($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT kd_moment.*, kd_user.nickname, kd_user.url_avatar AS user_avatar FROM kd_moment,kd_user WHERE kd_user.user_id=kd_moment.author_user_id AND state = 1 ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_moment WHERE state = 1 ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY create_time DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getMoments - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						
						$thisArray[$key][$k]=urlencode($v);
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql, $sqlPageCount);	
		return true;	
	}				
	
	public static function getDetailMoments($where, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
				
		$sql = "SELECT kd_moment.*, kd_user.nickname AS nickname, kd_user.url_avatar AS user_avatar, ld_work.url_work AS url_work, ld_work.publish_time AS publish_time_work, ld_work.counter1 AS counter1_work, ld_work.counter2 AS counter2_work, ld_work.counter3 AS counter3_work, ld_work.counter4 AS counter4_work, ld_article.title AS title_work, ld_article.url_thumb_image AS url_thumb_image_work, ld_article.tags AS tags_work  FROM kd_moment,kd_user,ld_article,ld_work WHERE kd_user.user_id = kd_moment.author_user_id AND kd_moment.work_id = ld_work.id AND ld_work.article_id = ld_article.id AND kd_moment.state = 1 ";
		
		$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_moment WHERE state = 1 ";
		
		if($where != NULL)
		{
			$sql = $sql." AND ".$where;
			$sqlPageCount = $sqlPageCount." AND ".$where;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		if(($where == NULL) || ($where == ""))
			$sql = $sql." ORDER BY ld_work.update_time DESC"; 
		else
			$sql = $sql." ORDER BY ld_work.create_time DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getDetailMoments - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						
						$thisArray[$key][$k]=urlencode($v);
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return false;
		}
		unset($sql, $sqlPageCount);	
		return true;	
	}	

	public static function addReport(&$reportId,$targetId,$targetType,$userId,$text,$images)
	{

		Db::initDbConnection();
		
		$sql = "insert into kd_report(`target_id`,`target_type`,`author_user_id`,`text`,`images`) values('{$targetId}','{$targetType}',{$userId},'{$text}','{$images}')";
		Log::DEBUG("addReport - SQL to be executed: ".$sql);
		try{

			$stmt=Db::$pdo->prepare($sql);
			$stmt->execute();	
			$affected = $stmt->rowCount();	
									
			if($affected === 0)
			{
				unset($sql, $stmt, $affected);
				return false;
			}
			else
			{
				unset($sql, $stmt, $affected);
				$reportId=Db::$pdo->lastInsertId();
				Log::INFO("addReport - insert kd_report with reportId ".$reportId." successfully");		
				return true;
			}
		}catch(PDOException $e){
			Log::ERROR("MySQL Error: ".$e->getMessage());
			
		}
		unset($sql);				
		return false;		
	}	
		

	public static function getMessages($userId, $targetType, $isFromMe, $pageSize, $page, &$thisArray, &$pageCount)
	{
		Db::initDbConnection();
	
		if($targetType == "work")
		{
			if($isFromMe)
			{
				Log::INFO("getMessages2");
				$sql = "SELECT kd_comment.*, ld_work.id AS work_id, ld_article.title as article_title, ld_article.url_thumb_image FROM kd_comment,ld_article,ld_work WHERE kd_comment.author_user_id = {$userId} AND kd_comment.target_type='work' AND kd_comment.target_id = ld_work.id AND ld_work.article_id = ld_article.id ";
				$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_comment,ld_article,ld_work WHERE kd_comment.author_user_id = {$userId} AND kd_comment.target_type='work' AND kd_comment.target_id = ld_work.id AND ld_work.article_id = ld_article.id ";
			}
			else
			{
				Log::INFO("getMessages2");
				$sql = "SELECT kd_comment.*, ld_work.id AS work_id, ld_article.title as article_title, ld_article.url_thumb_image FROM kd_comment,ld_article,ld_work WHERE ld_work.author_user_id = {$userId} AND kd_comment.target_type='work' AND kd_comment.target_id = ld_work.id AND ld_work.article_id = ld_article.id ";		
				$sqlPageCount = "SELECT count(*) AS thisCount FROM kd_comment,ld_article,ld_work WHERE ld_work.author_user_id = {$userId} AND kd_comment.target_type='work' AND kd_comment.target_id = ld_work.id AND ld_work.article_id = ld_article.id ";		
			}
		}
		else
		{	
			return -1;
		}
		
		if($pageSize != NULL && $page != NULL)
			 Db::getPageCount($sqlPageCount, $pageSize, $pageCount);
		
		$sql = $sql." ORDER BY create_time DESC"; 
			
		if($pageSize != NULL && $page != NULL)
			$sql = $sql." limit ".($page - 1)*$pageSize." , ".$pageSize;
		
		Log::INFO("getMessages - SQL to be executed : ".$sql);
		
		try{
				$stmt = Db::$pdo->query($sql);
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $key=>$value){  
					foreach ($value as $k=>$v){
	
						$k = Common::lineToHump($k);//将下划线转为小驼峰法<br />
						$thisArray[$key][$k]=urlencode($v);
						
					}
				}  		
				unset($stmt, $data);
			}catch(PDOException $e){
				unset($sql, $sqlPageCount);
				Log::ERROR("MySQL Error: ".$e->getMessage());
				return 0;
		}
		unset($sql, $sqlPageCount);	
		return 1;	
	}	


	
}
?>