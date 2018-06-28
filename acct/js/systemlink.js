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
