/***************************************************************************
* GNU/GPL Kumbia - PHP Rapid Development Framework
* Simple Object Helper
****************************************************************************
* (c) 2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
****************************************************************************/

var $Kumbia = {
	/**
	 * Contiene las constantes de ejecucion del Framework
	 **/
	Constant : {
		KUMBIA_PATH : '',
		application: '',
		module: '',
		controller_name : '',
		action_name : ''
	},
	
	/**
	 * Para manipular cadenas
	 **/
	String : {
		/**
		 * Rellena la cadena
		 * @param s cadena a rellenar
		 * @param length hasta donde se rellena
		 * @param pad_string cadena de relleno
		 * @param pad_type por donde se rellena (left, right, both)
		 * @return la cadena rellena
		 **/
		pad : function(s, length, pad_string, pad_type) {
			if(s.length<length) {
				var value;
				if(pad_type=='left') {
					value = pad_string+s;
				} else if(pad_type=='right') {
					value = s+pad_string;
				} else {
					value = pad_string+s+pad_string;
				}
				return this.pad(value, length, pad_string, pad_type);
			} else {
				return s;
			}
		}
	},

	/**
	 * Validadores
	 **/
	Validators : {
		/**
		 * Verifica si es solo digitos
		 * @param s la cadena a verificar
		 * @return boolean
		 **/
		is_digit : function(s) {			
			return s.match(/^\d+$/);
		},
		
		/**
		 * Verifica si es un numero entero
		 * @param s la cadena a verificar
		 * @return boolean
		 **/
		is_integer : function(s) {			
			return s.match(/^-?\d+$/);
		},
		
		/**
		 * Verifica si es numerico
		 * @param s la cadena a verificar
		 * @return boolean
		 **/
		is_numeric : function(s) {			
			return s.match(/^-?\d+$/) || s.match(/^-?\d+\.\d+$/);
		},
		
		/**
		 * Valida si es una fecha
		 * @param s la cadena a verificar
		 * @return boolean
		 **/
		is_date : function(s) {
			var my_date_s = s.split("-");

			if(!s.match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/)) return false;

			var year = parseInt(my_date_s[0]);
			var month = parseInt(my_date_s[1])-1;
			var day = parseInt(my_date_s[2]);

			if(!(isNaN(year) || isNaN(month) || isNaN(day))){
				var js_date = new Date(year, month, day);
						
				if((year==js_date.getFullYear()) && (month==js_date.getMonth()) && (day==js_date.getDate())){
					return true;
				}
			} 
			
			return false;
		},
		
		/**
		 * Verifica si esta vacia
		 * @param s la cadena a verificar
		 * @return boolean
		 **/
		is_empty : function(s) {			
			return s=="";
		}
	},

	/**
	 * Obtener parametros con nombre
	 * @param args array de argumentos
	 **/
	get_params : function(args) {
		var params = {};
		var n = 0;
		for(var i=0; i<args.length; i++){
			if(typeof(args[i])=='string') {
				var r = args[i].match(/^([A-Za-z_]+):\s(.+)$/);
			} else {
				r = false;
			}
			
			if(r) {
				params[r[1]] = r[2];
			} else {
				params[n] = args[i];
				n++;
			}
		}
		
		return params;
	},
		
	/**
	 * Metodo que se encarga de serializar para enviar a traves de url
	 * @param params objeto o array asociativo
	 * tambien puede serializar parametros con nombre
	 **/
	serialize : function(params) {
		if(arguments.length>1) params = this.get_params(arguments);
		var p = new Array();
		for(key in params) {
			p.push(key+'='+params[key]);
		}
		return p.join('&');
	},
	
	/**
	 * Devuelve una url de Kumbia
	 * @param route ruta de direccionamiento "module/controller/action"
	 *
	 * Soporta parametros con nombre como parametros para serializarse por metodo Get
	 * Ej: $Kumbia.get_kumbia_url('controller/action', 'id: 1')
	 *
	 * Soporta parametros para pasar por url bonita
	 * Ej: $Kumbia.get_kumbia_url('controller/action', '1', '2')
	 *
	 * Soporta objeto cuyos atributos se pasaran por metodo Get
	 * Ej: $Kumbia.get_kumbia_url('controller/action', {id: '1'})
	 *
	 * Soporta array cuyos parametros se pasaran utilizando url bonita
	 * Ej: $Kumbia.get_kumbia_url('controller/action', ['1', '2'])
	 **/
	get_kumbia_url : function(route) {
		var params = this.get_params(arguments);
		delete params[0];

		/**
		 * Procedo a construir la ruta base
		 **/
		var new_route = [route];
		if(params.module) {
			new_route = [params.module].concat(new_route);
			delete params.module;
		}
		if(params.application!=undefined) {
			new_route = [params.application].concat(new_route);
			delete params.application;
		}
		
		/**
		 * Si no es la aplicacion por defecto, entonces la cargo en la ruta
		 **/
		if(this.Constant.application!='') {
			new_route = [this.Constant.application].concat(new_route);
		}
		
		/**
		 * Serializo los parametros para pasarlos por la url
		 **/
		var params_get = new Array();
		for(key in params) {
			if(params[key] instanceof Array) {
				for(var i=0; i<params[key].length; i++) {
					new_route.push(params[key][i]);
				}
			} else if(params[key] instanceof String) {
				if(this.Validators.is_digit(key)) {
					new_route.push(params[key]);
				} else {
					params_get.push(key+'='+params[key]);
				}
			} else if(params[key] instanceof Object) {
				params_get.push(this.serialize(params[key]));
			} else {
				if(this.Validators.is_digit(key)) {
					new_route.push(params[key]);
				} else {
					params_get.push(key+'='+params[key]);
				}
			}			
		}
		
		/**
		 * Construyo la ruta con parametros
		 **/
		new_route = new_route.join('/');
		if(params_get.length) {
			new_route += '?';
			new_route += params_get.join('&');
		}

		return this.Constant.KUMBIA_PATH + new_route;
	},
	
	/**
	 * Redirecciona a una url
	 * @param route ruta de direccionamiento "module/controller/action"
	 *
	 * Soporta parametros con nombre como parametros para serializarse por metodo Get
	 * Ej: $Kumbia.redirect('controller/action', 'id: 1')
	 *
	 * Soporta parametros para pasar por url bonita
	 * Ej: $Kumbia.redirect('controller/action', '1', '2')
	 *
	 * Soporta objeto cuyos atributos se pasaran por metodo Get
	 * Ej: $Kumbia.redirect('controller/action', {id: '1'})
	 *
	 * Soporta array cuyos parametros se pasaran utilizando url bonita
	 * Ej: $Kumbia.redirect('controller/action', ['1', '2'])
	 **/
	redirect : function(route) {
		window.location = this.get_kumbia_url.apply(this,arguments);
	},
	
	/**
	 * Crea una ventana popup
	 * @param route ruta de direccionamiento "module/controller/action"
	 * @param name nombre de la ventana (soportados los de window.open)
	 * Los parametros con nombre corresponden a las especificaciones a utilizar en la creacion (width, height, dependent, ...)
	 **/
	popup : function(route, name) {
		var params = this.get_params(arguments);
		delete params[0];
		delete params[1];
		
		var spec = new Array();
		for(key in params) {
			spec.push(key+"="+params[key]);
		}
		
		window.open(this.get_kumbia_url(route), name, spec.join(","));
	}
}
