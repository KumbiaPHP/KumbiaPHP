
/** Kumbia - PHP Rapid Development Framework ***************************
*
* Copyright (C) 2005 Andrs Felipe Gutirrez (andresfelipe at vagoogle.net)
* NumberFormat: ProWebMasters.net based script
*
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*****************************************************************************/

function validaEmail(evt){
    var kc;
	evt = (evt) ? evt : ((window.event) ? window.event : null);
	if(document.all) {
		kc = event.keyCode
	} else {
	 	kc = evt.keyCode
	}
	if(
		(kc>=65&&kc<=90)||
		(kc==50)||
		(kc==8)||
		(kc==9)||
		(kc==17)||
		(kc==16)||
		(kc==35)||
		(kc==36)||
		(kc==46)||
		(kc==109)||
		(kc==189)||
		(kc==190)||
		(kc==189)||
		(kc>=37&&kc<=40)||
		((kc>=48&&kc<=57)&&evt.shiftKey==false&&evt.altKey==false)
		) {
		//Returns
	} else {
	  	if(document.all) evt.returnValue = false
    	else evt.preventDefault()
    }
    //window.status = kc
}

/*
 * Valida que los campos requeridos del formulario contengan datos.
 * Recibe como parametros el objeto formulario y el nombre de los campos que se desean exigir.
 * Retorna true si la validacion es correcta, false en caso contrario.
 *
 * Ej. de uso:
 * form_remote_tag("cotroller/action", "update: div_id", "required: nombre_campo_1,nombre_campo_2")
 *
 * Como se ve en el ejemplo anterior, es necesario incluir el parametro 'required' y luego especificar los
 * nombres de los campos requeridos separados por comas (,). En el ejemplo anterior 'nombre_campo_1' y
 * 'nombre_campo_2' serian los nombres (name) de dos campos requeridos del formulario.
 * @param Object form Objeto formulario.
 * @param Array requiredFields Matriz con los nombres de los campos requeridos.
 * @return boolean false en caso de que se encuentren campos requeridos sin rellenar, true en caso contrario.
 */
function validaForm(form, requiredFields){

   var cont = 0;
   var campos = new Array();

   // Obtiene los campos requeridos que no contienen datos (si los hay)
   for(i = 0; i<requiredFields.length; i++){
   	   if($(requiredFields[i]).value == ''){
   	   	   campos[cont++] = $(requiredFields[i]);
   	   }
   }

   // Si faltan datos requeridos se muestra el efecto de resaltado sobre los campos.
   if(cont >= 1){
	   alert("\nEs necesario que ingrese los datos que se resaltarán");
	   for(i=0; i<cont; i++){
	   	   new Effect.Highlight(campos[i].name, {startcolor:'#FF0000', endcolor:"#ffbbbb"});
	   }
	   campos[0].focus();
   }

   // Retorna false si hay campos requeridos sin rellenar; de lo contrario true.
   return cont >= 1 ? false : true;
}


function validaText(evt){
	var kc;
	evt = (evt) ? evt : ((window.event) ? window.event : null);
	kc = evt.keyCode
	window.status = kc
	if(
	(kc>=65&&kc<=90)||
	(kc==50)||
	(kc==8)||
	(kc==9)||
	(kc==17)||
	(kc==16)||
	(kc==32)||
	(kc==186)||
	(kc==190)||
	(kc==192)||
	(kc==222)||
	(kc>=37&&kc<=40) //||
	//((kc>=48&&kc<=57)&&evt.shiftKey==false&&evt.altKey==false)
	) {
		//Returns
	} else {
		if(document.all) evt.returnValue = false
		else evt.preventDefault()
	}
}

function valNumeric(evt){
	evt = (evt) ? evt : ((window.event) ? window.event : null);
	if(
	((evt.keyCode>=48&&evt.keyCode<=57)&&evt.shiftKey==false&&evt.altKey==false)||
	((evt.keyCode>=96&&evt.keyCode<=105)&&evt.shiftKey==false&&evt.altKey==false) ||
	( evt.keyCode==8   ||
	evt.keyCode==9   ||
	evt.keyCode==13  ||
	evt.keyCode==16  ||
	evt.keyCode==17  ||
	evt.keyCode==36  ||
	evt.keyCode==35  ||
	evt.keyCode==46  ||
	evt.keyCode==37  ||
	evt.keyCode==39  ||
	evt.keyCode==110 ||
	evt.keyCode==119 ||
	evt.keyCode==190)
	){
		//Lets that key value pass
	} else {
		if(document.all) {
			evt.returnValue = false
		} else evt.preventDefault()
	}
}

function valDate(){
	if(((event.keyCode!=8&&event.keyCode!=9&&event.keyCode!=36&&event.keyCode!=35&&event.keyCode!=46&&event.keyCode!=37&&event.keyCode!=39&&event.keyCode<48))||(event.keyCode>57&&(event.keyCode<96||(event.keyCode>105&&event.keyCode!=111&&event.keyCode!=189&&event.keyCode!=109)))||(event.shiftKey==true&&event.keyCode!=55)||event.altKey==true) {
		window.event.returnValue = false
	}
}

function keyUpper(obj){
	obj.value = obj.value.toUpperCase();
	saveValue(obj)
}

function keyUpper2(obj){
	obj.value = obj.value.toUpperCase();
}

function keyUpper3(obj){
	obj.value = obj.value.toUpperCase();
}

function checkDate(obj){
	if(!obj.value) return;
	var e = RegExp("([0-9]{4}[/-][0-9]{2}[/-][0-9]{2})", "i");
	if(!obj.value) return;
	if(e.exec(obj.value)==null) {
		window.status = "EL CAMPO TIENE UN FORMATO DE FECHA INCORRECTO";
		obj.className = "iError";
	}
	else {
		d = obj.value.substr(0, 2)
		m = obj.value.substr(3, 2)
		a = obj.value.substr(6, 4)
		if((d<1)||(d>31)){
			window.status = "EL CAMPO TIENE UN FORMATO DE FECHA INCORRECTO";
			obj.className = "iError";
		} else {
			if((m<1)||(m>12)){
				window.status = "EL CAMPO TIENE UN FORMATO DE FECHA INCORRECTO";
				obj.className = "iError";
			} else {
				window.status = "Listo";
				obj.className = "iNormal";
			}
		}
	}
}

function showConfirmPassword(obj){
	if(!$('div_'+obj.name).visible()){
		new Effect.Appear('div_'+obj.name)
	}
}

function nextValidatePassword(obj){
	if(!$('div_'+obj.name).visible()){
		$('div_'+obj.name).focus()
		$('div_'+obj.name).select()
	}
}

function validatePassword(confirma, password){
	if(confirma.value!=$(password).value){
		alert('Los Passwords No son Iguales')
		$(password).focus()
		$(password).select()
	} else {
		new Effect.Fade('div_'+$(password).name)
	}
}

function checkUnique(name, obj){
	var i, n;
	if(!obj.value) return;
	if(obj.value=="@") return;
	n = 0;
	for(i=0;i<=Fields.length-1;i++){
		if(Fields[i]==name) break
	}
	for(j=0;j<=Values.length-1;j++) {
		if(Values[j][i]==obj.value) {
			if(n==1){
				if(obj.tagName=='SELECT')
				alert('Esta Opción ya fué seleccionada por favor elija otra diferente')
				obj.className = "iError"
				if(obj.tagName=='INPUT') obj.select()
				obj.focus()
				return
			}
			else n++
		}
	}
	obj.className = 'iNormal'
}

function nextField(evt, oname){
	var kc;
	evt = (evt) ? evt : ((window.event) ? window.event : null);
	kc = evt.keyCode
	if(kc==13){
		for(i=0;i<=Fields.length-1;i++) {
			if(oname==Fields[i]){
				if(i==(Fields.length-1)){
					if((document.getElementById("flid_"+Fields[0]).style.visibility!='hidden')&&
					(document.getElementById("flid_"+Fields[0]).readOnly==false)&&
					(document.getElementById("flid_"+Fields[0]).type!='hidden'))
					document.getElementById("fl_id"+Fields[0]).focus()
				} else {
					if( (document.getElementById("flid_"+Fields[i+1]).style.visibility!='hidden')&&
					(document.getElementById("flid_"+Fields[i+1]).readOnly==false)&&
					(document.getElementById("flid_"+Fields[i+1]).type!='hidden')){
						//alert(document.getElementById("flid_"+Fields[i+1]).type)
						document.getElementById("flid_"+Fields[i+1]).focus()
					}
				}
				return
			}
		}
	}
	//window.status = kc
}


