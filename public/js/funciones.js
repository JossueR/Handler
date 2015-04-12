var $j = jQuery.noConflict();
var disabled_form = Array();

function UP(txt){
	var input = $(txt);
	
	
	if(isNaN(input.value)){
		input.value = 1;
	}
	
	input.value ++;
} 

function DOWN(txt){
	var input = $(txt);
	
	
	if(isNaN(input.value) || input.value <= 1 ){
		input.value = 1;
	}else{
		input.value--;
	}
} 




var AjaxLoading = Class.create();
AjaxLoading.prototype ={
	//cuanta la cantidad de llamados asicnronicos que no han terminado
	async_calls:null,
	
	//almacena el elemento que muestra el icono loading
	div:null,
	
	//constructor
	initialize: function(){
		this.async_calls=0;
		
	},
	
	//agrega un llamado, muestra el icono de loading, si no estaba
	addRequest: function(){
		if(this.div != null){
			this.async_calls++;
			
			var display = this.div.getStyle('display');
			
			this.div.update(this.async_calls);
			
			if(display == "none"){
				 this.div.setStyle({"display":"block"});
			}
		}
	},
	
	//remueve llamado, si no hay llamados => oculta icono loading
	endRequest: function(){
		if(this.div != null){
			this.async_calls--;
			
			var display = this.div.getStyle('display');
			
			this.div.update(this.async_calls);
			
			if(this.async_calls <= 0){
				 this.async_calls = 0;
				 this.div.setStyle({"display":"none"});
			}
		}
	},
	
	setElement: function(d){
		this.div = $(d);
		this.async_calls=0;
	}
}
var loadingIcon = new AjaxLoading();


function maxHeight(){
	var defaultMax = 200;
	maxHeightX(defaultMax);
}

function maxHeightX(defaultMax){
	var defaultUnit = "px";
	elementos = $$('div.lista');
	
	elementos.each(function(domElement) {
		 actualHeight = parseFloat($(domElement).getHeight());

		 if(actualHeight > defaultMax){
			 defaultMax += defaultUnit;
			 $(domElement).setStyle({'height': defaultMax,
				 					 'overflow': 'auto'});
		 }
		});
}

function dom_update(script, params, dest)
{
	params = params.toQueryParams();
	loadingIcon.addRequest();
	new Ajax.Updater({ success: dest }, script, {parameters: params, 
												evalScripts: true,
												on408: function(t){document.location.reload(true);},
												on401: function(t){
													if(t.responseText.isJSON()){
														var json = t.responseText.evalJSON();
														jsonAction(json);
													}
												
												},
												onComplete: function(t){
																loadingIcon.endRequest();
															}
												}
												
					);
}

function dom_update_refresh(script, params, dest, secs)
{
	params = params.toQueryParams();
	loadingIcon.addRequest();
	new Ajax.PeriodicalUpdater({ success: dest }, script, {parameters: params, 
												evalScripts: true,
												frequency: secs,
												on408: function(t){document.location.reload(true);},
												on401: function(t){
													if(t.responseText.isJSON()){
														var json = t.responseText.evalJSON();
														jsonAction(json);
													}
												
												},
												onComplete: function(t){
																loadingIcon.endRequest();
															}
												}
												
					);
}

function dom_insert(script, params, dest, pos, sufix)
{
	params = params.toQueryParams();
	if(pos == null){
		pos = 'top';
	}
	loadingIcon.addRequest();
	new Ajax.Updater({ success: dest }, script, {parameters: params, 
												evalScripts: true,
												insertion: pos,
												on408: function(t){document.location.reload(true);},
												on401: function(t){
													if(t.responseText.isJSON()){
														var json = t.responseText.evalJSON();
														jsonAction(json);
													}
												
												},
												onComplete: function(t){
																loadingIcon.endRequest();
																
																if(sufix != null){
																	dest.descendants().each(function(el){
																		if(el.id != ''){
																			el.id = el.id + sufix
																		}
																	}, this);
																}
																
																
															}
												}
												
					);
}

function dom_confirm(script, params, dest, msgConfirm)
{
	if(confirm(msgConfirm)){
		dom_update(script, params, dest);
	}
}

function send_form(form_name, result, do_action){
	send_form(form_name, result, do_action, '');
}

//envia datos de un formulario con ajax
function send_form(form_name, result, do_action, params)
{
	
	
	//borra los errors anteriores
	if($('form_errors') != null && $('form_errors') != undefined){
		$('form_errors').update();
	}

	//obtiene el formulario
	var form = $(form_name); 
	
	//verifica que no este desabilitado
	if(form != null && disabled_form[form.name] != 1){
		
		//desailita formulario
		disabled_form[form.name] = 1;
		
		params +="&do="+do_action;
		params = params.toQueryParams();
		
		//cuenta envio
		loadingIcon.addRequest();
		
		$(form_name).request({
			parameters: params,
			on408: function(t){document.location.reload(true);},
			onSuccess: function(t) {
				
						if(t.responseText.isJSON()){
							var json = t.responseText.evalJSON();
							jsonAction(json);
						}else{
							
							//no resetea los formularios con clase: noreset
							if(!$(form_name).hasClassName('noreset')){
								$(form_name).reset();
							}
							
							$(result).update(t.responseText);
						}
						
						//habilita form
						disabled_form[form.name] = 0;
						
						//termina request
						loadingIcon.endRequest();
					},
			onFailure: function(t) {			
							disabled_form[form.name] = 0;
						}	
			});
		
	}
}

function send_confirm(form_name, result, do_action, msgConfirm)
{
	if(confirm(msgConfirm)){
		send_form(form_name, result, do_action);
	}
}

var PushClicked = Class.create();
PushClicked.prototype = {
	
	//css class name to by used
	clicked_class : null,
	
	//active link (DOM object)
	clicked_link : null,
	
	//constructor
	initialize: function(class_name){
		this.clicked_class = class_name;
	},
	
	_push: function(link_obj){
		
		if(this.clicked_link != null){
			this.clicked_link.removeClassName(this.clicked_class);
		}
		
		$(link_obj).addClassName(this.clicked_class);
		//link_obj.setAttribute("class", this.clicked_class);
		
		this.clicked_link = link_obj;
	}
}

function jsonAction(json){
	if(json.errors != undefined && json.errors != ""){
		
		if($('form_errors') != null){
			$('form_errors').update();
			json.errors.each(function(msg){ $('form_errors').insert('<div class="error-msg">'+msg+'</div>' ); });
		}else{
			html = "";
			for (var i=0; i<json.errors.length; i++) {
				html+= json.errors[i] + "\n";
			}
			alert(html);
		}
	}
}

var todas_tablas = Array();
function showPagination(totalRows,dest,accion,params, maxPerPage, controls) {
	//console.log("pag_" + dest );
	//console.log($("pag_" + dest) );
	if($("pag_" + dest) == null){
		//console.log("new");
		todas_tablas[dest] = new Pagination(totalRows,dest,accion,params, maxPerPage, controls);
	}else{
		//console.log("update");
		todas_tablas[dest]._update(totalRows,params,maxPerPage);
	}
}

var Pagination = Class.create();
Pagination.prototype = {
	

	orderField: null,
	asc: 'A',
	dest : null,
	pages : null,
	pageActual: null,
	action : null,
	params : null,
	totalRows : null,
	maxPerPage : null,
	filter: null,
	filter_adv: null,
	group: null,
	order: null,
	sort: null,
	controls: null,

	//constructor
	initialize: function(totalRows, dest, action, params, maxPerPage, controls){

		this.totalRows = totalRows;
		this.maxPerPage = maxPerPage;
		this.dest = dest;
		this.action = action;
		this.params = params;
		this.controls = controls;
		
		this.pages = Math.ceil(this.totalRows / this.maxPerPage);
		this.pageActual = 0;
		
		//si no se envio el div del contenedor, solo la tabla
		if($(this.dest) == null && $('tabla_' + this.dest) != null){
			div = new Element('div', { 'id': this.dest, 'class': 'lista table-responsive' });
			$('tabla_' + this.dest).wrap(div);
		}
		
		if(this.controls.PAGING){
			this._show();
		}
		
		if(this.controls.ORDER){
			this.order = new Order(this.dest, this);
		}
		
		if(this.controls.SORT_FIELD){
			this.sort = new SortFields(this.dest, this);
		}
		
		if(this.controls.GROUP){
			this.group = new Group(this.dest, this);
		}
		
		if(this.controls.FILTER){
			this.filter = new Filter(this.dest, this.action, this.params, this);
		}
		
		if(this.controls.FILTER_ADV){
			this.filter_adv = new FilterAdv(this.dest, this.action, this.params, this);
		}
		
	},
	
	_update : function(totalRows, params, maxPerPage){
		
		if(this.controls.ORDER){
			this.order._make();
		}
		
		if(this.controls.GROUP){
			this.group._make();
		}
		
		if(this.controls.FILTER_ADV){
			this.filter_adv._make();
		}
		
		if(this.totalRows != totalRows || this.params != params || this.maxPerPage != maxPerPage ){
			this.totalRows = totalRows;
			this.maxPerPage = maxPerPage;
			this.params = params;
			
			this.pages = Math.ceil(this.totalRows / this.maxPerPage);
			this.pageActual = 0;
			
			if(this.controls.PAGING){
				$('pag_'+this.dest).remove();
				this._show();
			}
			
			if(this.controls.FILTER){
				this.filter._update(params);
			}

		}

		
	},
	
	_show : function(){
		var html='<table cellspacing="0" cellpadding="0" border="0" id="pag_'+this.dest+'">';
		html+='<tbody>';
		html+='<tr>';
		html+='<td>';
		
		//primera pagina
		html+='<a class="page-far-left" href="javascript: void(0)" onclick="todas_tablas[\''+this.dest+'\']._first()"><i class="fa fa-chevron-left fa-lg"></i></a>';
		
		//anterior
		html+='<a class="page-left" href="javascript: void(0)" onclick="todas_tablas[\''+this.dest+'\']._back()"><i class="fa fa-chevron-circle-left fa-lg"></i></a></td>';
		
		html+='<td>Page <input class="paging_page" id="'+this.dest+'_actual" type="text" value="'+(this.pageActual+1)+'" /> / '+ this.pages + '</td>';
		
		//siguiente  
		html+='<td><a class="page-right" href="javascript: void(0)" onclick="todas_tablas[\''+this.dest+'\']._next()"><i class="fa fa-chevron-circle-right fa-lg"></i></a>';
		
		//ultima pagina
		html+='<a class="page-far-right" href="javascript: void(0)" onclick="todas_tablas[\''+this.dest+'\']._last()"><i class="fa fa-chevron-right fa-lg"></i></a>';
		
		html+='</td>';
		html+='<td>Paginar:<input type="checkbox" onclick="todas_tablas[\''+this.dest+'\']._tooglePagination(this)" checked /></td>';
		html+='<td><a class="excel_link"  href="javascript: void(0)" onclick="todas_tablas[\''+this.dest+'\']._goExcel()" ><i class="fa fa-file-text fa-lg"></i> Excel</a></td>';
		
		html+='</tr>';
		html+='</tbody>';
		html+='</table>';
		

		
		$(this.dest).insert({after: html});
		$(this.dest + '_actual').observe('keypress', this._goPage.bind(this));
	},
	
	_tooglePagination: function(element){
		if(this.pages < 60 || this.pageActual < 0 || confirm('El resultado tiene muchos datos, realmente desea hacer esto?')){
			this.pageActual=(this.pageActual >= 0)? -1 : 0;
			this._go();
		}
		
		$(element).checked =(this.pageActual >= 0)? true : false;
	
	},
	
	_first: function(){
		
			this.pageActual=0;
	
			this._go();
		
	},
	
	_back: function(){
		if(this.pageActual > 0){
			this.pageActual--;

			this._go();
		}	
	},
	
	_next: function(){
		if(this.pageActual < this.pages - 1){
			this.pageActual++;

			this._go();
		}
	},
	
	
	_last: function(){
		if(this.pageActual != this.pages - 1){
			this.pageActual=this.pages - 1;
	
			this._go();
		}
	},
	
	_getParams: function(){
		var allParams = "";
		allParams += this.params+'&PAGE=' + this.pageActual;
		
		if(this.controls.FILTER || this.controls.FILTER_ADV){
			
			
			if(this.controls.FILTER_ADV){
				allParams +=  this.filter_adv._getString(this.filter._getFiltersString());
			}else{
				allParams +=  this.filter._getFiltersString();
			}
			
			allParams +=  this.filter._getFiltersKeysString();
		}
		
		if(this.controls.GROUP){
			allParams +=  this.group._getGroupsString() ;
		}
		if(this.controls.ORDER){
			allParams +=  this.order._getOrderString();
		}
		
		if(this.controls.SORT_FIELD){
			allParams +=  this.sort._getSortsString();
		}
		
		return allParams;
	},
	
	_go: function(){
		pagina = this.pageActual+1;
		dom_update(this.action,this._getParams(), this.dest);
		$(this.dest+'_actual').value = pagina;
	},
	
	_goPage: function(event){
		
		if(event.keyCode == Event.KEY_RETURN){
			pagina = parseInt($F(event.element()));
			
			if(pagina > 0 && pagina < this.pages){
				
				this.pageActual = pagina-1;
				this._go();
			}else{
				event.element().value=this.pageActual+1;
			}
		}
	},
	
	_goExcel: function(){
		//window.open(this.action + "?" + this._getParams(), 'excel', '' );
		excelForm = new Element('form', {'method': "post", 'action': this.action});
		$(this.dest).insert({bottom:excelForm});
		
		param = $H(this._getParams().toQueryParams());
		keys = param.keys();

		
		for(x = 0; x < keys.size(); x++){
			
			field = new Element('input', {'type': "hidden", 'name': keys[x], 'value': param.get(keys[x])});
			excelForm.insert({bottom:field});
		}
		
		field = new Element('input', {'type': "hidden", 'name': 'OUTPUT_FORMAT', 'value': 'EXCEL'});
		excelForm.insert({bottom:field});
		
		//console.log(excelForm);
		excelForm.submit();
		excelForm.remove();
	}
	
}

var Order = Class.create();
Order.prototype = {
	orderField: null,
	asc: null,
	pagination: null,
	
	//constructor
	initialize: function(reference, pagination){
		this.pagination = pagination;
		this.reference = reference;
		
		this._make();
	},
	
	_make: function(){
		//busca los campos ordenables
		$$('#'+this.reference+' .campo-ordenable').each(function(link){
			
			link.observe('click', this._order.bind(this));
		}, this);
	},
	
	_order: function(event){
		link = event.element();
		
		rel = link.readAttribute('rel');
		
		if(this.orderField == rel){
			
			if(this.asc == 'D'){
				this.asc = 'A';
			}else{
				this.asc = 'D';
			}
		}else{
			this.asc = 'A';
		}
		this.orderField = rel;
		//console.log(this);
		this.pagination._first();
	},
	
	_getOrderString: function(){
		
		if(this.orderField != null){
			return "&FIELD=" + this.orderField + "&ASC=" + this.asc;
		}else{
			return "";
		}
	}
}

var Filter = Class.create();
Filter.prototype = {
	
	reference: null,
	action: null,
	params: null,
	filterObj: null,
	filterKeys: null,
	bufferSize: 3,
	buffer: null,
	pagination: null,
	
	

	//constructor
	initialize: function(reference, action, params, pagination){

		this.reference = reference;
		this.action = action;
		this.params = params;
		this.pagination = pagination;
		
		if($("filter_"+this.reference) == null){
			filter = "<div ><input type=\"text\" class=\"form-control\" id=\"filter_"+this.reference+"\" name=\"filter_"+this.reference+"\" /></div>";
			$(reference).insert({before : filter});
			
			this.filterObj = $("filter_"+this.reference);
			
			this.filterObj.observe('keypress', this._filter.bind(this));
			
			
			//obtiene los links
			this.filterKeys="";
			$$('#'+this.reference+' .campo-ordenable').each(function(link){
				this.filterKeys += link.readAttribute('rel') + ',';
				
			}, this);
			this.filterKeys = this.filterKeys.substring(0,this.filterKeys.length-1);

		}
	},
	
	_filter: function(event){
		
		
		
		if(event.keyCode == Event.KEY_RETURN || this.buffer == this.bufferSize) {
        	//limpia el buffer
        	this.buffer = 0;
        	
        	this.pagination._first();
    	}else{
    		//incrementa el bufer
    		this.buffer++;
    	}
		
		
		
	},
	
	
	_update: function(params){
		this.params = params;
	},
	
	_getFiltersString: function(){
		if($F(this.filterObj).blank()){
			return "";	
		}else{
			return '&FILTER=' + $F(this.filterObj);
		}
	},
	
	_getFiltersKeysString: function(){
		if($F(this.filterObj).blank()){
			return "";	
		}else{
			return '&FILTER_KEYS='+this.filterKeys;
		}
	}
	
}


var Group = Class.create();
Group.prototype = {
	reference : null,
	pagination: null,
	groupText: null,
	
	initialize: function(reference, paginationObj){
		this.reference = reference;
		this.pagination= paginationObj;
		

		div = new Element('div', {'id': "group_"+this.reference });
		$(this.reference).insert({before : div});
		
		div_limpia = new Element('div');
		div_limpia.setStyle({'clear': 'both'});
		div.insert({after : div_limpia});
		
		this._make();
	},
	
	_make: function(){
		
		$$('#'+this.reference+' .campo-ordenable').each(function(link){

				a = new Element('a', { 'class': 'grouper', rel: link.readAttribute('rel'), title: link.innerHTML, href: 'javascript:void(0)' });	
				//a.update(' ');
				a.observe('click', this._addGroupField.bind(this, a));
				link.insert({after : a});
				
		}, this);
	},
	
	_addGroupField: function(){
		link = $A(arguments).first();
		
		a = new Element('a', { 'class': 'group_field', rel: link.readAttribute('rel'), href: 'javascript:void(0)' });
		a.update('<b>' + link.readAttribute('title') + '</b>');
		a.observe('click', this._removeGroup.bind(this, a));
		
		$("group_"+this.reference).insert({bottom : a});
		
		this._group();
	},
	
	_group : function(){
		this.pagination._first();
	},
	
	_removeGroup : function(event){
		link = $A(arguments).first();
		
		link.remove();
		
		this._group();
	},
	
	_getGroupsString : function(){
		this.groupText = "&GROUPS=";
		allLinks = $$('#group_'+this.reference+' .group_field').each(function(link){
			this.groupText += link.readAttribute('rel') + ",";
		}, this);
		
		if(allLinks.size() > 0){
			this.groupText = this.groupText.substring(0,this.groupText.length-1);
		}else{
			this.groupText="";
		}
		
		return this.groupText;
	}
}

var SortFields = Class.create();
SortFields.prototype = {
	reference : null,
	pagination: null,
	ul: null,
	sortableObj: null,
	sortText: null,
	showFields: true,
	
	initialize: function(reference, paginationObj){
		this.reference = reference;
		this.pagination= paginationObj;
		

		div = new Element('div', {'id': "reorder_"+this.reference, 'class': 'show_fields_container' });
		$(this.reference).insert({before : div});
		
		div_limpia = new Element('div');
		div_limpia.setStyle({'clear': 'both'});
		div.insert({after : div_limpia});
		
		this.ul = new Element('ul', {'id': "SHOW_FIELDS_"+this.reference, 'class': 'show_fields'});
		$(div).update(this.ul);
		
		a = new Element('a', {  'class': 'tab', 'href':'javascript:void(0)' });	
		a.observe('click', this._toogleFieldsSelector.bind(this));
		a.update('Campos');
		div.insert({top : a});
		
		this._make();
		this._toogleFieldsSelector();
	},
	
	_make: function(){
		
		$$('#'+this.reference+' .campo-ordenable').each(function(link){

				li = new Element('li', {  'rel': link.readAttribute('rel') });	
				li.update(link.innerHTML);
				li.observe('click', this._sort.bind(this));
				
				a = new Element('a', {  'class': 'remove_field', 'href':'javascript:void(0)' });	
				a.observe('click', this._removeField.bind(this));
				a.update('&nbsp;&nbsp;');
				li.insert({top: a});

				this.ul.insert({bottom : li});
				
		}, this);
		
		//console.log(this.ul.readAttribute('id'));
	    this.sortableObj = Sortable.create(this.ul.id);
	},
	
	_sort: function(){
		this.pagination._first();
	},
	
	_getSortsString: function(){
		
		;
		this.sortText = "&SHOW_FIELDS=";
		allLinks = this.ul.childElements().each(function(li){
			this.sortText += li.readAttribute('rel') + ",";
		}, this);
		
		if(allLinks.size() > 0){
			this.sortText = this.sortText.substring(0,this.sortText.length-1);
		}else{
			this.sortText="";
		}
		
		return this.sortText;
	},
	
	_removeField: function(event){
		
		li = event.element().up('li');
		//console.log(li)
		li.remove();
	},
	
	_toogleFieldsSelector: function(){
		
		if(this.showFields){
			Effect.Fade(this.ul, { duration: 0.8 });
			this.showFields=false;
		}else{
			Effect.Appear(this.ul, { duration: 0.8 });
			this.showFields=true;
		}
		
		
		
		
	}
}






function getRadioValue(form, radio){
	return $(form).getInputs('radio', radio).find(function(radio) { return radio.checked; }).value;
}

function setRadioCheked(form, radio, valor){
	if(valor == ""){
		valor = 0;
	}
	rads = $(form).getInputs('radio', radio);
	
	for(i=0;i<rads.size();i++){
		if(rads[i].value==valor){
			rads[i].checked=true;
			break;
		}
	}
}

var ChartGenerator = Class.create();
ChartGenerator.prototype = {
	script: null,
	params: null,
	dest: null,
	graph: null,
	imgFormat: null,
	
	initialize: function(script, params, dest){
		this.imgFormat = "png";
		this.script = script;
		this.params = params.toQueryParams();
		//console.log(dest);
		this.dest = $(dest);
		//console.log($(dest));
		
		this._update();

		div = new Element("div", {'class': 'well well-sm'});
			a = new Element('button', {  'class': 'graphControl btn btn-info', 'type':'button' });
			a.update("Actualizar");	
			a.observe('click', this._update.bind(this));
			div.insert({bottom : a});
			
			a = new Element('button', {  'class': 'graphControl btn btn-info', 'type':'button' });
			a.update("download");	
			a.observe('click', this._downloadIMG.bind(this));
			div.insert({bottom : a});
			
		this.dest.insert({after : div});
	},
	
	_update: function(){
		loadingIcon.addRequest();
					
		new Ajax.Request(this.script, {
						  method: 'post',
						  parameters: this.params, 
						  
						  onSuccess: this._make.bind(this),
						  onComplete: function(t){
										loadingIcon.endRequest();
						  }
						});
	},
	
	_make: function(t){
		json = t.responseJSON;
		
		this._defaulContainerStyle(json.destStyle);
		
		console.log(this.dest);
		this.graph = Flotr.draw(this.dest, json.series, {
	        xaxis: {
	            minorTickFreq: 4,
	            ticks: json.tick_x,
	            labelsAngle: 45,
            	title: json.xSerieName
	        },
	        yaxis: {
            	title: json.ySerieName,
            	tickDecimals: 0,
            	min: 0
	        },
	        grid: {
	            minorVerticalLines: true,
	            backgroundColor: ["#fff", "#CEF6E3"]
	        },
	        HtmlText: false,
			mouse: {
	            track: true,
	            relative: true,
	            trackDecimals: 2,
	            trackFormatter: function(o) {
	            	//console.log(o);
	            	
	                return "x = " + o.series.xaxis.ticks[o.index].label + ", y = " + o.y;
	            }
	       },
	       legend: {
	            position: "nw",
	            backgroundColor: "#D2E8FF"
        	}
	   });
	   console.log(this.graph);
	},
	
	_defaulContainerStyle: function(dest_style){
		if(!dest_style){
			this.dest.setStyle({
				width: "500px",
				height: "300px"
			});
		}else{
			this.dest.setStyle(dest_style);
		}
		
		
	},
	
	_downloadIMG : function(){
		this.graph.download.saveImage(this.imgFormat);
	}
}


var FilterAdv = Class.create();
FilterAdv.prototype = {
	reference : null,
	pagination: null,
	filterText: null,
	
	initialize: function(reference, paginationObj){
		this.reference = reference;
		this.pagination= paginationObj;
		console.log('creando filtros avazados');

		div = new Element('div', {'id': "f_adv_"+this.reference });
		$(this.reference).insert({before : div});
		
		div_limpia = new Element('div');
		div_limpia.setStyle({'clear': 'both'});
		div.insert({after : div_limpia});
		
		this._make();
	},
	
	_make: function(){
		console.log('construyrnfos');
		$$('#'+this.reference+' .campo-ordenable').each(function(link){

				a = new Element('a', { 'class': 'filter_adv_btn fa fa-filter ', rel: link.readAttribute('rel'), title: link.innerHTML, href: 'javascript:void(0)' });	
				//a.update(' ');
				a.observe('click', this._addFilter.bind(this, a));
				link.insert({before : a});
				
		}, this);
	},
	
	_addFilter: function(){
		link = $A(arguments).first();
		
		div = new Element('div', { 'class': 'f_advX'});

		
		a = new Element('a', { 'class': 'remove_field', rel: link.readAttribute('rel'), href: 'javascript:void(0)' });
		
		a.observe('click', this._removeFilter.bind(this));
		div.insert({bottom : a});
		
		span = new Element('span');
		span.update(link.readAttribute('title'));
		div.insert({bottom : span});
		
		select = new Element('select');
		select.insert(new Element('option', {value: 'eq'}).update('igual a'));
		select.insert(new Element('option', {value: 'ne'}).update('distinto de'));
		select.insert(new Element('option', {value: 'lk'}).update('contiene'));
		select.insert(new Element('option', {value: 'gt'}).update('mayor a'));
		select.insert(new Element('option', {value: 'ge'}).update('mayor e igual a'));
		select.insert(new Element('option', {value: 'lt'}).update('menor a'));
		select.insert(new Element('option', {value: 'le'}).update('menor e igual a'));
		
		div.insert({bottom : select});
		
		text = new Element('input', {type:'text'});
		div.insert({bottom : text});
		
		
		$("f_adv_"+this.reference).insert({bottom : div});
		
		
	},
	
	_removeFilter : function(event){
		
		div = event.element().up();

		div.remove();
	},
	
	_getString : function(filters){
		if(filters==""){
			filters = "&FILTER=";
		}else{
			filters = filters + " ";
		}
		this.filterText = filters;
		
		allLinks = $$('#f_adv_'+this.reference+' .f_advX').each(function(div){
			this.filterText += div.down('a').readAttribute('rel') + '::';
			this.filterText += div.down('select').value  + '::';
			text = div.down('input').value  + '&';
			
			this.filterText += text.replace(' ', ';;')  + '&';

		}, this);
		
		if(allLinks.size() > 0){
			this.filterText = this.filterText.substring(0,this.filterText.length-1);
		}else{
			this.filterText="";
		}
		
		return this.filterText;
	}
}

