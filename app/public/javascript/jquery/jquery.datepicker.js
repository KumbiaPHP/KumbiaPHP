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
 * Plugin para jQuery que integra la carga asincrona de Unobstrusive Date-Picker (http://www.frequency-decoder.com/2006/10/02/unobtrusive-date-picker-widgit-update)
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
	
	// Define el formato en función del estándar ISO-8601 el cual es utilizado en HTML 5
	$('.jp-datepicker').map(function() {
		$(this).addClass('format-y-m-d').addClass('divider-dash');
		
		// Verifica si hay mínimo
		if($(this).attr('min') != undefined) {
			$(this).addClass('range-low-' + $(this).attr('min'));
		}
		
		// Verifica si ha máximo
		if($(this).attr('max') != undefined) {
			$(this).addClass('range-high-' + $(this).attr('max'));
		}
	});
	
	// Verifica si ya se cargo Unobstrusive Date-Picker
    if(typeof datePickerController != "undefined") {
		return true;
	}
	
	// Carga el estilo de datepicker
	//$('head').append('<link href="css/datepicker.css" type="text/css" rel="stylesheet"/>');
				
	// Carga Unobstrusive Date-Picker
	$.getScript($.KumbiaPHP.publicPath + 'javascript/datepicker/datepicker.js', function(){ 
		// Inicializa los date-picker
		datePickerController.create(); 
	});
	
})(jQuery);
