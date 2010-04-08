/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Plugin para jQuery que incluye los callbacks basicos para los Helpers
 *
 * @copyright  Copyright (c) 2005-2010 Kumbia Team (http://www.kumbiaphp.com)
 * @license	http://wiki.kumbiaphp.com/Licencia	 New BSD License
 */

(function($) {
	/**
	 * Objeto KumbiaPHP
	 *
	 */
	$.KumbiaPHP = {
		/**
		 * Ruta al directorio public en el servidor
		 * 
		 * @var String
		 */
		publicPath : null,
		
		/**
		 * Plugins cargados
		 * 
		 * @var Array
		 */
		plugin: [],
        
		/**
		 * Muestra mensaje de confirmacion
		 *
		 * @param Object event
		 */
		cConfirm: function(event) {
			if(!confirm(this.title)) {
				event.preventDefault();
			}
		},
        
		/**
		 * Aplica un efecto a un elemento
		 *
		 * @param String fx
		 */
		cFx: function(fx) {
			return function(event) {
				event.preventDefault();
				(($(this.rel))[fx])();
			}
		},
        
		/**
		 * Carga con AJAX
		 *
		 * @param Object event
		 */
		cRemote: function(event) {
			event.preventDefault();
			$(this.rel).load(this.href);
		},
        
		/**
		 * Carga con AJAX y Confirmacion
		 *
		 * @param Object event
		 */
		cRemoteConfirm: function(event) {
			event.preventDefault();
			if(confirm(this.title)) {
				$(this.rel).load(this.href);
			}
		},
        
		/**
		 * Enviar formularios de manera asincronica, via POST
		 * Y los carga en un contenedor
		 */
		cFRemote: function(event){
			event.preventDefault();
			self = $(this);
			var button = $('[type=submit]', self);
			button.attr('disabled', 'disabled');
			var url = self.attr('action');
			var div = self.attr('data-div');
			$.post(url, self.serialize(), function(data, status){
				var capa = $('#'+div);
				capa.html(data);
				capa.hide();
				capa.show('slow');
				button.attr('disabled', null);
			});
		},

		/**
		 * Carga con AJAX al cambiar select
		 *
		 * @param Object event
		 */
		cUpdaterSelect: function(event) {
            var self = $(this); 
			$('#' + self.attr('data-update')).load(self.attr('data-action') + this.value);
		},

		/**
		 * Enlaza a las clases por defecto
		 *
		 */
		bind : function() {
            // Enlace con confirmacion
			$("a.js-confirm").live('click', this.cConfirm);
            
            // Enlace ajax
			$("a.js-remote").live('click', this.cRemote);
            
            // Enlace ajax con confirmacion
			$("a.js-remote-confirm").live('click', this.cRemoteConfirm);
            
            // Efecto show
			$("a.js-show").live('click', this.cFx('show'));
            
            // Efecto hide
			$("a.js-hide").live('click', this.cFx('hide'));
            
            // Efecto toggle
			$("a.js-toggle").live('click', this.cFx('toggle'));
            
            // Efecto fadeIn
			$("a.js-fade-in").live('click', this.cFx('fadeIn'));
            
            // Efecto fadeOut
			$("a.js-fade-out").live('click', this.cFx('fadeOut'));
            
            // Formulario ajax
			$("form.js-remote").live('submit', this.cFRemote);
            
            // Lista desplegable que actualiza con ajax
            $("select.js-remote").live('change', this.cUpdaterSelect);
            
            // Lista desplegable que actualiza con ajax
            $("select.js-remote").live('change', this.cUpdaterSelect);
		},
        
        /**
         * Implementa la autocarga de plugins, estos deben seguir
         * una convención para que pueda funcionar correctamente
         */
        autoload: function(){
            var elem = $("[class*='jp-']");
            $.each(elem, function(i, val){
                var self = $(this); //apunta al elemento con clase jp-*
                var classes = self.attr('class').split(' ');
                for (i in classes){
                    if(classes[i].substr(0, 3) == 'jp-'){
                        if($.inArray(classes[i].substr(3),$.KumbiaPHP.plugin) != -1)
                            continue;
                        $.KumbiaPHP.plugin.push(classes[i].substr(3))
                    }
                }
            });
            var head = $('head');
            for(i in $.KumbiaPHP.plugin){
                $.ajaxSetup({ cache: true});
                head.append('<link href="' + $.KumbiaPHP.publicPath + 'css/' + $.KumbiaPHP.plugin[i] + '.css" type="text/css" rel="stylesheet"/>');
				$.getScript($.KumbiaPHP.publicPath + 'javascript/jquery/jquery.' + $.KumbiaPHP.plugin[i] + '.js', function(data, text){});
            }
		},
		
		/**
		 * Inicializa el plugin
		 * 
		 */
		initialize: function() {
			// Obtiene el publicPath, restando los caracteres que sobran
			// de la ruta, respecto a la ruta de ubicacion del plugin de KumbiaPHP
			// "javascript/jquery/jquery.kumbiaphp.js"
			var src = $('script:last').attr('src');
			this.publicPath = src.substr(0, src.length - 37);
		
			// Enlaza a las clases por defecto
			$(function(){ 
				$.KumbiaPHP.bind(); 
				$.KumbiaPHP.autoload(); 
			});
		}
	}
    
	// Inicializa el plugin
	$.KumbiaPHP.initialize();
})(jQuery);
