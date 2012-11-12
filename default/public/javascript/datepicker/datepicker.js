/*
	DatePicker v5.4 by frequency-decoder.com

	Released under a creative commons Attribution-Share Alike 3.0 Unported license (http://creativecommons.org/licenses/by-sa/3.0/)

	Please credit frequency-decoder in any derivative work - thanks.
	
	You are free:
	
	* to Share — to copy, distribute and transmit the work
	* to Remix — to adapt the work
	    
	Under the following conditions:

	* Attribution — You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).      
	* Share Alike — If you alter, transform, or build upon this work, you may distribute the resulting work only under the same, similar or a compatible license.
*/

var datePickerController = (function datePickerController() {

	var debug	       = false,
	    isOpera	     = Object.prototype.toString.call(window.opera) === "[object Opera]",
	    isMoz	       = /mozilla/.test( navigator.userAgent.toLowerCase() ) && !/(compatible|webkit)/.test( navigator.userAgent.toLowerCase() ),
	    languageInfo	= parseUILanguage(),
	    datePickers	 = {},
	    uniqueId	    = 0,
	    weeksInYearCache    = {},
	    localeImport	= false,
	    nbsp		= String.fromCharCode(160),
	    describedBy	 = "",
	    nodrag	      = false,	    
	    buttonTabIndex      = true,
	    returnLocaleDate    = false,
	    mouseWheel	  = true,	      
	    cellFormat	  = "d-sp-F-sp-Y",
	    titleFormat	 = "F-sp-d-cc-sp-Y",
	    formatParts	 = isOpera ? ["placeholder"] : ["placeholder", "sp-F-sp-Y"],
	    dividors	    = ["dt","sl","ds","cc","sp"],
	    dvParts	     = "dt|sl|ds|cc|sp",
	    dParts	      = "d|j",
	    mParts	      = "m|n|M|F",	    
	    yParts	      = "Y|y",			
	    kbEvent	     = false,
	    bespokeTitles       = {},
	    finalOpacity	= 100,
	    validFmtRegExp      = /^((sp|dt|sl|ds|cc)|([d|D|l|j|N|w|S|W|M|F|m|n|t|Y|y]))(-((sp|dt|sl|ds|cc)|([d|D|l|j|N|w|S|W|M|F|m|n|t|Y|y])))*$/,
	    rangeRegExp	 = /^((\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01]))$/,
	    wcDateRegExp	= /^(((\d\d\d\d)|(\*\*\*\*))((0[1-9]|1[012])|(\*\*))(0[1-9]|[12][0-9]|3[01]))$/;				      
		
	(function() {
		var scriptFiles = document.getElementsByTagName('script'),		    
		    scriptInner = String(scriptFiles[scriptFiles.length - 1].innerHTML).replace(/[\n\r\s\t]+/g, " ").replace(/^\s+/, "").replace(/\s+$/, ""),		    
		    json	= parseJSON(scriptInner);		
	       
		if(typeof json === "object" && !("err" in json)) {			  
			affectJSON(json);
		};
       
		if(typeof(fdLocale) != "object" && jQuery != undefined) {	
			var script;
			
			for(var i = 0; i < languageInfo.length; i++) {								 
				script = document.createElement('script');								
				script.type = "text/javascript";						 
				script.src  = jQuery.KumbiaPHP.publicPath + "javascript/datepicker/lang/" + languageInfo[i] + ".js"; 
				script.charSet = "utf-8";
								
				jQuery('head').append(script);
			};
			
			script = null;	      
		} else {
			returnLocaleDate = true;
		};			      
	})();
	
	function parseUILanguage() {				 
		var languageTag = document.getElementsByTagName('html')[0].getAttribute('lang') || document.getElementsByTagName('html')[0].getAttribute('xml:lang');
		
		if(!languageTag) {
			languageTag = "en";
		} else {
			languageTag = languageTag.toLowerCase();
		};
							    
		return languageTag.search(/^([a-z]{2,3})-([a-z]{2})$/) != -1 ? [languageTag.match(/^([a-z]{2,3})-([a-z]{2})$/)[1], languageTag] : [languageTag];		       
	};
	
	function affectJSON(json) {
		if(typeof json !== "object") { return; };
		for(key in json) {
			value = json[key];								
			switch(key.toLowerCase()) { 
				case "lang":
					if(value.search(/^[a-z]{2,3}(-([a-z]{2}))?$/i) != -1) {						
						languageInfo = [value.toLowerCase()];						   
						returnLocaleDate = true;
					};
					break;							       
				case "nodrag":
					nodrag = !!value;
					break;				
				case "buttontabindex":
					buttonTabIndex = !!value;
					break;
				case "mousewheel":
					mouseWheel = !!value;
					break;  
				case "cellformat":
					if(typeof value == "string" && value.match(validFmtRegExp)) {
						parseCellFormat(value);
					};
					break;
				case "titleformat":
					if(typeof value == "string" && value.match(validFmtRegExp)) {
						titleFormat = value;
					}; 
					break;
				case "describedby":
					if(typeof value == "string") {
						describedBy = value;
					};
					break; 
				case "finalopacity":
					if(typeof value == 'number' && (+value > 20 && +value <= 100)) {
						finalOpacity = parseInt(value, 10);
					}; 
					break; 
				case "bespoketitles":
					bespokeTitles = {};
					for(var dt in value) {
						bespokeTitles[dt] = value[dt];
					};																	     
			};	  
		};	
	};		  
	
	function parseCellFormat(value) {		  
		if(isOpera) { 
			// Don't use hidden text for opera due to focus outline problems	       
			formatParts = ["placeholder"];
			cellFormat  = "j-sp-F-sp-Y";  
			return;
		};   
		
		// I'm sure this could be done with a regExp and a split in one line... seriously...
		var parts       = value.split("-"),
		    fullParts   = [],
		    tmpParts    = [],
		    part;			      
		
		for(var pt = 0; pt < parts.length; pt++) {
			part = parts[pt];			 
			if(part == "j" || part == "d") { 
				if(tmpParts.length) {
					fullParts.push(tmpParts.join("-")); 
					tmpParts = [];
				};
				fullParts.push("placeholder");   
			} else { 
				tmpParts.push(part);
			};					     
		};		  
		
		if(tmpParts.length) {
			fullParts.push(tmpParts.join("-"));					 
		};
		
		if(!fullParts.length || fullParts.length > 3) {
			formatParts = ["placeholder", "sp-F-sp-Y"];
			cellFormat = "j-sp-F-sp-Y"; 
			return;
		};		
		
		formatParts = fullParts;
		cellFormat  = value;	       
	};
	 
	function pad(value, length) { 
		length = length || 2; 
		return "0000".substr(0,length - Math.min(String(value).length, length)) + value; 
	};
	
	function addEvent(obj, type, fn) { 
		try {		 
			if( obj.attachEvent ) {
				obj["e"+type+fn] = fn;
				obj[type+fn] = function(){obj["e"+type+fn]( window.event );};
				obj.attachEvent( "on"+type, obj[type+fn] );
			} else {
				obj.addEventListener( type, fn, true );
			};
		} catch(err) {}
	};
	
	function removeEvent(obj, type, fn) {
		try {
			if( obj.detachEvent ) {
				obj.detachEvent( "on"+type, obj[type+fn] );
				obj[type+fn] = null;
			} else {
				obj.removeEventListener( type, fn, true );
			};
		} catch(err) {};
	};   

	function stopEvent(e) {
		e = e || document.parentWindow.event;
		if(e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		};
		/*@cc_on
		@if(@_win32)
		e.cancelBubble = true;
		e.returnValue = false;
		@end
		@*/
		return false;
	};
	
	function parseJSON(str) {
		// Check we have a String
		if(typeof str !== 'string' || str == "") { return {}; };		 
		try {
			// Does a JSON (native or not) Object exist			      
			if(typeof JSON === "object" && JSON.parse) {					      
				return window.JSON.parse(str);  
			// Genious code taken from: http://kentbrewster.com/badges/						      
			} else if(/lang|buttontabindex|mousewheel|cellformat|titleformat|nodrag|describedby/.test(str.toLowerCase())) {					       
				var f = Function(['var document,top,self,window,parent,Number,Date,Object,Function,',
					'Array,String,Math,RegExp,Image,ActiveXObject;',
					'return (' , str.replace(/<\!--.+-->/gim,'').replace(/\bfunction\b/g,'function­') , ');'].join(''));
				return f();			  
			};
		} catch (e) { };
		
		if(debug) {
			throw "Could not parse the JSON object";
		};
		
		return {"err":"Could not parse the JSON object"};					    
	};	

	function setARIARole(element, role) {
		if(element && element.tagName) {
			element.setAttribute("role", role);
		};
	};
	
	function setARIAProperty(element, property, value) {
	if(element && element.tagName) {
			element.setAttribute("aria-" + property, value);
		};  
    };

	// The datePicker object itself 
	function datePicker(options) {				      
		this.dateSet	     = null;		 
		this.timerSet	    = false;
		this.visible	     = false;
		this.fadeTimer	   = null;
		this.timer	       = null;
		this.yearInc	     = 0;
		this.monthInc	    = 0;
		this.dayInc	      = 0;
		this.mx		  = 0;
		this.my		  = 0;
		this.x		   = 0;
		this.y		   = 0; 
		this.created	     = false;
		this.disabled	    = false;
		this.opacity	     = 0; 
		this.opacityTo	   = 99;
		this.inUpdate	    = false;			      
		this.kbEventsAdded       = false;
		this.fullCreate	  = false;
		this.selectedTD	  = null;
		this.cursorTD	    = null;
		this.cursorDate	  = options.cursorDate ? options.cursorDate : "",       
		this.date		= options.cursorDate ? new Date(+options.cursorDate.substr(0,4), +options.cursorDate.substr(4,2) - 1, +options.cursorDate.substr(6,2)) : new Date();
		this.defaults	    = {};
		this.dynDisabledDates    = {};
		this.firstDayOfWeek      = localeImport.firstDayOfWeek; 
		this.interval	    = new Date();
		this.clickActivated      = false;
		this.noFocus	     = true;
		this.kbEvent	     = false; 
		this.disabledDates       = false;
		this.enabledDates	= false;
		this.delayedUpdate       = false;  
		this.bespokeTitles       = {};	     
		
		for(var thing in options) {
			if(thing.search(/callbacks|formElements|formatMasks/) != -1) continue;
			this[thing] = options[thing];		 
		};
   
		/*@cc_on
		@if(@_win32)		   
		this.iePopUp	     = null;
		this.isIE7	       = false;		 
		@end
		@*/
		
		/*@cc_on
		@if(@_jscript_version <= 5.7)
		this.isIE7	       = document.documentElement && typeof document.documentElement.style.maxHeight != "undefined";
		@end
		@*/
		
		for(var i = 0, prop; prop = ["callbacks", "formElements", "formatMasks"][i]; i++) { 
			this[prop] = {};			
			for(var thing in options[prop]) {				
				this[prop][thing] = options[prop][thing];		 
			};
		};
		
		// Adjust time to stop daylight savings madness on windows
		this.date.setHours(5);	      
		
		this.changeHandler = function() {			
			o.setDateFromInput();  
			o.callback("dateset", o.createCbArgObj());										 
		};
		this.createCbArgObj = function() {			
			return this.dateSet ? {"id":this.id,"date":this.dateSet,"dd":pad(this.date.getDate()),"mm":pad(this.date.getMonth() + 1),"yyyy":this.date.getFullYear()} : {"id":this.id,"date":null,"dd":null,"mm":null,"yyyy":null};			 
		};
		this.getScrollOffsets = function() {			 
			if(typeof(window.pageYOffset) == 'number') {
				//Netscape compliant
				return [window.pageXOffset, window.pageYOffset];				
			} else if(document.body && (document.body.scrollLeft || document.body.scrollTop)) {
				//DOM compliant
				return [document.body.scrollLeft, document.body.scrollTop];				
			} else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
				//IE6 standards compliant mode
				return [document.documentElement.scrollLeft, document.documentElement.scrollTop];
			};
			return [0,0];
		};
		this.reposition = function() {
			if(!o.created || o.staticPos) { return; };

			o.div.style.visibility = "hidden";
			o.div.style.left = o.div.style.top = "0px";			   
			o.div.style.display = "block";

			var osh	 = o.div.offsetHeight,
			    osw	 = o.div.offsetWidth,
			    elem	= document.getElementById('fd-but-' + o.id),
			    pos	 = o.truePosition(elem),
			    trueBody    = (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body,
			    sOffsets    = o.getScrollOffsets(),
			    scrollTop   = sOffsets[1], 
			    scrollLeft  = sOffsets[0],
			    fitsBottom  = parseInt(trueBody.clientHeight+scrollTop) > parseInt(osh+pos[1]+elem.offsetHeight+2),
			    fitsTop     = parseInt(pos[1]-(osh+elem.offsetHeight+2)) > parseInt(scrollTop); 
			
			o.div.style.visibility = "visible";

			o.div.style.left = Number(parseInt(trueBody.clientWidth+scrollLeft) < parseInt(osw+pos[0]) ? Math.abs(parseInt((trueBody.clientWidth+scrollLeft) - osw)) : pos[0]) + "px";
			o.div.style.top  = (fitsBottom || !fitsTop) ? Math.abs(parseInt(pos[1] + elem.offsetHeight + 2)) + "px" : Math.abs(parseInt(pos[1] - (osh + 2))) + "px";
			/*@cc_on
			@if(@_jscript_version <= 5.7)
			if(o.isIE7) return;
			o.iePopUp.style.top    = o.div.style.top;
			o.iePopUp.style.left   = o.div.style.left;
			o.iePopUp.style.width  = osw + "px";
			o.iePopUp.style.height = (osh - 2) + "px";
			@end
			@*/
		};
		this.removeOldFocus = function() {
			var td = document.getElementById(o.id + "-date-picker-hover");
			if(td) {					
				try { 
					td.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", "-1");
					td.tabIndex = -1;					  
					td.className = td.className.replace(/date-picker-hover/, "");					 
					td.id = ""; 
					td.onblur  = null; 
					td.onfocus = null;									     
				} catch(err) {};
			};
		}; 
		this.addAccessibleDate = function() {
			var td   = document.getElementById(o.id + "-date-picker-hover");			    
				
			if(td && !(td.getElementsByTagName("span").length)) {							  
				var ymd = td.className.match(/cd-([\d]{4})([\d]{2})([\d]{2})/),
				    noS = (td.className.search(/date-picker-unused|out-of-range|day-disabled|no-selection|not-selectable/) != -1),
				    spn  = document.createElement('span'),
				    spnC;					
			
				spn.className       = "fd-screen-reader";;
				
				while(td.firstChild) td.removeChild(td.firstChild);
				
				if(noS) {
					spnC = spn.cloneNode(false);
					spnC.appendChild(document.createTextNode(getTitleTranslation(13)));
					td.appendChild(spnC);
				};
				
				for(var pt = 0, part; part = formatParts[pt]; pt++) {
					if(part == "placeholder") {
						td.appendChild(document.createTextNode(+ymd[3]));
					} else {
						spnC = spn.cloneNode(false);
						spnC.appendChild(document.createTextNode(printFormattedDate(new Date(ymd[1], +ymd[2]-1, ymd[3]), part, true)));
						td.appendChild(spnC);
					};						
				};
			};
		};
		this.setNewFocus = function() {											     
			var td = document.getElementById(o.id + "-date-picker-hover");
			if(td) {
				try {					     
					td.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", "0");				
					td.tabIndex  = 0;  
														   
					td.className = td.className.replace(/date-picker-hover/, "") + " date-picker-hover"; 
					if(!this.clickActivated) {						
						td.onblur    = o.onblur;  
						td.onfocus   = o.onfocus;				   
					};
																							 
					if(!isOpera && !this.clickActivated) o.addAccessibleDate();
					
					if(!this.noFocus && !this.clickActivated) {																		   
						setTimeout(function() { try { td.focus(); } catch(err) {}; }, 0);
					};					 
				} catch(err) { };
			};
		};
		this.setCursorDate = function(yyyymmdd) {			
			if(String(yyyymmdd).search(/^([0-9]{8})$/) != -1) {
				this.date = new Date(+yyyymmdd.substr(0,4), +yyyymmdd.substr(4,2) - 1, +yyyymmdd.substr(6,2));
				this.cursorDate = yyyymmdd;
				
				if(this.staticPos) {					 
					this.updateTable();
				};												  
			};
		};		  
		this.updateTable = function(noCallback) {  
			if(!o || o.inUpdate || !o.created) return;
			
			o.inUpdate = true;					 
			o.removeOldFocus();
			
			if(o.timerSet && !o.delayedUpdate) {
				if(o.monthInc) {
					var n = o.date.getDate(),
					    d = new Date(o.date);			 
		       
					d.setDate(2);					       
					d.setMonth(d.getMonth() + o.monthInc * 1);
					d.setDate(Math.min(n, daysInMonth(d.getMonth(),d.getFullYear())));
					
					o.date = new Date(d);
				} else {				 
					o.date.setDate(Math.min(o.date.getDate()+o.dayInc, daysInMonth(o.date.getMonth()+o.monthInc,o.date.getFullYear()+o.yearInc)));
					o.date.setMonth(o.date.getMonth() + o.monthInc);					
					o.date.setFullYear(o.date.getFullYear() + o.yearInc);
				};				       
			}; 
	
			o.outOfRange();
			if(!o.noToday) { o.disableTodayButton(); };
			o.showHideButtons(o.date);
		
			var cd = o.date.getDate(),
			    cm = o.date.getMonth(),
			    cy = o.date.getFullYear(),
			    cursorDate = (String(cy) + pad(cm+1) + pad(cd)),
			    tmpDate    = new Date(cy, cm, 1);		      
			
			tmpDate.setHours(5);
			
			var dt, cName, td, i, currentDate, cellAdded, col, currentStub, abbr, bespokeRenderClass, spnC, dateSetD,
			weekDayC	    = ( tmpDate.getDay() + 6 ) % 7,		
			firstColIndex       = (((weekDayC - o.firstDayOfWeek) + 7 ) % 7) - 1,
			dpm		 = daysInMonth(cm, cy),
			today	       = new Date(),			
			stub		= String(tmpDate.getFullYear()) + pad(tmpDate.getMonth()+1),
			cellAdded	   = [4,4,4,4,4,4],								   
			lm		  = new Date(cy, cm-1, 1),
			nm		  = new Date(cy, cm+1, 1),			  
			daySub	      = daysInMonth(lm.getMonth(), lm.getFullYear()),		
			stubN	       = String(nm.getFullYear()) + pad(nm.getMonth()+1),
			stubP	       = String(lm.getFullYear()) + pad(lm.getMonth()+1),		
			weekDayN	    = (nm.getDay() + 6) % 7,
			weekDayP	    = (lm.getDay() + 6) % 7,				       
			today	       = today.getFullYear() + pad(today.getMonth()+1) + pad(today.getDate()),
			spn		 = document.createElement('span');			
			
			o.firstDateShown    = !o.constrainSelection && o.fillGrid && (0 - firstColIndex < 1) ? String(stubP) + (daySub + (0 - firstColIndex)) : stub + "01";	    
			o.lastDateShown     = !o.constrainSelection && o.fillGrid ? stubN + pad(41 - firstColIndex - dpm) : stub + String(dpm);
			o.currentYYYYMM     = stub;		    
		
			bespokeRenderClass  = o.callback("redraw", {id:o.id, dd:pad(cd), mm:pad(cm+1), yyyy:cy, firstDateDisplayed:o.firstDateShown, lastDateDisplayed:o.lastDateShown}) || {};					    
			dts		 = o.getDates(cy, cm+1);			       
		
			o.checkSelectedDate();
			
			dateSetD	    = (o.dateSet != null) ? o.dateSet.getFullYear() + pad(o.dateSet.getMonth()+1) + pad(o.dateSet.getDate()) : false;
			spn.className       = "fd-screen-reader";
			
			if(this.selectedTD != null) {
				setARIAProperty(this.selectedTD, "selected", false);
				this.selectedTD = null;
			};
			
			for(var curr = 0; curr < 42; curr++) {
				row  = Math.floor(curr / 7);			 
				td   = o.tds[curr];
				spnC = spn.cloneNode(false); 
				
				while(td.firstChild) td.removeChild(td.firstChild);
				
				if((curr > firstColIndex && curr <= (firstColIndex + dpm)) || o.fillGrid) {
					currentStub     = stub;
					weekDay	 = weekDayC;				
					dt	      = curr - firstColIndex;
					cName	   = [];					 
					selectable      = true;				     
					
					if(dt < 1) {
						dt	      = daySub + dt;
						currentStub     = stubP;
						weekDay	 = weekDayP;					
						selectable      = !o.constrainSelection;
						cName.push("month-out");						  
					} else if(dt > dpm) {
						dt -= dpm;
						currentStub     = stubN;
						weekDay	 = weekDayN;					
						selectable      = !o.constrainSelection; 
						cName.push("month-out");											   
					}; 
					
					weekDay = ( weekDay + dt + 6 ) % 7;
					
					cName.push("day-" + localeDefaults.dayAbbrs[weekDay].toLowerCase());
					
					currentDate = currentStub + String(dt < 10 ? "0" : "") + dt;			    
					
					if(o.rangeLow && +currentDate < +o.rangeLow || o.rangeHigh && +currentDate > +o.rangeHigh) {					  
						td.className = "out-of-range";  
						td.title = ""; 
						td.appendChild(document.createTextNode(dt));					     
						if(o.showWeeks) { cellAdded[row] = Math.min(cellAdded[row], 2); };																	       
					} else {  
						if(selectable) {													
							td.title = titleFormat ? printFormattedDate(new Date(+String(currentStub).substr(0,4), +String(currentStub).substr(4, 2) - 1, +dt), titleFormat, true) : "";												      
							cName.push("cd-" + currentDate + " yyyymm-" + currentStub + " mmdd-" + currentStub.substr(4,2) + pad(dt));
						} else {  
							td.title = titleFormat ? getTitleTranslation(13) + " " + printFormattedDate(new Date(+String(currentStub).substr(0,4), +String(currentStub).substr(4, 2) - 1, +dt), titleFormat, true) : "";								       
							cName.push("yyyymm-" + currentStub + " mmdd-" + currentStub.substr(4,2) + pad(dt) + " not-selectable");
						};																	     
						
						if(currentDate == today) { cName.push("date-picker-today"); };

						if(dateSetD == currentDate) { 
							cName.push("date-picker-selected-date"); 
							setARIAProperty(td, "selected", "true");
							this.selectedTD = td;
						};

						if(o.disabledDays[weekDay] || dts[currentDate] == 0) { cName.push("day-disabled"); if(titleFormat && selectable) { td.title = getTitleTranslation(13) + " " + td.title; }; }
					
						if(currentDate in bespokeRenderClass) { cName.push(bespokeRenderClass[currentDate]); }
					
						if(o.highlightDays[weekDay]) { cName.push("date-picker-highlight"); };

						if(cursorDate == currentDate) { 
							td.id = o.id + "-date-picker-hover";																				 
						};      
										   
						td.appendChild(document.createTextNode(dt));
						td.className = cName.join(" ");
					       
						if(o.showWeeks) {							 
							cellAdded[row] = Math.min(cName[0] == "month-out" ? 3 : 1, cellAdded[row]);							  
						}; 
					};		       
				} else {
					td.className = "date-picker-unused";														    
					td.appendChild(document.createTextNode(nbsp));
					td.title = "";									      
				};						  
				
				if(o.showWeeks && curr - (row * 7) == 6) { 
					while(o.wkThs[row].firstChild) o.wkThs[row].removeChild(o.wkThs[row].firstChild);					 
					o.wkThs[row].appendChild(document.createTextNode(cellAdded[row] == 4 && !o.fillGrid ? nbsp : getWeekNumber(cy, cm, curr - firstColIndex - 6)));
					o.wkThs[row].className = "date-picker-week-header" + (["",""," out-of-range"," month-out",""][cellAdded[row]]);					  
				};				
			};	    
			
			var span = o.titleBar.getElementsByTagName("span");
			while(span[0].firstChild) span[0].removeChild(span[0].firstChild);
			while(span[1].firstChild) span[1].removeChild(span[1].firstChild);
			span[0].appendChild(document.createTextNode(getMonthTranslation(cm, false) + nbsp));
			span[1].appendChild(document.createTextNode(cy));
			
			if(o.timerSet) {
				o.timerInc = 50 + Math.round(((o.timerInc - 50) / 1.8));
				o.timer = window.setTimeout(o.updateTable, o.timerInc);
			};
			
			o.inUpdate = o.delayedUpdate = false; 
			o.setNewFocus();			 
		};
		
		this.destroy = function() {
			
			if(document.getElementById("fd-but-" + this.id)) {
				document.getElementById("fd-but-" + this.id).parentNode.removeChild(document.getElementById("fd-but-" + this.id));	
			};
			
			if(!this.created) { return; };
			
			// Cleanup for Internet Explorer
			removeEvent(this.table, "mousedown", o.onmousedown);  
			removeEvent(this.table, "mouseover", o.onmouseover);
			removeEvent(this.table, "mouseout", o.onmouseout);
			removeEvent(document, "mousedown", o.onmousedown);
			removeEvent(document, "mouseup",   o.clearTimer);
			
			if (window.addEventListener && !window.devicePixelRatio) {
				try {
					window.removeEventListener('DOMMouseScroll', this.onmousewheel, false);
				} catch(err) {};				 
			} else {
				removeEvent(document, "mousewheel", this.onmousewheel);
				removeEvent(window,   "mousewheel", this.onmousewheel);
			}; 
			o.removeOnFocusEvents();
			clearTimeout(o.fadeTimer);
			clearTimeout(o.timer);

			/*@cc_on
			@if(@_jscript_version <= 5.7)			 
			if(!o.staticPos && !o.isIE7) {
				try {
					o.iePopUp.parentNode.removeChild(o.iePopUp);
					o.iePopUp = null;
				} catch(err) {};
			};
			@end
			@*/			 

			if(this.div && this.div.parentNode) {
				this.div.parentNode.removeChild(this.div);
			};
						 
			o = null;
		};
		this.resizeInlineDiv = function()  {			
			o.div.style.width = o.table.offsetWidth + "px";
			o.div.style.height = o.table.offsetHeight + "px";
		};
		this.create = function() {
			
			if(document.getElementById("fd-" + this.id)) return;
			
			this.noFocus = true; 
			
			function createTH(details) {
				var th = document.createElement('th');
				if(details.thClassName) th.className = details.thClassName;
				if(details.colspan) {
					/*@cc_on
					/*@if (@_win32)
					th.setAttribute('colSpan',details.colspan);
					@else @*/
					th.setAttribute('colspan',details.colspan);
					/*@end
					@*/
				};
				/*@cc_on
				/*@if (@_win32)
				th.unselectable = "on";
				/*@end@*/
				return th;
			};
			function createThAndButton(tr, obj) {
				for(var i = 0, details; details = obj[i]; i++) {
					var th = createTH(details);
					tr.appendChild(th);
					var but = document.createElement('span');
					but.className = details.className;
					but.id = o.id + details.id;
					but.appendChild(document.createTextNode(details.text || o.nbsp));
					but.title = details.title || "";					  
					/*@cc_on
					/*@if(@_win32)
					th.unselectable = but.unselectable = "on";
					/*@end@*/
					th.appendChild(but);
				};
			};  
			
			this.div		     = document.createElement('div');
			this.div.id		  = "fd-" + this.id;
			this.div.className	   = "datePicker";  
			
			// Attempt to hide the div from screen readers during content creation
			this.div.style.visibility = "hidden";
			this.div.style.display = "none";
						
			// Set the ARIA describedby property if the required block available
			if(this.describedBy && document.getElementById(this.describedBy)) {
				setARIAProperty(this.div, "describedby", this.describedBy);
			};
			
			// Set the ARIA labelled property if the required label available
			if(this.labelledBy) {
				setARIAProperty(this.div, "labelledby", this.labelledBy.id);
			};
			      
			var tr, row, col, tableHead, tableBody, tableFoot;

			this.table	     = document.createElement('table');
			this.table.className   = "datePickerTable";			 
			this.table.onmouseover = this.onmouseover;
			this.table.onmouseout  = this.onmouseout;
			this.table.onclick     = this.onclick;
			
			if(this.staticPos) {
				this.table.onmousedown  = this.onmousedown;
			};

			this.div.appendChild(this.table);   
			
			var dragEnabledCN = !this.dragDisabled ? " drag-enabled" : "";
				
			if(!this.staticPos) {
				this.div.style.visibility = "hidden";
				this.div.className += dragEnabledCN;
				document.getElementsByTagName('body')[0].appendChild(this.div);
							      
				/*@cc_on
				@if(@_jscript_version <= 5.7) 
				
				if(!this.isIE7) {					 
					this.iePopUp = document.createElement('iframe');
					this.iePopUp.src = "javascript:'<html></html>';";
					this.iePopUp.setAttribute('className','iehack');
					// Remove iFrame from tabIndex					
			    this.iePopUp.setAttribute("tabIndex", -1);			      
					// Hide it from ARIA aware technologies
			    setARIARole(this.iePopUp, "presentation");
					setARIAProperty(this.iePopUp, "hidden", "true");			    
					this.iePopUp.scrolling = "no";
					this.iePopUp.frameBorder = "0";
					this.iePopUp.name = this.iePopUp.id = this.id + "-iePopUpHack";
					document.body.appendChild(this.iePopUp);					
				};
				
				@end
				@*/
				
				// Aria "hidden" property for non active popup datepickers
				setARIAProperty(this.div, "hidden", "true");
			} else {
				elem = document.getElementById(this.positioned ? this.positioned : this.id);
				if(!elem) {
					this.div = null;
					if(debug) throw this.positioned ? "Could not locate a datePickers associated parent element with an id:" + this.positioned : "Could not locate a datePickers associated input with an id:" + this.id;
					return;
				};

				this.div.className += " static-datepicker";			  

				if(this.positioned) {
					elem.appendChild(this.div);
				} else {
					elem.parentNode.insertBefore(this.div, elem.nextSibling);
				};
				
				if(this.hideInput) {
					for(var elemID in this.formElements) {
						elem = document.getElementById(elemID);
						if(elem) {
							elem.className += " fd-hidden-input";
						};	
					};					
				};								  
									  
				setTimeout(this.resizeInlineDiv, 300);			       
			};			  
				
			// ARIA Grid role
			setARIARole(this.div, "grid");
		       
			if(this.statusFormat) {
				tableFoot = document.createElement('tfoot');
				this.table.appendChild(tableFoot);
				tr = document.createElement('tr');
				tr.className = "date-picker-tfoot";
				tableFoot.appendChild(tr);				
				this.statusBar = createTH({thClassName:"date-picker-statusbar" + dragEnabledCN, colspan:this.showWeeks ? 8 : 7});
				tr.appendChild(this.statusBar); 
				this.updateStatus(); 
			};

			tableHead = document.createElement('thead');
			this.table.appendChild(tableHead);

			tr  = document.createElement('tr');
			setARIARole(tr, "presentation");
			
			tableHead.appendChild(tr);

			// Title Bar
			this.titleBar = createTH({thClassName:"date-picker-title" + dragEnabledCN, colspan:this.showWeeks ? 8 : 7});
			
			tr.appendChild(this.titleBar);
			tr = null;

			var span = document.createElement('span');
			span.appendChild(document.createTextNode(nbsp));
			span.className = "month-display" + dragEnabledCN; 
			this.titleBar.appendChild(span);

			span = document.createElement('span');
			span.appendChild(document.createTextNode(nbsp));
			span.className = "year-display" + dragEnabledCN; 
			this.titleBar.appendChild(span);

			span = null;

			tr  = document.createElement('tr');
			setARIARole(tr, "presentation");
			tableHead.appendChild(tr);

			createThAndButton(tr, [
			{className:"prev-but prev-year",  id:"-prev-year-but", text:"\u00AB", title:getTitleTranslation(2) },
			{className:"prev-but prev-month", id:"-prev-month-but", text:"\u2039", title:getTitleTranslation(0) },
			{colspan:this.showWeeks ? 4 : 3, className:"today-but", id:"-today-but", text:getTitleTranslation(4)},
			{className:"next-but next-month", id:"-next-month-but", text:"\u203A", title:getTitleTranslation(1)},
			{className:"next-but next-year",  id:"-next-year-but", text:"\u00BB", title:getTitleTranslation(3) }
			]);

			tableBody = document.createElement('tbody');
			this.table.appendChild(tableBody);

			var colspanTotal = this.showWeeks ? 8 : 7,
			    colOffset    = this.showWeeks ? 0 : -1,
			    but, abbr;   
		
			for(var rows = 0; rows < 7; rows++) {
				row = document.createElement('tr');

				if(rows != 0) {
					// ARIA Grid role
					setARIARole(row, "row");
					tableBody.appendChild(row);   
				} else {
					tableHead.appendChild(row);
				};

				for(var cols = 0; cols < colspanTotal; cols++) {										
					if(rows === 0 || (this.showWeeks && cols === 0)) {
						col = document.createElement('th');											      
					} else {
						col = document.createElement('td');											   
						setARIAProperty(col, "describedby", this.id + "-col-" + cols + (this.showWeeks ? " " + this.id + "-row-" + rows : ""));
						setARIAProperty(col, "selected", "false");						 
					};
					
					/*@cc_on@*/
					/*@if(@_win32)
					col.unselectable = "on";
					/*@end@*/  
					
					row.appendChild(col);
					if((this.showWeeks && cols > 0 && rows > 0) || (!this.showWeeks && rows > 0)) {						
						setARIARole(col, "gridcell"); 
					} else {
						if(rows === 0 && cols > colOffset) {
							col.className = "date-picker-day-header";
							col.scope = "col";
							setARIARole(col, "columnheader"); 
							col.id = this.id + "-col-" + cols;					  
						} else {
							col.className = "date-picker-week-header";
							col.scope = "row";
							setARIARole(col, "rowheader");
							col.id = this.id + "-row-" + rows;
						};
					};
				};
			};

			col = row = null; 
		
			this.ths = this.table.getElementsByTagName('thead')[0].getElementsByTagName('tr')[2].getElementsByTagName('th');
			for (var y = 0; y < colspanTotal; y++) {
				if(y == 0 && this.showWeeks) {
					this.ths[y].appendChild(document.createTextNode(getTitleTranslation(6)));
					this.ths[y].title = getTitleTranslation(8);
					continue;
				};

				if(y > (this.showWeeks ? 0 : -1)) {
					but = document.createElement("span");
					but.className = "fd-day-header";					
					/*@cc_on@*/
					/*@if(@_win32)
					but.unselectable = "on";
					/*@end@*/
					this.ths[y].appendChild(but);
				};
			};
		
			but = null; 
					
			this.trs	     = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
			this.tds	     = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('td');
			this.butPrevYear     = document.getElementById(this.id + "-prev-year-but");
			this.butPrevMonth    = document.getElementById(this.id + "-prev-month-but");
			this.butToday	= document.getElementById(this.id + "-today-but");
			this.butNextYear     = document.getElementById(this.id + "-next-year-but"); 
			this.butNextMonth    = document.getElementById(this.id + "-next-month-but");
	
			if(this.noToday) {
				this.butToday.style.display = "none";	
			};
			
			if(this.showWeeks) {
				this.wkThs = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('th');
				this.div.className += " weeks-displayed";
			};

			tableBody = tableHead = tr = createThAndButton = createTH = null;

			if(this.rangeLow && this.rangeHigh && (this.rangeHigh - this.rangeLow < 7)) { this.equaliseDates(); };			
							     
			this.updateTableHeaders();
			this.created = true;								    
			this.updateTable();			 
			
			if(this.staticPos) {				 
				this.visible = true;
				this.opacity = this.opacityTo = this.finalOpacity;											      
				this.div.style.visibility = "visible";		       
				this.div.style.display = "block";
				this.noFocus = true;							  
				this.fade();
			} else {				     
				this.reposition();
				this.div.style.visibility = "visible";
				this.fade();
				this.noFocus = true;   
			};   
			
			this.callback("domcreate", { "id":this.id });						   
		};		 
		this.fade = function() {
			window.clearTimeout(o.fadeTimer);
			o.fadeTimer = null;   
			var diff = Math.round(o.opacity + ((o.opacityTo - o.opacity) / 4)); 
			o.setOpacity(diff);  
			if(Math.abs(o.opacityTo - diff) > 3 && !o.noFadeEffect) {				 
				o.fadeTimer = window.setTimeout(o.fade, 50);
			} else {
				o.setOpacity(o.opacityTo);
				if(o.opacityTo == 0) {
					o.div.style.display    = "none";
					o.div.style.visibility = "hidden";
					setARIAProperty(o.div, "hidden", "true");
					o.visible = false;
				} else {
					setARIAProperty(o.div, "hidden", "false");
					o.visible = true;					
				};
			};
		};		  
		this.trackDrag = function(e) {
			e = e || window.event;
			var diffx = (e.pageX?e.pageX:e.clientX?e.clientX:e.x) - o.mx;
			var diffy = (e.pageY?e.pageY:e.clientY?e.clientY:e.Y) - o.my;
			o.div.style.left = Math.round(o.x + diffx) > 0 ? Math.round(o.x + diffx) + 'px' : "0px";
			o.div.style.top  = Math.round(o.y + diffy) > 0 ? Math.round(o.y + diffy) + 'px' : "0px";
			/*@cc_on
			@if(@_jscript_version <= 5.7)			 
			if(o.staticPos || o.isIE7) return;
			o.iePopUp.style.top    = o.div.style.top;
			o.iePopUp.style.left   = o.div.style.left;
			@end
			@*/
		};
		this.stopDrag = function(e) {
			var b = document.getElementsByTagName("body")[0];
			b.className = b.className.replace(/fd-drag-active/g, "");
			removeEvent(document,'mousemove',o.trackDrag, false);
			removeEvent(document,'mouseup',o.stopDrag, false);
			o.div.style.zIndex = 9999;
		}; 
		this.onmousedown = function(e) {
			e = e || document.parentWindow.event;
			var el     = e.target != null ? e.target : e.srcElement,
			    origEl = el,
			    hideDP = true,
			    reg    = new RegExp("^fd-(but-)?" + o.id + "$");
			
			o.mouseDownElem = null;
		       
			// Are we within the wrapper div or the button    
			while(el) {
				if(el.id && el.id.length && el.id.search(reg) != -1) { 
					hideDP = false;
					break;
				};
				try { el = el.parentNode; } catch(err) { break; };
			};
			
			// If not, then ...     
			if(hideDP) {							
				hideAll();							    
				return true;								  
			};
			
			if((o.div.className + origEl.className).search('fd-disabled') != -1) { return true; };													    
			
			// We check the mousedown events on the buttons
			if(origEl.id.search(new RegExp("^" + o.id + "(-prev-year-but|-prev-month-but|-next-month-but|-next-year-but)$")) != -1) {
				
				o.mouseDownElem = origEl;
				
				addEvent(document, "mouseup", o.clearTimer);
				addEvent(origEl, "mouseout",  o.clearTimer); 
								 
				var incs = {
					"-prev-year-but":[0,-1,0],
					"-prev-month-but":[0,0,-1],
					"-next-year-but":[0,1,0],
					"-next-month-but":[0,0,1]
				    },
				    check = origEl.id.replace(o.id, ""),
				    dateYYYYMM = Number(o.date.getFullYear() + pad(o.date.getMonth()+1));
				
				o.timerInc      = 800;
				o.timerSet      = true;
				o.dayInc	= incs[check][0];
				o.yearInc       = incs[check][1];
				o.monthInc      = incs[check][2]; 
				o.accellerator  = 1;
				
				if(!(o.currentYYYYMM == dateYYYYMM)) {
					if((o.currentYYYYMM < dateYYYYMM && (o.yearInc == -1 || o.monthInc == -1)) || (o.currentYYYYMM > dateYYYYMM && (o.yearInc == 1 || o.monthInc == 1))) {
						o.delayedUpdate = false; 
						o.timerInc = 1200;						
					} else {
						o.delayedUpdate = true;
						o.timerInc = 800;						
					};  
				};
				
				o.updateTable();    
				
				return stopEvent(e);
							    
			} else if(el.className.search("drag-enabled") != -1) {				  
				o.mx = e.pageX ? e.pageX : e.clientX ? e.clientX : e.x;
				o.my = e.pageY ? e.pageY : e.clientY ? e.clientY : e.Y;
				o.x  = parseInt(o.div.style.left);
				o.y  = parseInt(o.div.style.top);
				addEvent(document,'mousemove',o.trackDrag, false);
				addEvent(document,'mouseup',o.stopDrag, false);
				var b = document.getElementsByTagName("body")[0];
				b.className = b.className.replace(/fd-drag-active/g, "") + " fd-drag-active";
				o.div.style.zIndex = 10000;
				
				return stopEvent(e);
			};
			return true;								      
		}; 
		this.onclick = function(e) {
			if(o.opacity != o.opacityTo || o.disabled) return stopEvent(e);
			
			e = e || document.parentWindow.event;
			var el = e.target != null ? e.target : e.srcElement;			 
			  
			while(el.parentNode) {
				// Are we within a valid i.e. clickable TD node  
				if(el.tagName && el.tagName.toLowerCase() == "td") {   
									
					if(el.className.search(/cd-([0-9]{8})/) == -1 || el.className.search(/date-picker-unused|out-of-range|day-disabled|no-selection|not-selectable/) != -1) return stopEvent(e);
					
					var cellDate = el.className.match(/cd-([0-9]{8})/)[1];																					   
					o.date       = new Date(cellDate.substr(0,4),cellDate.substr(4,2)-1,cellDate.substr(6,2));										
					o.dateSet    = new Date(o.date); 
					o.noFocus    = true;								       
					o.callback("dateset", { "id":o.id, "date":o.dateSet, "dd":o.dateSet.getDate(), "mm":o.dateSet.getMonth() + 1, "yyyy":o.dateSet.getFullYear() });					  
					o.returnFormattedDate();
					o.hide();		  
						
					o.stopTimer();
					
					break;   
				// Today button pressed	     
				} else if(el.id && el.id == o.id + "-today-but") {				 
					o.date = new Date(); 
					o.updateTable();
					o.stopTimer();
					break; 
				// Day headers clicked, change the first day of the week      
				} else if(el.className.search(/date-picker-day-header/) != -1) {
					var cnt = o.showWeeks ? -1 : 0,
					elem = el;
					
					while(elem.previousSibling) {
						elem = elem.previousSibling;
						if(elem.tagName && elem.tagName.toLowerCase() == "th") cnt++;
					};
					
					o.firstDayOfWeek = (o.firstDayOfWeek + cnt) % 7;
					o.updateTableHeaders();
					break;     
				};
				try { el = el.parentNode; } catch(err) { break; };
			};
			
			return stopEvent(e);						
		};
		
		this.show = function(autoFocus) {			 
			if(this.staticPos) { return; };
			
			var elem, elemID;
			for(elemID in this.formElements) {
				elem = document.getElementById(this.id);
				if(!elem || (elem && elem.disabled)) { return; };   
			};
			
			this.noFocus = true; 
			
			// If the datepicker doesn't exist in the dom  
			if(!this.created || !document.getElementById('fd-' + this.id)) {			  
				this.created    = false;
				this.fullCreate = false;											     
				this.create();				 
				this.fullCreate = true;							    
			} else {							
				this.setDateFromInput();							       
				this.reposition();				 
			};		      
			
			this.noFocus = !!!autoFocus;			  
			
			if(this.noFocus) { 
				this.clickActivated = true;
				addEvent(document, "mousedown", this.onmousedown); 
				if(mouseWheel) {
					if (window.addEventListener && !window.devicePixelRatio) window.addEventListener('DOMMouseScroll', this.onmousewheel, false);
					else {
						addEvent(document, "mousewheel", this.onmousewheel);
						addEvent(window,   "mousewheel", this.onmousewheel);
					};
				};     
			} else {
				this.clickActivated = false;
			};    
			
			this.opacityTo = this.finalOpacity;
			this.div.style.display = "block";			
							
			/*@cc_on
			@if(@_jscript_version <= 5.7)			  
			if(!o.isIE7) {
				this.iePopUp.style.width = this.div.offsetWidth + "px";
				this.iePopUp.style.height = this.div.offsetHeight + "px";
				this.iePopUp.style.display = "block";
			};				
			@end
			@*/			       
			
			this.setNewFocus(); 
			this.fade();
			var butt = document.getElementById('fd-but-' + this.id);
			if(butt) { butt.className = butt.className.replace("dp-button-active", "") + " dp-button-active"; };						
		};
		this.hide = function() {			
			if(!this.visible || !this.created || !document.getElementById('fd-' + this.id)) return;
			
			this.kbEvent = false;
			
			o.div.className = o.div.className.replace("datepicker-focus", "");  
			
			this.stopTimer();
			this.removeOnFocusEvents();
			this.clickActivated = false;			 
								
			// Update status bar				
			if(this.statusBar) { this.updateStatus(getTitleTranslation(9)); };    
			
			this.noFocus = true;
			this.setNewFocus();
			
			if(this.staticPos) {								 
				return; 
			};

			var butt = document.getElementById('fd-but-' + this.id);
			if(butt) butt.className = butt.className.replace("dp-button-active", "");
		
			removeEvent(document, "mousedown", this.onmousedown);
			
			if(mouseWheel) {
				if (window.addEventListener && !window.devicePixelRatio) {
					try { window.removeEventListener('DOMMouseScroll', this.onmousewheel, false);} catch(err) {};				 
				} else {
					removeEvent(document, "mousewheel", this.onmousewheel);
					removeEvent(window,   "mousewheel", this.onmousewheel);
				}; 
			};
			
			/*@cc_on
			@if(@_jscript_version <= 5.7)
			if(!this.isIE7) { this.iePopUp.style.display = "none"; };
			@end
			@*/

			this.opacityTo = 0;
			this.fade();		  
		};
		this.onblur = function(e) {												  
			o.hide();
		};
		this.onfocus = function(e) {					       
			o.noFocus = false; 
			o.div.className = o.div.className.replace("datepicker-focus", "") + " datepicker-focus";												      
			o.addOnFocusEvents();									
		};   
		this.onmousewheel = function(e) {			
			e = e || document.parentWindow.event;
			var delta = 0;
			
			if (e.wheelDelta) {
				delta = e.wheelDelta/120;
				if (isOpera && window.opera.version() < 9.2) delta = -delta;
			} else if(e.detail) {
				delta = -e.detail/3;
			};			  
			
			var n = o.date.getDate(),
			    d = new Date(o.date),
			    inc = delta > 0 ? 1 : -1;			 
		       
			d.setDate(2);
			d.setMonth(d.getMonth() + inc * 1);
			d.setDate(Math.min(n, daysInMonth(d.getMonth(),d.getFullYear())));
		      
			if(o.outOfRange(d)) { return stopEvent(e); };
			
			o.date = new Date(d);
			
			o.updateTable(); 
			
			if(o.statusBar) { o.updateStatus(printFormattedDate(o.date, o.statusFormat, true)); };
			
			return stopEvent(e);						       
		};		      
		this.onkeydown = function (e) {
			o.stopTimer();
			if(!o.visible) return false;
				
			 e = e || document.parentWindow.event;
			var kc = e.keyCode ? e.keyCode : e.charCode;
				
			if( kc == 13 ) {
				// RETURN/ENTER: close & select the date
				var td = document.getElementById(o.id + "-date-picker-hover");					 
				if(!td || td.className.search(/cd-([0-9]{8})/) == -1 || td.className.search(/no-selection|out-of-range|day-disabled/) != -1) {
					return stopEvent(e);
				};
				o.dateSet = new Date(o.date);
				o.callback("dateset", o.createCbArgObj()); 
				o.returnFormattedDate();    
				o.hide();
				return stopEvent(e);
			} else if(kc == 27) {
				// ESC: close, no date selection 
				if(!o.staticPos) {
					o.hide();
					return stopEvent(e);
				};
				return true;
			} else if(kc == 32 || kc == 0) {
				// SPACE: goto today's date 
				o.date = new Date();
				o.updateTable();
				return stopEvent(e);
			} else if(kc == 9) {
				// TAB: close, no date selection & focus on btton - popup only				      
				if(!o.staticPos) {
					return stopEvent(e);
				};
				return true;				
			};    
				 
			// Internet Explorer fires the keydown event faster than the JavaScript engine can
			// update the interface. The following attempts to fix this.
				
			/*@cc_on
			@if(@_win32)				 
			if(new Date().getTime() - o.interval.getTime() < 50) { return stopEvent(e); }; 
			o.interval = new Date();				 
			@end
			@*/
			
			if(isMoz) {
				if(new Date().getTime() - o.interval.getTime() < 50) { return stopEvent(e); }; 
				o.interval = new Date();
			};				 
			
			if ((kc > 49 && kc < 56) || (kc > 97 && kc < 104)) {
				if(kc > 96) kc -= (96-48);
				kc -= 49;
				o.firstDayOfWeek = (o.firstDayOfWeek + kc) % 7;
				o.updateTableHeaders();
				return stopEvent(e);
			};

			if ( kc < 33 || kc > 40 ) return true;

			var d = new Date(o.date), tmp, cursorYYYYMM = o.date.getFullYear() + pad(o.date.getMonth()+1); 

			// HOME: Set date to first day of current month
			if(kc == 36) {
				d.setDate(1); 
			// END: Set date to last day of current month				 
			} else if(kc == 35) {
				d.setDate(daysInMonth(d.getMonth(),d.getFullYear())); 
			// PAGE UP & DOWN				   
			} else if ( kc == 33 || kc == 34) {
				var inc = (kc == 34) ? 1 : -1; 
				
				// CTRL + PAGE UP/DOWN: Moves to the same date in the previous/next year
				if(e.ctrlKey) {													       
					d.setFullYear(d.getFullYear() + inc * 1);
				// PAGE UP/DOWN: Moves to the same date in the previous/next month					    
				} else {					  
					var n = o.date.getDate();			 
		       
					d.setDate(2);
					d.setMonth(d.getMonth() + inc * 1);
					d.setDate(Math.min(n, daysInMonth(d.getMonth(),d.getFullYear())));					 
				};								    
			// LEFT ARROW				    
			} else if ( kc == 37 ) {					 
				d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() - 1);				       
			// RIGHT ARROW
			} else if ( kc == 39 || kc == 34) {					 
				d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() + 1 ); 
			// UP ARROW					
			} else if ( kc == 38 ) {					  
				d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() - 7);  
			// DOWN ARROW					
			} else if ( kc == 40 ) {					  
				d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() + 7);					 
			};

			if(o.outOfRange(d)) { return stopEvent(e); };
			o.date = d;
			
			if(o.statusBar) { 
				o.updateStatus(o.getBespokeTitle(o.date.getFullYear(),o.date.getMonth() + 1,o.date.getDate()) || printFormattedDate(o.date, o.statusFormat, true));				
			};
			
			var t = String(o.date.getFullYear()) + pad(o.date.getMonth()+1) + pad(o.date.getDate());

			if(e.ctrlKey || (kc == 33 || kc == 34) || t < o.firstDateShown || t > o.lastDateShown) {								       
				o.updateTable(); 
				/*@cc_on
				@if(@_win32)
				o.interval = new Date();			
				@end
				@*/				       
			} else {				    
				if(!o.noToday) { o.disableTodayButton(); };					
				o.removeOldFocus();
					    
				for(var i = 0, td; td = o.tds[i]; i++) {											     
					if(td.className.search("cd-" + t) == -1) {							  
						continue;
					};						 
					o.showHideButtons(o.date);
					td.id = o.id + "-date-picker-hover";						
					o.setNewFocus();
					break;
				};
			};

			return stopEvent(e);
		}; 
		this.onmouseout = function(e) {
			e = e || document.parentWindow.event;
			var p = e.toElement || e.relatedTarget;
			while (p && p != this) try { p = p.parentNode } catch(e) { p = this; };
			if (p == this) return false;
			if(o.currentTR) {
				o.currentTR.className = ""; 
				o.currentTR = null;
			};
			
			if(o.statusBar) { 
				o.updateStatus(o.getBespokeTitle(o.date.getFullYear(),o.date.getMonth() + 1,o.date.getDate()) || printFormattedDate(o.date, o.statusFormat, true));				
			};			  
		};
		this.onmouseover = function(e) {
			e = e || document.parentWindow.event;
			var el = e.target != null ? e.target : e.srcElement;
			while(el.nodeType != 1) { el = el.parentNode; }; 
				
			if(!el || ! el.tagName) { return; };			      
				
			var statusText = getTitleTranslation(9);
			switch (el.tagName.toLowerCase()) {
				case "td":					    
					if(el.className.search(/date-picker-unused|out-of-range/) != -1) {
						statusText = getTitleTranslation(9);
					} if(el.className.search(/cd-([0-9]{8})/) != -1) {											       
						o.stopTimer();
						var cellDate = el.className.match(/cd-([0-9]{8})/)[1];															  
						
						o.removeOldFocus();
						el.id = o.id+"-date-picker-hover";
						o.setNewFocus();
										       
						o.date = new Date(+cellDate.substr(0,4),+cellDate.substr(4,2)-1,+cellDate.substr(6,2));						
						if(!o.noToday) { o.disableTodayButton(); };
						
						statusText = o.getBespokeTitle(+cellDate.substr(0,4),+cellDate.substr(4,2),+cellDate.substr(6,2)) || printFormattedDate(o.date, o.statusFormat, true);						
					};
					break;
				case "th":
					if(!o.statusBar) { break; };
					if(el.className.search(/drag-enabled/) != -1) {
						statusText = getTitleTranslation(10);
					} else if(el.className.search(/date-picker-week-header/) != -1) {
						var txt = el.firstChild ? el.firstChild.nodeValue : "";
						statusText = txt.search(/^(\d+)$/) != -1 ? getTitleTranslation(7, [txt, txt < 3 && o.date.getMonth() == 11 ? getWeeksInYear(o.date.getFullYear()) + 1 : getWeeksInYear(o.date.getFullYear())]) : getTitleTranslation(9);
					};
					break;
				case "span":
					if(!o.statusBar) { break; };
					if(el.className.search(/drag-enabled/) != -1) {
						statusText = getTitleTranslation(10);
					} else if(el.className.search(/day-([0-6])/) != -1) {
						var day = el.className.match(/day-([0-6])/)[1];
						statusText = getTitleTranslation(11, [getDayTranslation(day, false)]);
					} else if(el.className.search(/prev-year/) != -1) {
						statusText = getTitleTranslation(2);
					} else if(el.className.search(/prev-month/) != -1) {
						statusText = getTitleTranslation(0);
					} else if(el.className.search(/next-year/) != -1) {
						statusText = getTitleTranslation(3);
					} else if(el.className.search(/next-month/) != -1) {
						statusText = getTitleTranslation(1);
					} else if(el.className.search(/today-but/) != -1 && el.className.search(/disabled/) == -1) {
						statusText = getTitleTranslation(12);
					};
					break;
				default:
					statusText = "";
			};
			while(el.parentNode) {
				el = el.parentNode;
				if(el.nodeType == 1 && el.tagName.toLowerCase() == "tr") {						  
					if(o.currentTR) {
						if(el == o.currentTR) break;
						o.currentTR.className = ""; 
					};						 
					el.className = "dp-row-highlight";
					o.currentTR = el;
					break;
				};
			};							  
			if(o.statusBar && statusText) { o.updateStatus(statusText); };				 
		}; 
		this.clearTimer = function() {
			o.stopTimer();
			o.timerInc      = 800;
			o.yearInc       = 0;
			o.monthInc      = 0;
			o.dayInc	= 0;
			
			removeEvent(document, "mouseup", o.clearTimer);
			if(o.mouseDownElem != null) {
				removeEvent(o.mouseDownElem, "mouseout",  o.clearTimer);
			};
			o.mouseDownElem = null;
		};    
		
		var o = this;		 
		
		this.setDateFromInput();
		
		if(this.staticPos) {			  
			this.create();					       
		} else { 
			this.createButton();					       
		};
	       
		(function() {
			var elemID, elem;
			
			for(elemID in o.formElements) {			      
				elem = document.getElementById(elemID);
				if(elem && elem.tagName && elem.tagName.search(/select|input/i) != -1) {								     
					addEvent(elem, "change", o.changeHandler);				
				};
				
				if(!elem || elem.disabled == true) {
					o.disableDatePicker();
				};			 
			};				      
		})();   
		
		
		// We have fully created the datepicker...
		this.fullCreate = true;
		
		
	};
	datePicker.prototype.addButtonEvents = function(but) {
	       function buttonEvent (e) {
			e = e || window.event;		      
			
			var inpId     = this.id.replace('fd-but-',''),
			    dpVisible = isVisible(inpId),
			    autoFocus = false,
			    kbEvent   = datePickers[inpId].kbEvent;
			    
			if(kbEvent) {
				datePickers[inpId].kbEvent = false;
				return;
			};

			if(e.type == "keydown") {
				datePickers[inpId].kbEvent = true;
				var kc = e.keyCode != null ? e.keyCode : e.charCode;
				if(kc != 13) return true; 
				if(dpVisible) {
					this.className = this.className.replace("dp-button-active", "");					  
					hideAll();
					return stopEvent(e);
				};				   
				autoFocus = true;
			} else {
				datePickers[inpId].kbEvent = false;
			};

			this.className = this.className.replace("dp-button-active", "");
			
			if(!dpVisible) {				 
				this.className += " dp-button-active";
				hideAll(inpId);							     
				showDatePicker(inpId, autoFocus);
			} else {
				hideAll();
			};
		
			return stopEvent(e);
		};
		
		but.onkeydown = buttonEvent;
		but.onclick = buttonEvent;
		
		if(!buttonTabIndex || this.bespokeTabIndex === false) {
			but.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", "-1");
			but.tabIndex = -1; 
			but.onkeydown = null; 
			removeEvent(but, "keydown", buttonEvent);
		} else {
			but.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", this.bespokeTabIndex);
			but.tabIndex = this.bespokeTabIndex;
		};			      
	};
	
	datePicker.prototype.createButton = function() {
		
		if(this.staticPos || document.getElementById("fd-but-" + this.id)) { return; };

		var inp	 = document.getElementById(this.id),
		    span	= document.createElement('span'),
		    but	 = document.createElement('a');

		but.href	= "#" + this.id;
		but.className   = "date-picker-control";
		but.title       = getTitleTranslation(5);
		but.id	  = "fd-but-" + this.id;
				
		span.appendChild(document.createTextNode(nbsp));
		but.appendChild(span);

		span = document.createElement('span');
		span.className = "fd-screen-reader";
		span.appendChild(document.createTextNode(but.title));
		but.appendChild(span);
		
		// Set the ARIA role to be "button"
		setARIARole(but, "button");		 
		
		// Set a "haspopup" ARIA property - should this not be a list if ID's????
		setARIAProperty(but, "haspopup", true);
									    
		if(this.positioned && document.getElementById(this.positioned)) {
			document.getElementById(this.positioned).appendChild(but);
		} else {
			inp.parentNode.insertBefore(but, inp.nextSibling);
		};		   
		
		this.addButtonEvents(but);

		but = null;
		
		this.callback("dombuttoncreate", {id:this.id});
	};
	datePicker.prototype.setBespokeTitles = function(titles) {		
		this.bespokeTitles = titles;	       
	}; 
	datePicker.prototype.addBespokeTitles = function(titles) {		
		for(var dt in titles) {
			this.bespokeTitles[dt] = titles[dt];
		};	      
	}; 
	datePicker.prototype.getBespokeTitle = function(y,m,d) {
		var dt, dtFull, yyyymmdd = y + String(pad(m)) + pad(d);
		
		// Try this datepickers bespoke titles
		for(dt in this.bespokeTitles) {
			dtFull = dt.replace(/^(\*\*\*\*)/, y).replace(/^(\d\d\d\d)(\*\*)/, "$1"+ pad(m));	
			if(dtFull == yyyymmdd) return this.bespokeTitles[dt];
		};
				
		// Try the generic bespoke titles
		for(dt in bespokeTitles) {
			dtFull = dt.replace(/^(\*\*\*\*)/, y).replace(/^(\d\d\d\d)(\*\*)/, "$1"+ pad(m));	
			if(dtFull == yyyymmdd) return bespokeTitles[dt];
		};
		
		return false;	     
	};
	datePicker.prototype.returnSelectedDate = function() {		
		return this.dateSet;		
	};   
	datePicker.prototype.setRangeLow = function(range) {
		this.rangeLow = (String(range).search(/^(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/) == -1) ? false : range;					      
		if(!this.inUpdate) this.setDateFromInput();		
	};
	datePicker.prototype.setRangeHigh = function(range) {
		this.rangeHigh = (String(range).search(/^(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/) == -1) ? false : range;					       
		if(!this.inUpdate) this.setDateFromInput();		
	};
	datePicker.prototype.setDisabledDays = function(dayArray) {
		if(!dayArray.length || dayArray.length != 7 || dayArray.join("").search(/^([0|1]{7})$/) == -1) {
			if(debug) {
				throw "Invalid values located when attempting to call setDisabledDays";
			};
			return false;
		};		
		this.disabledDays = dayArray;		 
		if(!this.inUpdate) this.setDateFromInput();    
	};
	datePicker.prototype.setDisabledDates = function(dateObj) {			       
		this.disabledDates  = {};	       
		this.addDisabledDates(dateObj);		
	}; 
	datePicker.prototype.setEnabledDates = function(dateObj) {			       
		this.enabledDates = {};		 
		this.addEnabledDates(dateObj);		
	};	 
	datePicker.prototype.addDisabledDates = function(dateObj) {		    
		this.enabledDates  = false; 
		this.disabledDates = this.disabledDates || {};
		
		var startD;
		for(startD in dateObj) {
			if((String(startD).search(wcDateRegExp) != -1 && dateObj[startD] == 1) || (String(startD).search(rangeRegExp) != -1 && String(dateObj[startD]).search(rangeRegExp) != -1)) {
				this.disabledDates[startD] = dateObj[startD];
			};
		};
			   
		if(!this.inUpdate) this.setDateFromInput();								
	};
	datePicker.prototype.addEnabledDates = function(dateObj) {
		this.disabledDates = false; 
		this.enabledDates  = this.enabledDates || {};
		
		var startD;
		for(startD in dateObj) {
			if((String(startD).search(wcDateRegExp) != -1 && dateObj[startD] == 1) || (String(startD).search(rangeRegExp) != -1 && String(dateObj[startD]).search(rangeRegExp) != -1)) {
				this.enabledDates[startD] = dateObj[startD];
			};
		};
			    
		if(!this.inUpdate) this.setDateFromInput();								   
	};
	datePicker.prototype.setSelectedDate = function(yyyymmdd) {					     
		if(String(yyyymmdd).search(wcDateRegExp) == -1) {
			return false;
		};  
		
		var match = yyyymmdd.match(rangeRegExp),
		    dt    = new Date(+match[2],+match[3]-1,+match[4]);
		
		if(!dt || isNaN(dt) || !this.canDateBeSelected(dt)) {
			return false;
		};
		    
		this.dateSet = new Date(dt);
		
		if(!this.inUpdate) this.updateTable();
		
		this.callback("dateset", this.createCbArgObj());
		this.returnFormattedDate();					 
	};
	datePicker.prototype.checkSelectedDate = function() {		
		if(this.dateSet && !this.canDateBeSelected(this.dateSet)) {			
			this.dateSet = null;
		};
		if(!this.inUpdate) this.updateTable();
	};
	datePicker.prototype.addOnFocusEvents = function() {			      
		if(this.kbEventsAdded || this.noFocus) {			 
			return;
		};
		
		addEvent(document, "keypress", this.onkeydown);
		addEvent(document, "mousedown", this.onmousedown);
		
		/*@cc_on
		@if(@_win32)
		removeEvent(document, "keypress", this.onkeydown);
		addEvent(document, "keydown", this.onkeydown);		 
		@end
		@*/
		if(window.devicePixelRatio) {
			removeEvent(document, "keypress", this.onkeydown);
			addEvent(document, "keydown", this.onkeydown);
		};	     
		this.noFocus = false;   
		this.kbEventsAdded = true;		
	};	 
	datePicker.prototype.removeOnFocusEvents = function() {
		
		if(!this.kbEventsAdded) { return; };
		
		removeEvent(document, "keypress",  this.onkeydown);
		removeEvent(document, "keydown",   this.onkeydown);
		removeEvent(document, "mousedown", this.onmousedown);		 
		
		this.kbEventsAdded = false;		 
	};	 
	datePicker.prototype.stopTimer = function() {
		this.timerSet = false;
		window.clearTimeout(this.timer);
	};
	datePicker.prototype.setOpacity = function(op) {
		this.div.style.opacity = op/100;
		this.div.style.filter = 'alpha(opacity=' + op + ')';
		this.opacity = op;
	};	 
	datePicker.prototype.getDates = function(y, m) {		
		var dpm = daysInMonth(m - 1, y),
		    obj = {},
		    dds = this.getGenericDates(y, m, false), 
		    eds = this.getGenericDates(y, m, true), 
		    dts = y + pad(m);		
		   
		for(var i = 1; i <= dpm; i++) {
			dt = dts + "" + pad(i);
			
			if(dds) {				
				obj[dt] = (dt in dds) ? 0 : 1;				
			} else if(eds) {
				obj[dt] = (dt in eds) ? 1 : 0;
			} else {
				obj[dt] = 1;
			};						    
		};
		
		return obj;
	}; 
	datePicker.prototype.getGenericDates = function(y, m, enabled) {
		var deDates = enabled ? this.enabledDates : this.disabledDates;		
		
		if(!deDates) {			
			return false;
		};
		
		m = pad(m);		 
		
		var obj    = {},	    
		    lower  = this.firstDateShown,
		    upper  = this.lastDateShown,			    
		    dt1, dt2, rngLower, rngUpper;  
		
		if(!upper || !lower) {
			lower = this.firstDateShown = y + pad(m) + "01";
			upper = this.lastDateShown  = y + pad(m) + pad(daysInMonth(m, y));			
		};		 
		
		for(dt in deDates) {			 
			dt1 = dt.replace(/^(\*\*\*\*)/, y).replace(/^(\d\d\d\d)(\*\*)/, "$1"+m);
			dt2 = deDates[dt];
			
			if(dt2 == 1) {						
				if(Number(dt1.substr(0,6)) >= +String(this.firstDateShown).substr(0,6)
				   && 
				   Number(dt1.substr(0,6)) <= +String(this.lastDateShown).substr(0,6)) {
					obj[dt1] = 1;							      
				};
				continue; 
			};		       
			
			// Range
			if(+String(this.firstDateShown).substr(0,6) >= Number(dt1.substr(0,6))
			   &&
			   +String(this.lastDateShown).substr(0,6) <= Number(dt2.substr(0,6))) {					      
				// Same month
				if(Number(dt1.substr(0,6)) == Number(dt2.substr(0,6))) {
					for(var i = dt1; i <= dt2; i++) {
						obj[i] = 1;												
					};
					continue;
				};

				// Different months but we only want this month
				rngLower = Number(dt1.substr(0,6)) == +String(this.firstDateShown).substr(0,6) ? dt1 : lower;
				rngUpper = Number(dt2.substr(0,6)) == +String(this.lastDateShown).substr(0,6) ? dt2 : upper;
				for(var i = +rngLower; i <= +rngUpper; i++) {
					obj[i] = 1;										    
				};
			};
		};
		return obj;
	};       
	datePicker.prototype.truePosition = function(element) {
		var pos = this.cumulativeOffset(element);
		if(isOpera) { return pos; };
		var iebody      = (document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body,
		    dsocleft    = document.all ? iebody.scrollLeft : window.pageXOffset,
		    dsoctop     = document.all ? iebody.scrollTop  : window.pageYOffset,
		    posReal     = this.realOffset(element);
		return [pos[0] - posReal[0] + dsocleft, pos[1] - posReal[1] + dsoctop];
	};
	datePicker.prototype.realOffset = function(element) {
		var t = 0, l = 0;
		do {
			t += element.scrollTop  || 0;
			l += element.scrollLeft || 0;
			element = element.parentNode;
		} while(element);
		return [l, t];
	};
	datePicker.prototype.cumulativeOffset = function(element) {
		var t = 0, l = 0;
		do {
			t += element.offsetTop  || 0;
			l += element.offsetLeft || 0;
			element = element.offsetParent;
		} while(element);
		return [l, t];
	};
	datePicker.prototype.equaliseDates = function() {
		var clearDayFound = false, tmpDate;
		for(var i = this.rangeLow; i <= this.rangeHigh; i++) {
			tmpDate = String(i);
			if(!this.disabledDays[new Date(tmpDate.substr(0,4), tmpDate.substr(6,2), tmpDate.substr(4,2)).getDay() - 1]) {
				clearDayFound = true;
				break;
			};
		};
		if(!clearDayFound) { this.disabledDays = [0,0,0,0,0,0,0] };
	};
	datePicker.prototype.outOfRange = function(tmpDate) {
		
		if(!this.rangeLow && !this.rangeHigh) { return false; };
		var level = false;
		if(!tmpDate) {
			level   = true;
			tmpDate = this.date;
		};

		var d  = pad(tmpDate.getDate()),
		    m  = pad(tmpDate.getMonth() + 1),
		    y  = tmpDate.getFullYear(),
		    dt = String(y)+String(m)+String(d);

		if(this.rangeLow && +dt < +this.rangeLow) {
			if(!level) { return true; };
			this.date = new Date(this.rangeLow.substr(0,4), this.rangeLow.substr(4,2)-1, this.rangeLow.substr(6,2), 5, 0, 0);
			return false;
		};
		if(this.rangeHigh && +dt > +this.rangeHigh) {
			if(!level) { return true; };
			this.date = new Date(this.rangeHigh.substr(0,4), this.rangeHigh.substr(4,2)-1, this.rangeHigh.substr(6,2), 5, 0, 0);
		};
		return false;
	};  
	datePicker.prototype.canDateBeSelected = function(tmpDate) {
		if(!tmpDate) return false;
							       
		var d  = pad(tmpDate.getDate()),
		    m  = pad(tmpDate.getMonth() + 1),
		    y  = tmpDate.getFullYear(),
		    dt = String(y)+String(m)+String(d),
		    dd = this.getDates(y, m),		    
		    wd = tmpDate.getDay() == 0 ? 7 : tmpDate.getDay();	       
		
		if((this.rangeLow && +dt < +this.rangeLow) || (this.rangeHigh && +dt > +this.rangeHigh) || (dd[dt] == 0) || this.disabledDays[wd-1]) {
			return false;
		};
		
		return true;
	};	
	datePicker.prototype.updateStatus = function(msg) {				
		while(this.statusBar.firstChild) { this.statusBar.removeChild(this.statusBar.firstChild); };
		
		if(msg && this.statusFormat.search(/-S|S-/) != -1 && msg.search(/([0-9]{1,2})(st|nd|rd|th)/) != -1) {		
			msg = msg.replace(/([0-9]{1,2})(st|nd|rd|th)/, "$1<sup>$2</sup>").split(/<sup>|<\/sup>/);						 
			var dc = document.createDocumentFragment();
			for(var i = 0, nd; nd = msg[i]; i++) {
				if(/^(st|nd|rd|th)$/.test(nd)) {
					var sup = document.createElement("sup");
					sup.appendChild(document.createTextNode(nd));
					dc.appendChild(sup);
				} else {
					dc.appendChild(document.createTextNode(nd));
				};
			};
			this.statusBar.appendChild(dc);			
		} else {			
			this.statusBar.appendChild(document.createTextNode(msg ? msg : getTitleTranslation(9)));						 
		};				    
	};
	datePicker.prototype.setDateFromInput = function() {
		var origDateSet = this.dateSet,
		    m = false,
		    dt, elemID, elem, elemFmt, d, y, elemVal;
		
		this.dateSet = null;
		   
		for(elemID in this.formElements) {
			elem = document.getElementById(elemID);
			
			if(!elem) {
				return;
			};
			
			elemVal = String(elem.value);
			elemFmt = this.formElements[elemID];
			dt      = false;
			
			if(!(elemVal == "")) {			
				for(var i = 0, fmt; fmt = this.formatMasks[elemID][i]; i++) {					
					dt = parseDateString(elemVal, fmt);							      
					if(dt) {										       
						break;
					};				
				}; 
			};
			
			if(dt) {
				if(elemFmt.search(new RegExp('[' + dParts + ']')) != -1) {
					//console.log("located d part " + elemFmt + " : " + dt.getDate());
					d = dt.getDate();	
				};
				if(elemFmt.search(new RegExp('[' + mParts + ']')) != -1) { 
					//console.log("located m part " + elemFmt + " : " + dt.getMonth());				       
					m = dt.getMonth();					       
				};
				if(elemFmt.search(new RegExp('[' + yParts + ']')) != -1) {
					//console.log("located y part " + elemFmt + " : " + dt.getFullYear());
					y = dt.getFullYear()	
				};			
			};					    
		};
		
		dt = false;
		
		if(d && !(m === false) && y) {					    
			if(+d > daysInMonth(+m, +y)) { 
				d  = daysInMonth(+m, +y);
				dt = false;
			} else {
				dt = new Date(+y, +m, +d);
			};
		};
	       
		if(!dt || isNaN(dt)) {			
			var newDate = new Date(y || new Date().getFullYear(), !(m === false) ? m : new Date().getMonth(), 1);
			this.date = this.cursorDate ? new Date(+this.cursorDate.substr(0,4), +this.cursorDate.substr(4,2) - 1, +this.cursorDate.substr(6,2)) : new Date(newDate.getFullYear(), newDate.getMonth(), Math.min(+d || new Date().getDate(), daysInMonth(newDate.getMonth(), newDate.getFullYear())));
			
			this.date.setHours(5);
			this.outOfRange();			 
			//this.callback("dateset", this.createCbArgObj());  
			this.updateTable();			 
			return;
		};

	
		dt.setHours(5);
		this.date = new Date(dt);			    
		this.outOfRange();		 
		
		if(dt.getTime() == this.date.getTime() && this.canDateBeSelected(this.date)) {					      
			this.dateSet = new Date(this.date);
		};
		
		//this.callback("dateset", this.createCbArgObj()); 
		if(this.fullCreate) this.updateTable();
		this.returnFormattedDate(true);
	};
	datePicker.prototype.setSelectIndex = function(elem, indx) {
		for(var opt = elem.options.length-1; opt >= 0; opt--) {
			if(elem.options[opt].value == indx) {
				elem.selectedIndex = opt;
				return;
			};
		};
	};
	datePicker.prototype.returnFormattedDate = function(noFocus) {     
		if(!this.dateSet) {				
			return;
		};
		
		var d   = pad(this.dateSet.getDate()),
		    m   = pad(this.dateSet.getMonth() + 1),
		    y   = this.dateSet.getFullYear(),
		    el  = false, 
		    elemID, elem, elemFmt, fmtDate;
		
		noFocus = !!noFocus;
		 
		for(elemID in this.formElements) {
			elem    = document.getElementById(elemID);
			
			if(!elem) return;
			
			if(!el) el = elem;
			
			elemFmt = this.formElements[elemID];
			
			fmtDate = printFormattedDate(this.dateSet, elemFmt, returnLocaleDate);		   
			if(elem.tagName.toLowerCase() == "input") {
				elem.value = fmtDate; 
			} else {  
				this.setSelectIndex(elem, fmtDate);			      
			};
		};
		
		if(this.staticPos) { 
			this.noFocus = true;
			this.updateTable(); 
			this.noFocus = false;
		};			 
			
		if(this.fullCreate) {
			if(el.type && el.type != "hidden" && !noFocus) { el.focus(); };																	     
		};	 
	};
	datePicker.prototype.disableDatePicker = function() {
		if(this.disabled) return;
		
		if(this.staticPos) {
			this.removeOnFocusEvents();
			this.removeOldFocus();
			this.noFocus = true;
			this.div.className = this.div.className.replace(/dp-disabled/, "") + " dp-disabled";  
			this.table.onmouseover = this.table.onclick = this.table.onmouseout = this.table.onmousedown = null;				      
			removeEvent(document, "mousedown", this.onmousedown);			 
			removeEvent(document, "mouseup",   this.clearTimer);		       
		} else {  
			if(this.visible) this.hide();			
			var but = document.getElementById("fd-but-" + this.id);
			if(but) {
				but.className = but.className.replace(/dp-disabled/, "") + " dp-disabled";
				// Set a "disabled" ARIA state
				setARIAProperty(but, "disabled", true);			       
				but.onkeydown = but.onclick = function() { return false; }; 
				but.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", "-1");
				but.tabIndex = -1;		
			};			 
		};	       
				
		clearTimeout(this.timer);		
		this.disabled = true;  
	}; 
	datePicker.prototype.enableDatePicker = function() {
		if(!this.disabled) return;
		
		if(this.staticPos) {
			this.removeOldFocus();
			this.noFocus = true;			
			this.updateTable();
			this.div.className = this.div.className.replace(/dp-disabled/, "");
			this.disabled = false;			 
			this.table.onmouseover = this.onmouseover;
			this.table.onmouseout  = this.onmouseout;
			this.table.onclick     = this.onclick;			 
			this.table.onmousedown = this.onmousedown;								    
		} else {			 
			var but = document.getElementById("fd-but-" + this.id);
			if(but) {
				but.className = but.className.replace(/dp-disabled/, "");
				// Reset the "disabled" ARIA state
				setARIAProperty(but, "disabled", false);
				this.addButtonEvents(but);						
			};			 
		};
		
		this.disabled = false;		
	};
	datePicker.prototype.disableTodayButton = function() {
		var today = new Date();		     
		this.butToday.className = this.butToday.className.replace("fd-disabled", "");
		if(this.outOfRange(today) || (this.date.getDate() == today.getDate() && this.date.getMonth() == today.getMonth() && this.date.getFullYear() == today.getFullYear())) {
			this.butToday.className += " fd-disabled";			  
		};
	};
	datePicker.prototype.updateTableHeaders = function() {
		var colspanTotal = this.showWeeks ? 8 : 7,
		    colOffset    = this.showWeeks ? 1 : 0,
		    d, but;

		for(var col = colOffset; col < colspanTotal; col++ ) {
			d = (this.firstDayOfWeek + (col - colOffset)) % 7;
			this.ths[col].title = getDayTranslation(d, false);

			if(col > colOffset) {
				but = this.ths[col].getElementsByTagName("span")[0];
				while(but.firstChild) { but.removeChild(but.firstChild); };
				but.appendChild(document.createTextNode(getDayTranslation(d, true)));
				but.title = this.ths[col].title;
				but.className = but.className.replace(/day-([0-6])/, "") + " day-" + d;
				but = null;
			} else {
				while(this.ths[col].firstChild) { this.ths[col].removeChild(this.ths[col].firstChild); };
				this.ths[col].appendChild(document.createTextNode(getDayTranslation(d, true)));
			};

			this.ths[col].className = this.ths[col].className.replace(/date-picker-highlight/g, "");
			if(this.highlightDays[d]) {
				this.ths[col].className += " date-picker-highlight";
			};
		};
		
		if(this.created) { this.updateTable(); }
	}; 
	datePicker.prototype.callback = function(type, args) {   
		if(!type || !(type in this.callbacks)) { 
			return false; 
		};
		
		var ret = false;		   
		for(var func = 0; func < this.callbacks[type].length; func++) {			 
			ret = this.callbacks[type][func](args || this.id);			
		};		      
		return ret;
	};      
	datePicker.prototype.showHideButtons = function(tmpDate) {
		if(!this.butPrevYear) { return; };
		
		var tdm = tmpDate.getMonth(),
		    tdy = tmpDate.getFullYear();

		if(this.outOfRange(new Date((tdy - 1), tdm, daysInMonth(+tdm, tdy-1)))) {			    
			if(this.butPrevYear.className.search(/fd-disabled/) == -1) {
				this.butPrevYear.className += " fd-disabled";
			};
			if(this.yearInc == -1) this.stopTimer();
		} else {
			this.butPrevYear.className = this.butPrevYear.className.replace(/fd-disabled/g, "");
		};		 
		
		if(this.outOfRange(new Date(tdy, (+tdm - 1), daysInMonth(+tdm-1, tdy)))) {			   
			if(this.butPrevMonth.className.search(/fd-disabled/) == -1) {
				this.butPrevMonth.className += " fd-disabled";
			};
			if(this.monthInc == -1) this.stopTimer();
		} else {
			this.butPrevMonth.className = this.butPrevMonth.className.replace(/fd-disabled/g, "");
		};
	 
		if(this.outOfRange(new Date((tdy + 1), +tdm, 1))) {			    
			if(this.butNextYear.className.search(/fd-disabled/) == -1) {
				this.butNextYear.className += " fd-disabled";
			};
			if(this.yearInc == 1) this.stopTimer();
		} else {
			this.butNextYear.className = this.butNextYear.className.replace(/fd-disabled/g, "");
		};		
		
		if(this.outOfRange(new Date(tdy, +tdm + 1, 1))) {
			if(this.butNextMonth.className.search(/fd-disabled/) == -1) {
				this.butNextMonth.className += " fd-disabled";
			};
			if(this.monthInc == 1) this.stopTimer();
		} else {
			this.butNextMonth.className = this.butNextMonth.className.replace(/fd-disabled/g, "");
		};
	};	
	var localeDefaults = {
		fullMonths:["January","February","March","April","May","June","July","August","September","October","November","December"],
		monthAbbrs:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
		fullDays:  ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
		dayAbbrs:  ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
		titles:    ["Previous month","Next month","Previous year","Next year", "Today", "Show Calendar", "wk", "Week [[%0%]] of [[%1%]]", "Week", "Select a date", "Click \u0026 Drag to move", "Display \u201C[[%0%]]\u201D first", "Go to Today\u2019s date", "Disabled date :"],
		firstDayOfWeek:0,
		imported:  false
	};	
	var joinNodeLists = function() {
		if(!arguments.length) { return []; }
		var nodeList = [];
		for (var i = 0; i < arguments.length; i++) {
			for (var j = 0, item; item = arguments[i][j]; j++) {
				nodeList[nodeList.length] = item;
			};
		};
		return nodeList;
	};
	var cleanUp = function() {
		var dp, fe;
		for(dp in datePickers) {
			for(fe in datePickers[dp].formElements) {
				if(!document.getElementById(fe)) {
					datePickers[dp].destroy();
					datePickers[dp] = null;
					delete datePickers[dp];
					break;
				}
			};
		};
	};	 
	var hideAll = function(exception) {
		var dp;
		for(dp in datePickers) {
			if(!datePickers[dp].created || (exception && exception == datePickers[dp].id)) continue;
			datePickers[dp].hide();
		};
	};
	var hideDatePicker = function(inpID) {		
		if(inpID in datePickers) {
			if(!datePickers[inpID].created || datePickers[inpID].staticPos) return;
			datePickers[inpID].hide();
		};
	};
	var showDatePicker = function(inpID, autoFocus) {		
		if(!(inpID in datePickers)) return false;   
		
		datePickers[inpID].clickActivated = !!!autoFocus;	     
		datePickers[inpID].show(autoFocus);
		return true;	
	};
	var destroy = function(e) {
		e = e || window.event;
		
		// Don't remove datepickers if it's a pagehide/pagecache event (webkit et al)
		if(e.persisted) {
			return;
		};
		
		for(dp in datePickers) {
			datePickers[dp].destroy();
			datePickers[dp] = null;
			delete datePickers[dp];
		};
		datePickers = null;
		
		removeEvent(window, 'unload', datePickerController.destroy);
	}; 
	var destroySingleDatePicker = function(id) {
		if(id && (id in datePickers)) {
			datePickers[id].destroy();
			datePickers[id] = null;
			delete datePickers[id];	
		};
	};
	var getTitleTranslation = function(num, replacements) {
		replacements = replacements || [];
		if(localeImport.titles.length > num) {
			 var txt = localeImport.titles[num];
			 if(replacements && replacements.length) {
				for(var i = 0; i < replacements.length; i++) {
					txt = txt.replace("[[%" + i + "%]]", replacements[i]);
				};
			 };
			 return txt.replace(/[[%(\d)%]]/g,"");
		};
		return "";
	};
	var getDayTranslation = function(day, abbreviation) {
		var titles = localeImport[abbreviation ? "dayAbbrs" : "fullDays"];
		return titles.length && titles.length > day ? titles[day] : "";
	};
	var getMonthTranslation = function(month, abbreviation) {
		var titles = localeImport[abbreviation ? "monthAbbrs" : "fullMonths"];
		return titles.length && titles.length > month ? titles[month] : "";
	};
	var daysInMonth = function(nMonth, nYear) {
		nMonth = (nMonth + 12) % 12;
		return (((0 == (nYear%4)) && ((0 != (nYear%100)) || (0 == (nYear%400)))) && nMonth == 1) ? 29: [31,28,31,30,31,30,31,31,30,31,30,31][nMonth];
	};
	
	var getWeeksInYear = function(Y) {
		if(Y in weeksInYearCache) {
			return weeksInYearCache[Y];
		};
		var X1, X2, NW;
		with (X1 = new Date(Y, 0, 4)) {
			setDate(getDate() - (6 + getDay()) % 7);
		};
		with (X2 = new Date(Y, 11, 28)) {
			setDate(getDate() + (7 - getDay()) % 7);
		};
		weeksInYearCache[Y] = Math.round((X2 - X1) / 604800000);
		return weeksInYearCache[Y];
	};

	var getWeekNumber = function(y,m,d) {
		var d = new Date(y, m, d, 0, 0, 0);
		var DoW = d.getDay();
		d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
		var ms = d.valueOf(); // GMT
		d.setMonth(0);
		d.setDate(4); // Thu in Week 1
		return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
	};

	var printFormattedDate = function(date, fmt, useImportedLocale) {
		if(!date || isNaN(date)) { return ""; };		
		
		var parts = fmt.split("-"),
		      str = [],
			d = date.getDate(),
			D = date.getDay(),
			m = date.getMonth(),
			y = date.getFullYear(),
		    flags = {
				"sp":" ",
				"dt":".",
				"sl":"/",
				"ds":"-",
				"cc":",",
				"d":pad(d),
				"D":useImportedLocale ? localeImport.dayAbbrs[D == 0 ? 6 : D - 1] : localeDefaults.dayAbbrs[D == 0 ? 6 : D - 1],
				"l":useImportedLocale ? localeImport.fullDays[D == 0 ? 6 : D - 1] : localeDefaults.fullDays[D == 0 ? 6 : D - 1],
				"j":d,
				"N":D == 0 ? 7 : D,
				"w":D,				
				"W":getWeekNumber(y,m,d),
				"M":useImportedLocale ? localeImport.monthAbbrs[m] : localeDefaults.monthAbbrs[m],
				"F":useImportedLocale ? localeImport.fullMonths[m] : localeDefaults.fullMonths[m],
				"m":pad(m + 1),
				"n":m + 1,
				"t":daysInMonth(m, y),
				"y":String(y).substr(2,2),				
				"Y":y,
				"S":["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			    };    
		
		for(var pt = 0, part; part = parts[pt]; pt++) {  
			str.push(!(part in flags) ? "" : flags[part]);
		};
		
		return str.join("");
	};
	var parseDateString = function(str, fmt) {
		var d     = false,
		    m     = false,
		    y     = false,
		    now   = new Date(),
		    parts = fmt.replace(/-sp(-sp)+/g, "-sp").split("-"),
		    divds = { "dt":".","sl":"/","ds":"-","cc":"," },
		    str   = "" + str;		    
	    
		loopLabel:
		for(var pt = 0, part; part = parts[pt]; pt++) {			
			if(str.length == 0) { return false; };
			      
			switch(part) {
				// Dividers - be easy on them all i.e. accept them all when parsing...				
				case "sp":
				case "dt":
				case "sl":
				case "ds":
				case "cc":       
						str = str.replace(/^(\s|\.|\/|,|-){1,}/, "");				     
						break;
				// DAY
				case "d": // Day of the month, 2 digits with leading zeros (01 - 31)
				case "j": // Day of the month without leading zeros (1 - 31)  
					  // Accept both when parsing							  
						if(str.search(/^(3[01]|[12][0-9]|0?[1-9])/) != -1) {
							d = +str.match(/^(3[01]|[12][0-9]|0?[1-9])/)[0];
							str = str.substr(str.match(/^(3[01]|[12][0-9]|0?[1-9])/)[0].length);							
							break;
						} else {							
							return "";
						};
				case "D": // A textual representation of a day, three letters (Mon - Sun)
				case "l": // A full textual representation of the day of the week (Monday - Sunday)
					  // Accept English & imported locales and both modifiers						  
						l = localeDefaults.fullDays.concat(localeDefaults.dayAbbrs);						  
						if(localeImport.imported) {
							l = l.concat(localeImport.fullDays).concat(localeImport.dayAbbrs);
						}; 
						
						for(var i = 0; i < l.length; i++) {
							if(new RegExp("^" + l[i], "i").test(str)) {								
								str = str.substr(l[i].length);
								continue loopLabel;
							};
						};
						
						break;				  
				case "N": // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) 1 (for Monday) through 7 (for Sunday)
				case "w": // Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)
						if(str.search(part == "N" ? /^([1-7])/ : /^([0-6])/) != -1) {
							str = str.substr(1);
							
						};
						break;
				case "S": // English ordinal suffix for the day of the month, 2 characters: st, nd, rd or th
						if(str.search(/^(st|nd|rd|th)/i) != -1) {
							str = str.substr(2);							
						};
						break;				
				// WEEK
				case "W": // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0): 1 - 53
						if(str.search(/^([1-9]|[1234[0-9]|5[0-3])/) != -1) {
							str = str.substr(str.match(/^([1-9]|[1234[0-9]|5[0-3])/)[0].length);							
						};
						break;
				// MONTH
				case "M": // A short textual representation of a month, three letters
				case "F": // A full textual representation of a month, such as January or March
					  // Accept English & imported locales and both modifiers						    
						l = localeDefaults.fullMonths.concat(localeDefaults.monthAbbrs);
						if(localeImport.imported) {
							l = l.concat(localeImport.fullMonths).concat(localeImport.monthAbbrs);
						};
						for(var i = 0; i < l.length; i++) {							
							if(str.search(new RegExp("^" + l[i],"i")) != -1) {
								str = str.substr(l[i].length);
								m = ((i + 12) % 12);								 
								continue loopLabel;
							};
						};
						return "";
				case "m": // Numeric representation of a month, with leading zeros
				case "n": // Numeric representation of a month, without leading zeros
					  // Accept either when parsing
						l = /^(1[012]|0?[1-9])/;
						if(str.search(l) != -1) {
							m = +str.match(l)[0] - 1;
							str = str.substr(str.match(l)[0].length);
							break;
						} else {							
							return "";
						};
				case "t": // Number of days in the given month: 28 through 31
						if(str.search(/2[89]|3[01]/) != -1) {
							str = str.substr(2);
							break;
						};
						break;
				// YEAR
				case "Y": // A full numeric representation of a year, 4 digits
						if(str.search(/^(\d{4})/) != -1) {
							y = str.substr(0,4);
							str = str.substr(4);
							break;
						} else {							
							return "";
						};
				case "y": // A two digit representation of a year - be easy on four figure dates though						
						if(str.search(/^(\d{4})/) != -1) {
							y = str.substr(0,4);
							str = str.substr(4);
							break;
						} else if(str.search(/^(0[0-9]|[1-9][0-9])/) != -1) {
							y = str.substr(0,2);
							y = +y < 50 ? '20' + "" + String(y) : '19' + "" + String(y);
							str = str.substr(2);
							break;
						} else return "";
				       
				default:
						return "";
			};
		};   
		
		if(!(str == "") || (d === false && m === false && y === false)) {
			return false;
		};		
		
		m = m === false ? 11		  : m;
		y = y === false ? now.getFullYear()   : y;
		d = d === false ? daysInMonth(+m, +y) : d;
		
		if(d > daysInMonth(+m, +y)) {
			return false;
		};
		
		var tmpDate = new Date(y,m,d);
		
		return !tmpDate || isNaN(tmpDate) ? false : tmpDate;
	};	
	var findLabelForElement = function(element) {
		var label;
		if(element.parentNode && element.parentNode.tagName.toLowerCase() == "label") lebel = element.parentNode;
		else {
			var labelList = document.getElementsByTagName('label');
			// loop through label array attempting to match each 'for' attribute to the id of the current element
			for(var lbl = 0; lbl < labelList.length; lbl++) {
				// Internet Explorer requires the htmlFor test
				if((labelList[lbl]['htmlFor'] && labelList[lbl]['htmlFor'] == element.id) || (labelList[lbl].getAttribute('for') == element.id)) {
					label = labelList[lbl];
					break;
				};
			};
		};
		
		if(label && !label.id) { label.id = element.id + "_label"; };
		return label;	 
	};  
	var updateLanguage = function() {
		if(typeof(window.fdLocale) == "object" ) {			 
			localeImport = {
				titles	  : fdLocale.titles,
				fullMonths      : fdLocale.fullMonths,
				monthAbbrs      : fdLocale.monthAbbrs,
				fullDays	: fdLocale.fullDays,
				dayAbbrs	: fdLocale.dayAbbrs,
				firstDayOfWeek  : ("firstDayOfWeek" in fdLocale) ? fdLocale.firstDayOfWeek : 0,
				imported	: true
			};					       
		} else if(!localeImport) {			
			localeImport = localeDefaults;
		};    
	};
	var loadLanguage = function() {
		updateLanguage();
		for(dp in datePickers) {
			if(!datePickers[dp].created) continue;
			datePickers[dp].updateTable();
		};   
	};
	var checkElem = function(elem) {			
		return !(!elem || !elem.tagName || !((elem.tagName.toLowerCase() == "input" && (elem.type == "text" || elem.type == "hidden")) || elem.tagName.toLowerCase() == "select"));		
	};
	var addDatePicker = function(options) {  
		
		updateLanguage();
		
		if(!options.formElements) {
			if(debug) throw "No form elements stipulated within initialisation parameters";
			return;
		};
	       
		options.id = (options.id && (options.id in options.formElements)) ? options.id : "";
		options.formatMasks = {};
		 
		var testParts  = [dParts,mParts,yParts],
		    partsFound = [0,0,0],
		    tmpPartsFound,
		    matchedPart,
		    newParts,
		    indParts,
		    fmt,
		    fmtBag,
		    fmtParts,
		    newFormats,
		    myMin,
		    myMax;	       
		
		for(var elemID in options.formElements) {		
			elem = document.getElementById(elemID);
			
			if(!checkElem(elem)) {
				if(debug) throw "The element with and id of '" + elemID + "' is of the wrong type or does not exist within the DOM";
				return false;
			};
			
			if(!options.id) options.id = elemID;
			
			fmt	     = options.formElements[elemID];
			
			if(!(fmt.match(validFmtRegExp))) {
				if(debug) throw "The element with and id of '" + elemID + "' has the following incorrect date format assigned to it: " + fmt;
				return false;
			};
			
			fmtBag	  = [fmt];
			
			if(options.dateFormats && (elemID in options.dateFormats) && options.dateFormats[elemID].length) {
				newFormats = [];
				
				for(var f = 0, bDft; bDft = options.dateFormats[elemID][f]; f++) {				       
					if(!(bDft.match(validFmtRegExp))) {
						if(debug) throw "The element with and id of '" + elemID + "' has the following incorrect date format assigned to it within the dateFormats parameter: " + bDft;
						return false;
					};  
					
					newFormats.push(bDft); 
				};
				
				fmtBag = fmtBag.concat(newFormats);  
			};
			 
			tmpPartsFound   = [0,0,0];			
			
			for(var i = 0, testPart; testPart = testParts[i]; i++) {				
				if(fmt.search(new RegExp('('+testPart+')')) != -1) {
					partsFound[i] = tmpPartsFound[i] = 1;
					
					// Create the date format strings to check against later for text input elements
					if(elem.tagName.toLowerCase() == "input") {
						matchedPart = fmt.match(new RegExp('('+testPart+')'))[0];
						newParts    = String(matchedPart + "|" + testPart.replace(new RegExp("(" + matchedPart + ")"), "")).replace("||", "|");
						indParts    = newParts.split("|");
						newFormats  = [];
					
						for(var z = 0, bFmt; bFmt = fmtBag[z]; z++) {
							for(var x = 0, indPart; indPart = indParts[x]; x++) {
								if(indPart == matchedPart) continue;
								newFormats.push(bFmt.replace(new RegExp('(' + testPart + ')(-|$)', 'g'), indPart + "-").replace(/-$/, ""));
							};
						};
					
						fmtBag = fmtBag.concat(newFormats);
					};
				};
			};			
			
			options.formatMasks[elemID] = fmtBag.concat();
			
			if(elem.tagName.toLowerCase() == "select") {
				myMin = myMax = 0;
			
				// If we have a selectList, then try to parse the higher and lower limits 
				var selOptions = elem.options;
				
				// Check the yyyymmdd 
				if(tmpPartsFound[0] && tmpPartsFound[1] && tmpPartsFound[2]) { 
					var yyyymmdd, 
					    cursorDate = false;
					
					// Remove the disabledDates parameter
					if("disabledDates" in options) {
						delete(options.disabledDates);
					};
					
					// Dynamically calculate the available "enabled" dates
					options.enabledDates = {};
					    
					for(i = 0; i < selOptions.length; i++) {
						for(var f = 0, fmt; fmt = fmtBag[f]; f++) {
							dt = parseDateString(selOptions[i].value, fmt /*options.formElements[elemID]*/);
							if(dt) {
								yyyymmdd = dt.getFullYear() + "" + pad(dt.getMonth()+1) + "" + pad(dt.getDate());
							
								if(!cursorDate) cursorDate = yyyymmdd;
							
								options.enabledDates[yyyymmdd] = 1;
							
								if(!myMin || Number(yyyymmdd) < myMin) {
									myMin = yyyymmdd;
								}; 
							
								if(!myMax || Number(yyyymmdd) > myMax) {
									myMax = yyyymmdd;
								};
								
								break;
							};						
						};					
					};  
			
					// Automatically set cursor to first available date (if no bespoke cursorDate was set);					
					if(!options.cursorDate && cursorDate) options.cursorDate = cursorDate;
					  
				} else if(tmpPartsFound[1] && tmpPartsFound[2]) {
					var yyyymm;
					    
					for(i = 0; i < selOptions.length; i++) {
						for(var f = 0, fmt; fmt = fmtBag[f]; f++) {						
							dt = parseDateString(selOptions[i].value, fmt /*options.formElements[elemID]*/);
							if(dt) {
								yyyymm = dt.getFullYear() + "" + pad(dt.getMonth()+1);
							
								if(!myMin || Number(yyyymm) < myMin) {
									myMin = yyyymm;
								}; 
							
								if(!myMax || Number(yyyymm) > myMax) {
									myMax = yyyymm;
								};   
								
								break;					     
							}; 
						};				       
					};					   
					
					// Round the min & max values to be used as rangeLow & rangeHigh
					myMin += "" + "01";
					myMax += "" + daysInMonth(+myMax.substr(4,2) - 1, +myMax.substr(0,4));
										
				} else if(tmpPartsFound[2]) {
					var yyyy;
					    
					for(i = 0; i < selOptions.length; i++) {
						for(var f = 0, fmt; fmt = fmtBag[f]; f++) { 
							dt = parseDateString(selOptions[i].value, fmt /*options.formElements[elemID]*/);
							if(dt) {
								yyyy = dt.getFullYear();							
								if(!myMin || Number(yyyy) < myMin) {
									myMin = yyyy;
								}; 
							
								if(!myMax || Number(yyyy) > myMax) {
									myMax = yyyy;
								}; 
							       
								break;
							};					       
						};			   
					};  
					
					// Round the min & max values to be used as rangeLow & rangeHigh
					myMin += "0101";
					myMax += "1231";												    
				};
				
				if(myMin && (!options.rangeLow  || (+options.rangeLow < +myMin)))  options.rangeLow = myMin;
				if(myMax && (!options.rangeHigh || (+options.rangeHigh > +myMin))) options.rangeHigh = myMax;				
			};
		};
		
		if(!(partsFound[0] && partsFound[1] && partsFound[2])) {
			if(debug) throw "Could not find all of the required date parts for element: " + elem.id;
			return false;
		}; 
		
		var opts = {
			formElements:options.formElements,
			// Form element id
			id:options.id,
			// Format masks 
			formatMasks:options.formatMasks,
			// Non popup datepicker required
			staticPos:!!(options.staticPos),
			// Position static datepicker or popup datepicker's button
			positioned:options.positioned && document.getElementById(options.positioned) ? options.positioned : "",
			// Ranges stipulated in YYYYMMDD format       
			rangeLow:options.rangeLow && String(options.rangeLow).search(rangeRegExp) != -1 ? options.rangeLow : "",
			rangeHigh:options.rangeHigh && String(options.rangeHigh).search(rangeRegExp) != -1 ? options.rangeHigh : "",
			// Status bar format
			statusFormat:options.statusFormat && String(options.statusFormat).search(validFmtRegExp) != -1 ? options.statusFormat : "",										 
			// No fade in/out effect
			noFadeEffect:!!(options.staticPos) ? true : !!(options.noFadeEffect),
			// No drag functionality
			dragDisabled:nodrag || !!(options.staticPos) ? true : !!(options.dragDisabled),
			// Bespoke tabindex for this datePicker (or it's activation button)
			bespokeTabIndex:options.bespokeTabindex && typeof options.bespokeTabindex == 'number' ? parseInt(options.bespokeTabindex, 10) : 0,
			// Bespoke titles
			bespokeTitles:options.bespokeTitles || {},
			// Final opacity 
			finalOpacity:options.finalOpacity && typeof options.finalOpacity == 'number' && (options.finalOpacity > 20 && options.finalOpacity <= 100) ? parseInt(+options.finalOpacity, 10) : (!!(options.staticPos) ? 100 : finalOpacity),
			// Do we hide the form elements on datepicker creation
			hideInput:!!(options.hideInput),
			// Do we hide the "today" button
			noToday:!!(options.noTodayButton),
			// Do we show week numbers
			showWeeks:!!(options.showWeeks),
			// Do we fill the entire grid with dates						  
			fillGrid:!!(options.fillGrid),
			// Do we constrain selection of dates outside the current month
			constrainSelection:"constrainSelection" in options ? !!(options.constrainSelection) : true,
			// The date to set the initial cursor to
			cursorDate:options.cursorDate && String(options.cursorDate).search(rangeRegExp) != -1 ? options.cursorDate : "",			
			// Locate label to set the ARIA labelled-by property
			labelledBy:findLabelForElement(elem),
			// Have we been passed a describedBy to set the ARIA decribed-by property...
			describedBy:(options.describedBy && document.getElementById(options.describedBy)) ? options.describedBy : describedBy && document.getElementById(describedBy) ? describedBy : "",
			// Callback functions
			callbacks:options.callbackFunctions ? options.callbackFunctions : {},
			// Days of the week to highlight (normally the weekend)
			highlightDays:options.highlightDays && options.highlightDays.length && options.highlightDays.length == 7 ? options.highlightDays : [0,0,0,0,0,1,1],
			// Days of the week to disable
			disabledDays:options.disabledDays && options.disabledDays.length && options.disabledDays.length == 7 ? options.disabledDays : [0,0,0,0,0,0,0]								   
		};  
		
		if(options.disabledDates) {
			if(options.enabledDates) delete(options.enabledDates);
			opts.disabledDates = {};
			var startD;
			for(startD in options.disabledDates) {				
				if((String(startD).search(wcDateRegExp) != -1 && options.disabledDates[startD] == 1) || (String(startD).search(rangeRegExp) != -1 && String(options.disabledDates[startD]).search(rangeRegExp) != -1)) {
					opts.disabledDates[startD] = options.disabledDates[startD];					   
				};
			};
		} else if(options.enabledDates) {			
			var startD;
			opts.enabledDates = {};
			for(startD in options.enabledDates) {				
				if((String(startD).search(wcDateRegExp) != -1 && options.enabledDates[startD] == 1) || (String(startD).search(rangeRegExp) != -1 && String(options.enabledDates[startD]).search(rangeRegExp) != -1)) {
					opts.enabledDates[startD] = options.enabledDates[startD];									    
				};
			};
		};		
		
		datePickers[options.id] = new datePicker(opts);	       
		datePickers[options.id].callback("create", datePickers[options.id].createCbArgObj());		  
	};

	// Used by the button to dictate whether to open or close the datePicker
	var isVisible = function(id) {
		return (!id || !(id in datePickers)) ? false : datePickers[id].visible;
	};  
	
	addEvent(window, 'unload', destroy);
	
	return {
		// General event functions...
		addEvent:	       function(obj, type, fn) { return addEvent(obj, type, fn); },
		removeEvent:	    function(obj, type, fn) { return removeEvent(obj, type, fn); },
		stopEvent:	      function(e) { return stopEvent(e); },
		// Show a single popup datepicker
		show:		   function(inpID) { return showDatePicker(inpID, false); },
		// Hide a popup datepicker
		hide:		   function(inpID) { return hideDatePicker(inpID); },		
		// Create a new datepicker
		createDatePicker:       function(options) { addDatePicker(options); },
		// Destroy a datepicker (remove events and DOM nodes)	       
		destroyDatePicker:      function(inpID) { destroySingleDatePicker(inpID); },
		// Check datePicker form elements exist, if not, destroy the datepicker
		cleanUp:		function() { cleanUp(); },		    
		// Pretty print a date object according to the format passed in	       
		printFormattedDate:     function(dt, fmt, useImportedLocale) { return printFormattedDate(dt, fmt, useImportedLocale); },
		// Update the internal date using the form element value
		setDateFromInput:       function(inpID) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setDateFromInput(); },
		// Set low and high date ranges
		setRangeLow:	    function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) { return false; }; datePickers[inpID].setRangeLow(yyyymmdd); },
		setRangeHigh:	   function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) { return false; }; datePickers[inpID].setRangeHigh(yyyymmdd); },
		// Set bespoke titles for a datepicker instance
		setBespokeTitles:       function(inpID, titles) {if(!inpID || !(inpID in datePickers)) { return false; }; datePickers[inpID].setBespokeTitles(titles); },
		// Add bespoke titles for a datepicker instance
		addBespokeTitles:       function(inpID, titles) {if(!inpID || !(inpID in datePickers)) { return false; }; datePickers[inpID].addBespokeTitles(titles); },		
		// Attempt to parse a valid date from a date string using the passed in format
		parseDateString:	function(str, format) { return parseDateString(str, format); },
		// Change global configuration parameters
		setGlobalVars:	  function(json) { affectJSON(json); },
		setSelectedDate:	function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) { return false; }; datePickers[inpID].setSelectedDate(yyyymmdd); },
		// Is the date valid for selection i.e. not outside ranges etc
		dateValidForSelection:  function(inpID, dt) { if(!inpID || !(inpID in datePickers)) return false; return datePickers[inpID].canDateBeSelected(dt); },
		// Add disabled and enabled dates
		addDisabledDates:       function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].addDisabledDates(dts); },
		setDisabledDates:       function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setDisabledDates(dts); },
		addEnabledDates:	function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].addEnabledDates(dts); },
		setEnabledDates:	function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setEnabledDates(dts); },
		// Disable and enable the datepicker
		disable:		function(inpID) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].disableDatePicker(); },
		enable:		 function(inpID) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].enableDatePicker(); },
		// Set the cursor date
		setCursorDate:	  function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setCursorDate(yyyymmdd); },
		// Whats the currently selected date
		getSelectedDate:	function(inpID) { return (!inpID || !(inpID in datePickers)) ? false : datePickers[inpID].returnSelectedDate(); },
		// Attempt to update the language (causes a redraw of all datepickers on the page)
		loadLanguage:	   function() { loadLanguage(); },
		// Set the debug level i.e. throw errors or fail silently
		setDebug:	       function(dbg) { debug = !!(dbg); }							   
	}; 
})();
