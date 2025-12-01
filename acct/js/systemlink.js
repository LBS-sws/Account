function remoteLoginTwApp(id,url,home) {
	var homeurl = home+'/ajax/remotelogin';
	var appurl = url+'/remote/lbsremote.php';
	var wid = "view"+id;
    $.ajax({
		type: 'GET', 
		url: homeurl,
		dataType: 'json', 
		success: function(json) {
			var x = json;
			var data = json;
			if (data!='') {
				var id = data.id;
				var skey = data.sk;
				var key = data.ky;
				var lang = data.lang;
				
				var form = document.createElement("form");
				form.setAttribute("method", "post");
				form.setAttribute("action", appurl);
				
				form.setAttribute("target", wid);

				var hiddenField1 = document.createElement("input"); 
				hiddenField1.setAttribute("type", "hidden");
				hiddenField1.setAttribute("name", "lbstwid");
				hiddenField1.setAttribute("value", id);
				form.appendChild(hiddenField1);
	
				var hiddenField2 = document.createElement("input"); 
				hiddenField2.setAttribute("type", "hidden");
				hiddenField2.setAttribute("name", "lbstwkey");
				hiddenField2.setAttribute("value", key);
				form.appendChild(hiddenField2);

				var hiddenField3 = document.createElement("input"); 
				hiddenField3.setAttribute("type", "hidden");
				hiddenField3.setAttribute("name", "lbstwlang");
				hiddenField3.setAttribute("value", lang);
				form.appendChild(hiddenField3);

				var hiddenField4 = document.createElement("input"); 
				hiddenField4.setAttribute("type", "hidden");
				hiddenField4.setAttribute("name", "lbstwskey");
				hiddenField4.setAttribute("value", skey);
				form.appendChild(hiddenField4);

				document.body.appendChild(form);

				window.open('', wid);

				form.submit();	
			}
		},
		error: function(xhr, status, error) {
			skip = 1;
		}
	});
}
	
function remoteLoginOnlib(id,url,home) {
	var homeurl = home+'/ajax/remoteloginonlib';
	var appurl = url+'/restapi/index.php/rlogin';
	var wid = "view"+id;
    $.ajax({
		type: 'GET', 
		url: homeurl,
		dataType: 'json', 
		success: function(json) {
			var rtn = json;
			if (json!='') {
//				$.post(appurl, {user: json.id, pass: json.pwd}, function(data) {
//					if (data.success) window.open(url, wid);
//				}, 'json');
				var id = json.id;
				var pass = json.pwd;
				
				var form = document.createElement("form");
				form.setAttribute("method", "post");
				form.setAttribute("action", appurl);
				
				form.setAttribute("target", wid);

				var hiddenField1 = document.createElement("input"); 
				hiddenField1.setAttribute("type", "hidden");
				hiddenField1.setAttribute("name", "user");
				hiddenField1.setAttribute("value", id);
				form.appendChild(hiddenField1);
	
				var hiddenField2 = document.createElement("input"); 
				hiddenField2.setAttribute("type", "hidden");
				hiddenField2.setAttribute("name", "pass");
				hiddenField2.setAttribute("value", pass);
				form.appendChild(hiddenField2);

				var hiddenField3 = document.createElement("input"); 
				hiddenField3.setAttribute("type", "hidden");
				hiddenField3.setAttribute("name", "url");
				hiddenField3.setAttribute("value", url);
				form.appendChild(hiddenField3);

				document.body.appendChild(form);

				window.open('', wid);

				form.submit();	
			}
		},
		error: function(xhr, status, error) {
			skip = 1;
			window.open(url, wid);
		}
	});
}

// 前往派单系统
function goNewUnited(id, url, home, string, user_id,code){
	if(id!=='nu'){ return false; }
	if(code === false) { alert('该账号未绑定员工号，无法跳转！请联系管理员进行绑定') }
	var token_time = 43200//设置cookie有效时间 5小时

	var cookie = {
		'Token': 'yaAuthAdminToken',
		'Username': 'yaAuthUsername',
		'Nickname': 'yaAuthNickname',
		'Avatar': 'yaAuthAvatar',
		'TokenName': 'yaSettingsTokenName',
		'TokenType': 'yaSettingsTokenType',
		'SystemName': 'yaSettingsSystemName',
		'PageTitle': 'yaSettingsPageTitle',
		'LogoUrl': 'yaSettingsLogoUrl',
		'FaviconUrl': 'yaSettingsFaviconUrl',
		'Id': 'yaLbsId',
		'ttl':'yaAuthAdminTokenTTl',
	};
	var timestamp = Math.floor(Date.now() / 1000);

	/* 先请求派单系统，获取token */
	var homeurl = home+'/api/system.login/login';

	//token 没过期 && token属于当前LBS用户 && token至少还能使用一个小时
	if(getCookie(cookie.Token) && user_id==getCookie(cookie.Id) && timestamp<=getCookie(cookie.ttl)-3600){
		window.open(url, '_self');
	}else{
		$.ajax({
			type: 'post',
			url: homeurl,
			dataType: 'json',
			data: {
				'iopfd':string
			},
			success: function(json) {
				// console.log(json)
				if (json!='' && json.code==200) {
					// 设置cookie
					setCookie(cookie['Token'], json.data.AdminToken, json.data.token_time || token_time)
					setCookie(cookie['Username'], json.data.name)
					setCookie(cookie['Nickname'], json.data.nickname)
					setCookie(cookie['Avatar'], null)
					setCookie(cookie['SystemName'], json.data.system_name)
					setCookie(cookie['PageTitle'], json.data.page_title)
					setCookie(cookie['LogoUrl'], json.data.logo_url)
					setCookie(cookie['FaviconUrl'], json.data.favicon_url)
					setCookie(cookie['TokenType'], json.data.token_type)
					setCookie(cookie['TokenName'], json.data.token_name)
					setCookie(cookie['Id'], user_id)
					setCookie(cookie['ttl'], timestamp + parseInt(token_time))

					window.open(url, '_self');
				}else if(json.code===300){//未绑定办公室
					alert('账号在派单系统未设置办公室权限,无法跳转，请联系你的上级处理')
				}else if(json.code===400){//账号禁用
					alert('账号在派单系统被禁用,禁止跳转')
				}else{
					alert('切换失败，找不到账号')
				}
			}, error: function(xhr, status, error) {
				console.log(error);
			}
		});
	}
}

//读取cookie
function getCookie(name) {
	let cookies = document.cookie.split(';');
	for (let i = 0; i < cookies.length; i++) {
		let cookie = cookies[i].trim();
		if (cookie.startsWith(name + '=')) {
			return cookie.substring(name.length + 1);
		}
	}
	// Return null if the cookie is not found
	return false;
}

//设置cookie
function setCookie(name, value, daysToExpire) {
	let expirationDate = new Date();
	expirationDate.setTime(expirationDate.getTime() + (daysToExpire * 1000)); // Convert seconds to milliseconds

	let cookieValue = `${name}=${value}; expires=${expirationDate.toUTCString()}; path=/`;

	// Set the cookie in the document
	document.cookie = cookieValue;
}
