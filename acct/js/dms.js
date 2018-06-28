function DMSLogout(url) {
	var url = JSON.parse(url);
    for (i=1; i < url.length; i++) {
		var x = url[i];
		var wid = 'view'+x.id;
		window.open(x.url, wid).close();
	}
	window.location = url[0];
}
