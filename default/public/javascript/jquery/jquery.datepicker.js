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
 * Plugin para jQuery que integra la carga asincrona de Unobstrusive Date-Picker v5 (http://www.frequency-decoder.com/2009/09/09/unobtrusive-date-picker-widget-v5)
 * solo en caso de que no se soporte el input tipo date
 * 
 * @copyright  Copyright (c) 2005-2010 Kumbia Team (http://www.kumbiaphp.com)
 * @license	http://wiki.kumbiaphp.com/Licencia	 New BSD License
 */

(function($) {
	
	var i = document.createElement("input");
    i.setAttribute("type", "date");
	
	// Verifica si se soporta date
    if(i.type == 'date') {
		return true;
	}
	
	/**
	 * Funcion encargada de integrar el DatePicker
	 * 
	 */ 
	function integrarDatePicker() {
		// Define el formato en función del estándar ISO-8601 el cual es utilizado en HTML 5
		$('.jp-datepicker').map(function() {

			var opts = { formElements : {} };    
			opts.formElements[this.id] = "Y-ds-m-ds-d";
			
			var input = $(this);
			
			// Verifica si hay mínimo
			if(input.attr('min') != undefined) {
				opts.rangeLow = input.attr('min').replace(/\-/g, '');
			}
			
			// Verifica si ha máximo
			if(input.attr('max') != undefined) {
				opts.rangeLow = input.attr('max').replace(/\-/g, '');
			}
			
			// Crea el calendario
			datePickerController.createDatePicker(opts);
		});
	}
		
	// Verifica si ya se cargo el datepicker
	if(datePickerController != undefined) {
		integrarDatePicker();
		return true;
	}
		
	// Carga el estilo de datepicker
	//$('head').append('<link href="css/datepicker.css" type="text/css" rel="stylesheet"/>');
				
	// Carga Unobstrusive DatePicker
	$.getScript($.KumbiaPHP.publicPath + 'javascript/datepicker/datepicker.js', function(){ 
		integrarDatePicker();
	});
	
})(jQuery);
