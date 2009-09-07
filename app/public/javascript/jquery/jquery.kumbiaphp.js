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
         **/
        cConfirm: function(event) {
            if(!confirm(this.title)) {
                event.preventDefault();
            }
        },
        /**
         * Muestra un elemento
         *
         **/
        cShow: function(event) {
            event.preventDefault();
            $(this.rel).show();
        },
        /**
         * Oculta un elemento
         *
         **/
        cHide: function(event) {
            event.preventDefault();
            $(this.rel).hide();
        },
        /**
         * Toggle de elemento
         *
         **/
        cToggle: function(event) {
            event.preventDefault();
            $(this.rel).toggle();
        },
        /**
         * Carga con AJAX
         *
         **/
        cRemote: function(event) {
            event.preventDefault();
            $(this.rel).load(this.href);
        },
        /**
         * Carga con AJAX y Confirmacion
         *
         **/
        cRemoteConfirm: function(event) {
            event.preventDefault();
            if(confirm(this.title)) {
                $(this.rel).load(this.href);
            }
        },
        /**
         * Enlaza a las clases por defecto
         *
         **/
        bind : function() {
            $("a.jsConfirm").live('click', this.cConfirm);
            $("a.jsShow").live('click', this.cShow);
            $("a.jsHide").live('click', this.cHide);
            $("a.jsToggle").live('click', this.cToggle);
            $("a.jsRemote").live('click', this.cRemote);
            $("a.jsRemoteConfirm").live('click', this.cRemoteConfirm);
        }
    }

     // Enlaza a las clases por defecto
    $(function(){ $.KumbiaPHP.bind(); });
})(jQuery);