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
            event.preventDefault();
            return confirm(this.title);
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
            $(this.rel).load(this.href)
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
        }
    }

     // Enlaza a las clases por defecto
    $(function(){ $.KumbiaPHP.bind(); });
})(jQuery);
