<?php
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
 * Este archivo debe ser incluido desde un controlador
 * usando include "test/adapters.php"
 *
 * @category Kumbia
 * @package Test
 * @subpackage Flash
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

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
