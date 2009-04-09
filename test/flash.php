<?php
	stylesheet_link_tag('style');
	
	ob_start();
	
	Flash::show('cuidado','Kumbia puede ser adictivo.');
	flash::error('Test de flash::error');
	flash::notice('Test de flash::notice');
	flash::success('Test de flash::success');
	flash::warning('Test de flash::warning');
	flash::interactive('Test de flash::interactive');
	flash::kumbia_error('Test de flash::kumbia_error');
	
	$salida = ob_get_contents();
	ob_end_clean();
	
	
	
	$correcto = '<div class="flash_show cuidado">Kumbia puede ser adictivo.</div>
<div class="flash_show error_message">Test de flash::error</div>
<div class="flash_show notice_message">Test de flash::notice</div>
<div class="flash_show success_message">Test de flash::success</div>
<div class="flash_show warning_message">Test de flash::warning</div>
<div class="flash_show interactive_message">Test de flash::interactive</div>
<div class="flash_show error_message"><em>KumbiaError:</em> Test de flash::kumbia_error</div>
';
	
	
	if ($salida == $correcto) {
		
		flash::success('Test pasado correctamente.');
		echo 'Salida:',"\n",$salida;
	} else {
		flash::error ('FALLO el test.');
		echo 'Salida correcta debe ser:',"\n",$correcto;
		echo 'Pero la salida es:',"\n",$salida;
	}
	
?>