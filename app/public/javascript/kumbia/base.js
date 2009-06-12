/***************************************************************************
* GNU/GPL Kumbia - PHP Rapid Development Framework
* Simple Object Manipulation Base Functions
****************************************************************************
* (c) 2007 Andres Felipe Gutierrez <andresfelipe at vagoogle.net>
****************************************************************************/

var dummy = function(){}

Object.extend(Array.prototype, {
	append: function(item){
		this[this.length] = item;
	}
})

Object.extend(Number.prototype, {

	upto: function(up, iterator){
		$R(this, up).each(iterator);
    	return this;
	},

	downto: function(down, iterator){
		$A($R(down, this)).reverse().each(iterator);
    	return this;
	},

	step: function(limit, step, iterator){
		range = []
		if(step>0){
			for(i=this;i<=limit;i+=step){
				range.append(i)
			}
		} else {
			for(i=this;i>=limit;i+=step){
				range.append(i)
			}
		}
		range.each(iterator);
    	return this;
	},

	next: function(){
		return this+1;
	}

})

//Obtiene una referencia a un ob
function $O(obj){
	if($("flid_"+obj)){
		return $("flid_"+obj);
	}
	return $(obj);
}



// Obtiene una referencia a un objeto del formulario generado
// o un document.getElementById
function $C(obj){
	return $("flid_"+obj);
}

// Obtiene el valor de un objeto de un formulario generado
function $V(obj){
	return $F("flid_"+obj);
}


/****************************************************
* Auth Functions
****************************************************/
//Funcion que envia un formulario via AJAX
function ajaxRemoteForm(form, up, callback){
	new Ajax.Updater(up, form.action, {
		 method: "post",
		 asynchronous: true,
         evalScripts: true,
         onSuccess: function(transport){
			$(up).update(transport.responseText)
		},
		onLoaded: callback.before!=undefined ? callback.before: function(){},
		onComplete: callback.success!=undefined ? callback.success: function(){},
  		parameters: Form.serialize(form)
    });
  	return false;
}

var AJAX = new Object();

AJAX.xmlRequest = function(params){
	this.options = $H()
	if(!params.url && params.action){
		this.url = params.action
	}
	if(params.parameters){
		this.url+= "/&"+params.parameters
	}
	if(params.debug){
		alert(this.url)
	}
	if(this.action) {
		this.action = params.action;
	}
	if(params.asynchronous==undefined) {
		this.options.asynchronous = true
	} else {
		this.options.asynchronous = params.asynchronous
	}
	if(params.callbacks){
		if(params.callbacks.oncomplete){
			this.options.onComplete = params.callbacks.oncomplete
		}
		if(params.callbacks.before){
			this.options.onLoading = params.callbacks.before
		}
		if(params.callbacks.success){
			this.options.onSuccess = params.callbacks.success
		}
	}
	try {
		return new Ajax.Request(this.url, this.options)
	}
	catch(e){
		alert("KumbiaError: "+e.message+"["+e.name+"]");
	}
}


AJAX.viewRequest = function(params){
	this.options = {}
	if(!params.action){
		alert("KumbiaError: Ajax Action is not set!");
		return;
	}

	this.url = params.action;
	if(params.parameters){
		this.url+="&"+params.parameters;
	}
	this.action = params.action;
	if(params.debug){
		alert(this.action)
	}
	if(params.asynchronous==undefined) {
		this.asynchronous = true
	} else {
		this.asynchronous = params.asynchronous
	}

	if(params.callbacks){
		if(params.callbacks.oncomplete){
			this.options.onComplete = params.callbacks.oncomplete
		}
		if(params.callbacks.before){
			this.options.onLoading = params.callbacks.before
		}
		if(params.callbacks.success){
			this.options.onSuccess = params.callbacks.success
		}
	}

	container = params.container;
	this.options.evalScripts = true

	if(!$(container)){
		window.alert("KumbiaError: Container Ajax Object '"+container+"' Not Found")
		return null
	}

	try {
		return new Ajax.Updater(container, this.url, this.options)
	}
	catch(e){
		alert("KumbiaError: "+e.message+" ["+e.name+"]");
	}

}

AJAX.execute = function(params){
	this.options = {}
	if(!params.action){
		alert("KumbiaError: AJAX Action is not set!");
		return;
	}
	this.url = params.action;
	if(params.parameters){
		this.url+="&"+params.parameters;
	}
	this.action = params.action;
	if(params.debug){
		alert(this.action)
	}
	if(params.asynchronous==undefined) {
		this.asynchronous = false
	} else {
		this.asynchronous = params.asynchronous
	}

	if(params.callbacks){
		if(params.callbacks.oncomplete){
			this.options.onComplete = params.callbacks.onend
		}
		if(params.callbacks.before){
			this.options.onLoading = params.callbacks.before
		}
		if(params.callbacks.success){
			this.options.onSuccess = params.callbacks.success
		}
	}
	try {
		return new Ajax.Request(this.url, this.options)
	}
	catch(e){
		alert("KumbiaError: "+e.message+" ["+e.name+"]");
	}
}

AJAX.query = function(qaction){
	var me;
	new Ajax.Request(qaction, {
			asynchronous: false,
			onSuccess: function(resp){
				xml = resp.responseXML
				data = xml.getElementsByTagName("data");
				if(document.all){
					xmlValue = data[0].text
				} else {
					xmlValue = data[0].textContent
				}
				me = xmlValue
			}
		}
	)
	return me
}

function enable_upload_file(file){
	$(file+"_span").show()
	$(file+"_span_pre").hide()
	if(document.all){
		$(file+"_file").click()
	}
	$(file+"_im").hide()
}

function upload_file(file){
	$(file+"_file").id = file+"_tmp"
	$(file).id = file+"_file"
	$(file+"_file").name = file+"_file"
	$(file+"_tmp").id = file
	$(file).name = file
}

function cancel_upload_file(file){
	$(file+"_file").id = file+"_tmp"
	$(file).id = file+"_file"
	$(file+"_file").name = file+"_file"
	$(file+"_tmp").id = file
	$(file).name = file
	$(file).selectedIndex = 0
	$(file+"_span").hide()
	$(file+"_span_pre").show()
}

function show_upload_image(file, url_path){
	if(file.options[file.selectedIndex].value!='@'){
		$(file.id+"_im").show()
		$(file.id+"_im").src = url_path + "img/" + file.options[file.selectedIndex].value
	} else {
		$(file.id+"_im").hide()
	}
}
