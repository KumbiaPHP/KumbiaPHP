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
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
 
(function($) {
    /**
     * Objeto KumbiaPHP
     *
     **/
    $.KumbiaPHP = {
        /**
         * Muestra mensaje de confirmacion
         *
         * @param Object event
         **/
        cConfirm: function(event) {
            if(!confirm(this.title)) {
                event.preventDefault();
            }
        },
        /**
         * Aplica un efecto a un elemento
         *
         * @param String fx
         **/
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
         **/
        cRemote: function(event) {
            event.preventDefault();
            $(this.rel).load(this.href);
        },
        /**
         * Carga con AJAX y Confirmacion
         *
         * @param Object event
         **/
        cRemoteConfirm: function(event) {
            event.preventDefault();
            if(confirm(this.title)) {
                $(this.rel).load(this.href);
            }
        },
        /**
         * Metodo para utilizar con map de jQuery, el cual genera calendarios jsCalendar
         *
         **/
        calendar: function() {
            var tigger = $('#' + this.id + '_tigger').get(0);
            Calendar.setup({ inputField: this.id, ifFormat: tigger.alt, daFormat: tigger.alt, button: tigger.id});
        },
        
        /**
		 * Enviar formularios de manera asincronica, via POST
		 * Y los carga en un contenedor
		 **/
		cFRemote: function(e){
			e.preventDefault();
			self = $(this);
			var url = self.attr('action');
			var div = self.attr('rel');
			$.post(url, self.serialize(), function(data, status){
				$('#'+div).html(data);
			});
		},
        
        /**
         * Carga con AJAX al cambiar select
         *
         * @param Object event
         **/
        cUpdaterSelect: function(event) {
            var action = $('meta[name=js.' + this.id + '.action]').attr("content");
			var update = $('meta[name=js.' + this.id + '.update]').attr("content");
            $('#' + update).load(action + this.value);
        },
        
        
        /**
         * Enlaza a las clases por defecto
         *
         **/
        bind : function() {
            $("a.js-confirm").live('click', this.cConfirm);
            $("a.js-remote").live('click', this.cRemote);
            $("a.js-remote-confirm").live('click', this.cRemoteConfirm);
            $("a.js-show").live('click', this.cFx('show'));
            $("a.js-hide").live('click', this.cFx('hide'));
            $("a.js-toggle").live('click', this.cFx('toggle'));
            $("a.js-fade-in").live('click', this.cFx('fadeIn'));
            $("a.js-fade-out").live('click', this.cFx('fadeOut'));
            
            $("div.js-remote").ajaxComplete(function (event, XMLHttpRequest, ajaxOptions) {
                $.KumbiaPHP.bindNoLive(this);
            });
            
            // enlaza los que no funcionan con live
            this.bindNoLive(document);
        },
        /**
         * Aquellos que no se enlazan automaticamente con live
         *
         * @param DomObject parent
         **/
        bindNoLive : function(parent) {
            // lista desplegable que actualiza con ajax
            $("select.js-remote", parent).bind('change', this.cUpdaterSelect);
            
            // calendario
            $("input.js-calendar", parent).map(this.calendar);
            
            // formulario ajax
            $("form.js-remote", parent).bind('submit', this.cFRemote);
        }
    }

     // Enlaza a las clases por defecto
    $(function(){ $.KumbiaPHP.bind(); });
})(jQuery);