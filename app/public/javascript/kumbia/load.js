
/** Kumbia - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
*
* Este framework es software libre; puedes redistribuirlo y/o modificarlo
* bajo los terminos de la licencia pública general GNU tal y como fue publicada
* por la Fundación del Software Libre; desde la versión 2.1 o cualquier
* versión superior.
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; sin dejar atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

function entero(x){ return parseInt(x); }
function integer(x){ return parseInt(x); }

function enable_browse(obj, action){
	var str = window.location.toString()
	window.location = $Kumbia.get_kumbia_url(action+"/browse/")
}

//Handling Errors

if(document.all){
	onerror=handleErr

}
var txt=""
function handleErr(msg,url,l) {
	if(document.all){
		txt="KumbiaError: There was an error on this Application.\n\n"
		txt+="Error: " + msg + "\n"
		txt+="URL: " + url + "\n"
		txt+="Line: " + l + "\n\n"
		txt+="Please inform this error to your Software Provider.\n\n"
		alert(txt)
	}
	return true
}


