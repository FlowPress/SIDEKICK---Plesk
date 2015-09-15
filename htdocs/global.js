// Copyright 1999-2014. Parallels IP Holdings GmbH.
Jsw.namespace('PleskExt.Sidekick');

PleskExt.Sidekick.init = function() {

	var url = window.location.toString();

	if (url.indexOf('login_up.php') === -1) {
		$$("head").first().insert(
			new Element("script", {type:"text/javascript", src:"//loader.sidekick.pro/platforms/1a17ab63-9f83-4e8c-9375-f0b6e4a0998a.js"})
			);
	};

	
}

Jsw.onReady(PleskExt.Sidekick.init());
