<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<button id="googleLogin" type="button">Google登录</button>  
	<script src="https://apis.google.com/js/api:client.js"></script> 
	<script>
		gapi.load('auth2', function(){  
		  auth2 = gapi.auth2.init({  
		    client_id: 'replace_with_google_client_id',
		    cookiepolicy: 'single_host_origin',  
		    scope: 'profile'
		  });  
		  auth2.attachClickHandler(document.getElementById('googleLogin'),{},function(googleUser){
		    var profile = auth2.currentUser.get().getBasicProfile();
		    var userinfo={}
		    userinfo.nickname=profile.getName();
		    userinfo.email=profile.getEmail();
		    userinfo.avatar=profile.getImageUrl();
		    userinfo.id=profile.getId();
		    userinfo.id_token=googleUser.Zi.id_token; //jwt 可使用接口解析得到用户基本信息

		    console.log(googleUser);
		    console.log(profile);
		    console.log(userinfo);
		  })
		});
	</script>
</body>
</html>

<?php
//使用id_token 换取用户信息
$google_info=http('https://www.googleapis.com/oauth2/v3/tokeninfo',['id_token'=>$_GET['id_token']]);
$google_info=json_decode($google_info,true);
if($google_info['aud']=='replace_with_google_client_id'){
	$user['nickname']   = $google_info['name'];
	$user['email']      = $google_info['email'];
	$user['avatar']     = $google_info['picture'];
	$user['id']         = $google_info['sub'];
	var_dump($user);
}

//http请求
function http($url, $params = array(), $method = 'GET', $ssl = false){
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $getQuerys = !empty($params) ? '?'. http_build_query($params) : '';
            $opts[CURLOPT_URL] = $url . $getQuerys;
            break;
        case 'POST':
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
    }
    if ($ssl) {
        $opts[CURLOPT_SSLCERTTYPE] = 'PEM';
        $opts[CURLOPT_SSLCERT]     = $ssl['cert'];
        $opts[CURLOPT_SSLKEYTYPE]  = 'PEM';
        $opts[CURLOPT_SSLKEY]      = $ssl['key'];;
    }
    /* 初始化并执行curl请求 */
    $ch     = curl_init();
    curl_setopt_array($ch, $opts);
    $data   = curl_exec($ch);
    $err    = curl_errno($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);
    if ($err > 0) {
        echo $errmsg;
        return false;
    }else {
        return $data;
    }
}