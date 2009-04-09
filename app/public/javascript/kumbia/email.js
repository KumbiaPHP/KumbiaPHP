/*****************************************************************************
* Kumbia - PHP Rapid Development Framework                              *
******************************************************************************
*	                                                                         *
* Copyright (C) 2005 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)  *
* 	                                                                         *
* This framework is free software; you can redistribute it and/or            *
* modify it under the terms of the GNU Lesser General Public                 *
* License as published by the Free Software Foundation; either               *
* version 2.1 of the License, or (at your option) any later version.         *
*                                                                            *
* This framework is distributed in the hope that it will be useful,          *
* but WITHOUT ANY WARRANTY; without even the implied warranty of             *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU          *
* Lesser General Public License for more details.                            *
*                                                                            *
* You should have received a copy of the GNU Lesser General Public           *
* License along with this library; if not, write to the Free Software        *
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA *
*                                                                            *
******************************************************************************/

//Email Component
function saveEmail(obj) {
	document.getElementById("flid_"+obj).value = document.getElementById(obj+"_email1").value + "@" + document.getElementById(obj+"_email2").value
}
