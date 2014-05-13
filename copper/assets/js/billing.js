Copper.billing = {
	toBill: {},

	getOtherItemsFields: function(){
		var fields = $$("#otherItemsEditRow input");
		var obj = {};
		fields.forEach(function(item){
			obj[item.id] = item.value;
		})
		return obj;
	},

	ajaxAddOtherItem: function(){
		var parameters = this.getOtherItemsFields();
		var request = new Ajax.Request('index.php', {
			method:'get',
			parameters: parameters,
			onSuccess: function(response){
				$('addedItems').innerHTML += response.responseText;
				$$("#otherItemsEditRow input[type='text']").each(function(item){item.value=""})
				Copper.billing.initChecks();
			},
			onError: function(){
				alert("error saving");
			}
		});
		return false;
	},

	toggleOtherItem: function(id){
		Effect.toggle($(id), 'blind', {duration:0.5});
	},

	initChecks:function(){
		Copper.billing.graphs.init();

		// ie teh gays
		if ($('addOtherItem'))
		{
			if((! $('addOtherItem').hasAttribute("observed")))
				$('addOtherItem').observe('click', Copper.billing.ajaxAddOtherItem.bind(Copper.billing));
			$('addOtherItem').setAttribute("observed",true);
		}
		
		if ($$('.payable').length > 0)
		{
			$$('.payable').forEach(function(item){
				if(!item.hasAttribute("converted")){
					item.observe("click",Copper.billing.checkHandler.bind(Copper.billing));
					var idParts= item.getAttribute('id').split("_");
					var taskId = idParts[1];
					var amountElement = $(idParts[0]+"Value_"+taskId);
					amountElement.observe("change",Copper.billing.amountHandler.bind(Copper.billing));
					var value = parseFloat(amountElement.value);
					var amount = isNaN(value)?0:value;
					Copper.billing.toBill[taskId] = {checked:item.checked, amount:amount, isTask:item.getAttribute("isTask")};
					item.setAttribute("converted",true);
				}
			});
			this.updateTotals();
		}
		editableFieldmanager.init();
	},

	checkHandler: function(e){
		var el = Event.element(e);
		var taskId = this._getTaskId(el);
		Copper.billing.toBill[taskId]['checked'] = el.checked;
		this.updateTotals();
	},
	amountHandler: function(e){
		var el = Event.element(e);
		var taskId = this._getTaskId(el);
		var value = parseFloat($F(el));
		Copper.billing.toBill[taskId]['amount'] = isNaN(value)?0:value;
		this.updateTotals();
	},
	convertMoney: function(str){
		var num = parseFloat(str);
		num = isNaN(num)?0:num;
		return '' + Copper.currency_symbol + num.toFixed(2);
	},
	updateTotals: function(){
		var items = this.getInvoiceItems();
		var taskSum = 0;
		cu.forEach(items.tasks, function(item){
			taskSum += parseFloat(item.amount);
		}, this)
		$('totalToBill').innerHTML = this.convertMoney(taskSum);
		var otherSum = 0;
		cu.forEach(items.other, function(item){
			otherSum += parseFloat(item.amount);
		}, this)
		$('totalOtherToBill').innerHTML = this.convertMoney(otherSum);
		$('totalProjectToBill').innerHTML = this.convertMoney(parseFloat(otherSum) + parseFloat(taskSum));
	},
	_getTaskId: function(el){
		var id = el.getAttribute("id");
		var taskId = id.split("_")[1];
		return taskId;
	},
	getInvoiceItems: function(){
		var obj = this.toBill;
		var result = {tasks:[], other:[]};
		for(var key in obj){
			var item = obj[key];
			if((item)&&(item.checked)){
				if(item.isTask=="true"){
					result.tasks.push({id:key, amount:item.amount})
				} else {
					result.other.push({id:key, amount:item.amount})
				}
			}
		}
		return result;
	},
	
	getInvoiceInfo: function(invoice_id)
	{
		var data = {
			title: $('invoice_title_' + invoice_id).getValue(),
			status: $('invoice_status_' + invoice_id).getValue(),
			due: $('invoice_due_' + invoice_id).getValue()
		};
		return data;
	},
	
	deleteOther: function(id){
		this.currentTarget = id;
		modalDialog.show(
			Copper.language.MSG_CONFIRM_ITEM_DELETE_TITLE, 
			Copper.language.MSG_CONFIRM_ITEM_DELETE_BODY, 
			Copper.billing.removeOtherItem.bind(Copper.billing)
		);
		return false;
	},
	removeOtherItem: function(){
		var id = this.currentTarget;
		this.currentTarget = null;
		cu.ajaxGet(("index.php?module=projects&action=deleteotheritem&projectid=" + Copper.project.ID + "&id="+id), function(response){
			var resp = (response.responseText).evalJSON();
			if(resp.success){
				Effect.toggle($("block_"+id),'blind',{duration:0.5});
				delete(Copper.billing.toBill[id]);
			} else {
				console.error("failed to remove");
			}
		});
	},
	
	// this called when we start creating a new invoice
	createInvoice: function(isQuote) {
		if ( ! isQuote ) {
			isQuote = 0;
		}

		var params = {
			"projectid": Copper.project.ID,
			"quote":isQuote
		}
		cu.ajaxPost(
			"index.php?module=projects&action=ajaxcreateinvoice", 
			params, 
			function(response){
				// append, but hide so we can blind it open in a tic
				$('invoicelist').insert(response.responseText, 'bottom').childElements().last().hide();
				Effect.toggle($('invoicelist').childElements().last(), 'blind', {duration:0.5});
				
				// and we'll reinit so clikcs get listened to etc
				Copper.billing.initChecks().bind(Copper.billing);
			}
		);
	},

	newInvoice: function(invoice_id){ this.sendInvoiceQuoteRequest(invoice_id, 0); },
	newQuote: function(invoice_id){ this.sendInvoiceQuoteRequest(invoice_id, 1); },
	sendInvoiceQuoteRequest: function(invoice_id, isQuote){
		var data = this.getInvoiceItems();
		var params = {
			'invoice_id': invoice_id,
			"data": Object.toJSON(data),
			"invoice_info": Object.toJSON(Copper.billing.getInvoiceInfo(invoice_id)),
			"projectid": Copper.project.ID,
			"isquote":isQuote
		};

		cu.ajaxPost("index.php?module=projects&action=ajaxnewinvoice", params, function(response){
			// hokay, now we blind up the quote, and remove it.
			Effect.toggle(
				$('invoiceBlock' + invoice_id), 
				'blind', 
				{
					duration:0.5, 
					afterFinish: function() { 
						$('invoiceBlock' + invoice_id).remove(); 

						// also, add the new one.
						$('invoicelist').insert(response.responseText, 'bottom').childElements().last().hide();
						Effect.toggle($('invoicelist').childElements().last(), 'blind', {duration:0.5});
				}
			});
		})
	},
	/*show the modal dialog warning*/
	deleteInvoice: function(id){
		this.currentId = id;
		modalDialog.show(
			Copper.language.MSG_CONFIRM_ITEM_DELETE_TITLE,
			Copper.language.MSG_CONFIRM_ITEM_DELETE_BODY,
			Copper.billing.removeInvoice.bind(Copper.billing),
			function(){Copper.billing.currentId = null;}
		);
	},
	/*aciton the delete at the server and the page level*/
	removeInvoice: function(){
		var id = this.currentId;
		var url = "index.php?module=projects&action=ajaxdeleteinvoice&id="+id;
		cu.ajaxGet(url, function(response){
			var obj = response.responseText.evalJSON();
			if(obj.success){
				Effect.toggle($('invoiceBlock'+id), "blind", {duration:0.5, onSuccess:function(){console.log(arguments);}});
			}
		})
	},
	editInvoice: function(id){
		editableFieldmanager.makeEditable(id);
		$("updateInvoiceRow_"+id).show();
		$("standardInvoiceRow_"+id).hide();
	},
	updateInvoice: function(id){
		//send save.. on save:
		var rootNode = $("invoiceBlock"+id);
		var inputs = rootNode.getElementsBySelector("input[type='text']");
		inputs = inputs.concat(rootNode.getElementsBySelector("select"));
		var params = {"id":id};
		inputs.each(function(item){
			params[item.name] = $F(item);
		})
		var url = "index.php?module=projects&action=ajaxupdateinvoice";
		// console.log(params);
		
		cu.ajaxPost(url, params, function(response){
			var obj = response.responseText.evalJSON();
			if(obj.success){
				editableFieldmanager.makeReadable(id);
				$("updateInvoiceRow_"+id).hide();
				$("standardInvoiceRow_"+id).show();
				var row = editableFieldmanager.getRow(id);
				row[0].read.innerHTML = params.invoiceName;
				row[1].read.innerHTML = row[1].write.options[params.invoiceStatus].innerHTML;
				row[2].read.innerHTML = params.invoiceDue;
			} else {
				console.error("unable to update");
			}
		});
		
	},
	cancelEditInvoice: function(id){
		editableFieldmanager.makeReadable(id);
		$("updateInvoiceRow_"+id).hide();
		$("standardInvoiceRow_"+id).show();
	},
	setInvoiceVisibility: function(isEdit, id){
		var standardDisplay = "";
		var editDisplay = "none";
		if(isEdit){
			var standardDisplay = "none";
			var editDisplay = "";
		}
		$('standardInvoiceRow_'+id).style.display = standardDisplay;
		$('updateInvoiceRow_'+id).style.display = editDisplay;
		$$("#invoiceBlock"+id+" .readOnlyDisplay").forEach(function(item){item.style.display = standardDisplay})
		$$("#invoiceBlock"+id+" .writeDisplay").forEach(function(item){item.style.display = editDisplay})
	}
}

Copper.billing.graphs = {
	initted: false,
	
	init: function() {
		if (this.initted == true)
		{
			return;
		}
		
		this.initted = true;
		
		// load up the google api dynamically, as otherwise it slows down every other page load
		google.load("visualization", "1", { 
			packages: ["corechart", 'annotatedtimeline'],
			callback: Copper.billing.graphs.finish_init
		});
		
	},
	
	finish_init: function() {
		
		var b = Copper.project.budgets;
		// only draw graphs if we have some data.
		if ((b.target > 0) || (b.target > 0) || (b.target > 0) || (b.target > 0))
		{
			Copper.billing.graphs.draw_budgets_graph(b.target, b.cost, b.charge, b.invoiced);
		}

		if (b.invoices.length > 0)
		{
			Copper.billing.graphs.draw_invoices_graph(Copper.project.budgets.invoices);
		}
	},
	
	draw_budgets_graph: function(target, cost, charge, invoiced) {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Budget');
		data.addColumn('number', 'Value');
		data.addRows(4);
		data.setValue(0, 0, 'Target');
		data.setValue(0, 1, target);
		data.setValue(1, 0, 'Cost');
		data.setValue(1, 1, cost);
		data.setValue(2, 0, 'Charge');
		data.setValue(2, 1, charge);
		data.setValue(3, 0, 'Invoiced');
		data.setValue(3, 1, invoiced);

		var chart = new google.visualization.ColumnChart(document.getElementById('billing_overview'));

		chart.draw(data, {
			width: 300,
			height: 240,
			title: 'Project Budget Overview',
			legend: 'none',
			backgroundColor: '#ffffff'
		});
	},
	
	// invoices object should be an array like the following 
	// [{id: 24, date: 2010-06-21, amount: 258.00}, {id: 28, date: 2010-08-21, amount: 458.00}, ...]
	draw_invoices_graph: function(invoices)
	{
		var data = new google.visualization.DataTable();
		data.addColumn('date', 'Date');
		data.addColumn('number', 'Total Invoiced');
		data.addColumn('string', 'title1');
		data.addColumn('string', 'text1');
		data.addRows(invoices.length + 1); // add one for the first point.

		var i, invoice, total = 0;
		var min, date_to_use, lastDate = null;

		// // do a starting point
		data.setValue(0, 0, new Date(Copper.project.StartDate));
		data.setValue(0, 1, 0);

		for (i = 0; i < invoices.length; i++)
		{
			invoice = invoices[i];
			total += invoice.Amount;
			if (lastDate == new Date(invoice.date))
			{
				// if we have multiple invoices on the same date, shift them forward slightly so gg doesn't cry.
				mins = lastDate.getMinutes();
				date_to_use = lastDate;
				date_to_use.setMinutes(mins + 1);
			} else {
				lastDate = new Date(invoice.date);
				date_to_use = lastDate;
			}
			
			data.setValue(i + 1, 0, date_to_use);
			data.setValue(i + 1, 1, total);
			// don't annotate 0 dollar invoices.
			if (invoice.Amount != 0)
			{
				data.setValue(i + 1, 2, 'Invoice ' + invoice.id + ' due.');
				data.setValue(i + 1, 3, 'Invoice for ' + invoice.Amount + ' due.');
			}
		}

		var annotatedtimeline = new google.visualization.AnnotatedTimeLine(document.getElementById('invoices_timeline'));

		google.visualization.events.addListener(annotatedtimeline, 'ready', function(){
			jQuery('#scrollingListTd > div > div').css('position','absolute');
		});

		annotatedtimeline.draw(data, {
			backgroundColor : '#f7f7f7',
			'displayAnnotations': true,
			'displayRangeSelector' : true, // Do not sow the range selector
			'displayZoomButtons': true, // DO not display the zoom buttons
			'fill': 20, // Fill the area below the lines with 20% opacity
			'legendPosition': 'newRow', // Can be sameRow
			'thickness': 2 // Make the lines thicker
		});
	}
}

if ((Copper != undefined) && (Copper.project != undefined) && (Copper.project.budgets != undefined))
{
	cu.addOnLoad(Copper.billing.initChecks, Copper.billing);
}

var editableFieldmanager = {
	index:{},
	getRow: function(id){
		return this.index[id];
	},
	register: function(id, readElement, writeElement){
		if(!this.index[id]){this.index[id]=[];}
		this.index[id].push({read: readElement, write:writeElement, value:""});
	},
	makeEditable: function(id){
		var obj = this.index[id];
		if(obj){
			obj.each(function(item){
				item.read.hide();
				item.write.show();
			})
		}
	},
	makeReadable: function(id){
		var obj = this.index[id];
		if(obj){
			obj.each(function(item){
				item.read.show();
				item.write.hide();
			});
		}
	},
	/* partner up all the readony and writable fields making it easy to show / hide a whole row */
	init: function(){
		$$('#invoicelist li[id]')
			.each(function(item){
				var id = parseInt(item.id.replace("invoiceBlock",""));
				$$('#invoiceBlock'+id+' .readOnlyDisplay').zip($$('#invoiceBlock'+id+' .writeDisplay')).each(function(item){editableFieldmanager.register(id, item[0], item[1])})
			});
		jQuery('.js_other_items_list').show();
		jQuery('.js_show_purchase_header').hide();
		
	}
}

