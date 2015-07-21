var lang = this.childObjects.langSelector.getValue();
		var vers = this.childObjects.versionSelector.getValue();
		
		window.location = app.createUrl([this.baseUrl,lang,vers]);