if (typeof Customweb == 'undefined') {
	var Customweb = {};
}

Customweb.BarclaycardCw = Class.create({
	initialize : function(hiddenFieldsUrl, visibleFieldsUrl, serverUrl, javascriptUrl, saveShippingUrl, methodCode)
	{
		this.hiddenFieldsUrl = hiddenFieldsUrl;
		this.visibleFieldsUrl = visibleFieldsUrl;
		this.serverUrl = serverUrl;
		this.javascriptUrl = javascriptUrl;
		this.saveShippingUrl = saveShippingUrl;
		this.methodCode = methodCode;
		
		this.defaultFormValidation = function(successCallback, failureCallback){
			var validateFunctionName = 'cwValidateFields'+this.methodCode;
			var validateFunction = window[validateFunctionName];
			if(typeof validateFunction != 'undefined') {
				validateFunction(successCallback, failureCallback);
				return;
			}
			successCallback(new Array());
			
		};
		this.formValidation = this.defaultFormValidation;

		this.onOrderCreated = this.onOrderCreated.bindAsEventListener(this);
		this.onReceivedHiddenFields = this.gatherHiddenFields.bindAsEventListener(this);
		this.onReceivedVisibleFields = this.displayVisibleFields.bindAsEventListener(this);
		this.onReceiveJavascript = this.runAjaxAuthorization.bindAsEventListener(this);

		if (typeof checkout != 'undefined' && typeof Review != 'undefined' && typeof FireCheckout == 'undefined' && typeof IWD == 'undefined') {
			checkout.accordion.openSection = checkout.accordion.openSection.wrap(this.opcGotoSection.bind(this));
			Review.prototype.save = Review.prototype.save.wrap(this.beforePlaceOrder.bind(this));
			Payment.prototype.save = Payment.prototype.save.wrap(this.beforePaymentSave.bind(this));
			if (typeof shippingMethod != 'undefined') {
				shippingMethod.onSave = this.loadPaymentForm.bindAsEventListener(this);
				shippingMethod.saveUrl = this.saveShippingUrl;
			}
		} else if (typeof AWOnestepcheckoutForm != 'undefined') {
			awOSCForm.placeOrderButton.stopObserving('click');
			AWOnestepcheckoutPayment.prototype.savePayment = AWOnestepcheckoutPayment.prototype.savePayment.wrap(this.awcheckoutPaymentSave.bind(this));
			AWOnestepcheckoutForm.prototype.placeOrder = AWOnestepcheckoutForm.prototype.placeOrder.wrap(this.awcheckoutPlaceOrder.bind(this));
			awOSCForm.placeOrderButton.observe('click', awOSCForm.placeOrder.bind(awOSCForm));
			this.formValidation = function(successCallback, failureCallback){
				if(!awOSCForm.validate()) {
					failureCallback({}, []);
					return;
				}
				this.defaultFormValidation(successCallback, failureCallback);
			};
		}
		// For GoMage LightCheckout
		else if (typeof checkout != 'undefined' && typeof checkout.LightcheckoutSubmit != 'undefined') {
			Lightcheckout.prototype.LightcheckoutSubmit = Lightcheckout.prototype.LightcheckoutSubmit.wrap(this.lightcheckoutBeforePaymentSave.bind(this));
			Lightcheckout.prototype.saveorder = Lightcheckout.prototype.saveorder.wrap(this.lightcheckoutSaveOrder.bind(this));
			this.formValidation = function(successCallback, failureCallback){
				if(!checkoutForm.validator.validate()) {
					failureCallback({}, []);
					return;
				}
				this.defaultFormValidation(successCallback, failureCallback);
			};
		}
		// For FireCheckout
		else if (typeof FireCheckout != 'undefined') {
			FireCheckout.prototype.save = FireCheckout.prototype.save.wrap(this.firecheckoutSave.bind(this));
			FireCheckout.prototype.update = FireCheckout.prototype.update.wrap(this.firecheckoutUpdate.bind(this));
			FireCheckout.prototype.setResponse = FireCheckout.prototype.setResponse.wrap(this.firecheckoutSetResponse.bind(this));
			this.formValidation = function(successCallback, failureCallback){
				if((checkout.validate && !checkout.validate()) || (checkout.validator.validate && !checkout.validator.validate())) {
					failureCallback({}, []);
					return;
				}
				this.defaultFormValidation(successCallback, failureCallback);
			};
		} else if (typeof IWD != 'undefined' && typeof IWD.OPC != 'undefined') {
			IWD.OPC.savePayment = IWD.OPC.savePayment.wrap(this.iwdSavePayment.bind(this));
			IWD.OPC.saveOrder = IWD.OPC.saveOrder.wrap(this.iwdSaveOrder.bind(this));
			IWD.OPC.prepareOrderResponse = IWD.OPC.prepareOrderResponse.wrap(this.iwdPrepareOrderResponse.bind(this));
			this.formValidation = function(successCallback, failureCallback){
				successCallback(new Array());
			};
		} else if (typeof oscPlaceOrder != 'undefined') {
			window.save_shipping_method = window.save_shipping_method.wrap(this.magestoreSaveShippingMethod.bind(this));
			window.oscPlaceOrder = window.oscPlaceOrder.wrap(this.magestorePlaceOrder.bind(this));
			this.formValidation = function(successCallback, failureCallback){
				if(!(new Validation('one-step-checkout-form').validate() && checkpayment())) {
					failureCallback({}, []);
					return;
				}
				this.defaultFormValidation(successCallback, failureCallback);
			};
		} else {
			var onestepcheckoutPlaceOrder = $('onestepcheckout-place-order');
			if (typeof onestepcheckoutPlaceOrder != 'undefined') {
				onestepcheckoutPlaceOrder.observe('click', this.createOrder.bind(this));
				var form = new VarienForm('onestepcheckout-form');
				this.originalFunction = Validation.prototype.validate.bind(form.validator);
				Validation.prototype.validate = Validation.prototype.validate.wrap(this.onestepValidate.bind(this));
				this.formValidation = function(successCallback, failureCallback){
					if(!this.originalFunction()) {
						failureCallback({}, []);
						return;
					}
					this.defaultFormValidation(successCallback, failureCallback);
				};
			}
			else {
				console.log("You should use either one of the supported one page checkouts or the magento default onestepcheckout.")
			}
		}
	},
	
	opcGotoSection: function(callOriginal, section)
	{
		if (typeof section != "string") {
			section = section.id;
		}
		
		if (section == 'opc-payment') {
			this.refillPaymentForm(this.formFields);
		}
		
		callOriginal(section);
	},
	
	loadPaymentForm: function(transport)
	{
		if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
		
		shippingMethod.nextStep(transport);
		
		if (!response.error && response.update_section.js) {
			eval.call(window, response.update_section.js);
		}
	},

	loadAliasData : function(element)
	{
		var sel = element;
		var value = sel.options[sel.selectedIndex].value;
		new Ajax.Request(this.visibleFieldsUrl, {
			method : 'get',
			parameters : 'alias_id=' + value + '&payment_method=' + this.methodCode,
			onSuccess : this.onReceivedVisibleFields
		});
	},

	displayVisibleFields : function(transport)
	{
		if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
		
		if (response.error) {
            alert(response.message);
            return false;
        }
		
		var container = $('payment_form_fields_' + this.methodCode);
		container.update(response.html);
		
		eval.call(window, response.js);
	},

	isModulePaymentMethod: function(){
		var result = false;
		var currentMethod;
		if (typeof payment != 'undefined') {
			currentMethod = payment.currentMethod;
		}
		else if (typeof awOSCPayment != 'undefined' && typeof awOSCPayment.currentMethod != 'undefined') {
			currentMethod = awOSCPayment.currentMethod;
		}
		else if (typeof oscPlaceOrder != 'undefined') {
			currentMethod = $RF(form, 'payment[method]');
		}
		if (currentMethod && currentMethod == this.methodCode) {
			if(document.getElementById(currentMethod + '_authorization_method')) {
				result = true;
			}
		}
		return result;
	},
	
	isAuthorization : function(method)
	{
		var result = false;
		var currentMethod;
		if (typeof payment != 'undefined') {
			currentMethod = payment.currentMethod;
		}
		else if (typeof awOSCPayment != 'undefined' && typeof awOSCPayment.currentMethod != 'undefined') {
			currentMethod = awOSCPayment.currentMethod;
		}
		else if (typeof oscPlaceOrder != 'undefined') {
			currentMethod = $RF(form, 'payment[method]');
		}
		if (currentMethod && currentMethod == this.methodCode) {
			if(document.getElementById(currentMethod + '_authorization_method')) {
				if (document.getElementById(currentMethod + '_authorization_method').value == method) {
					result = true;
				}
			}
		}
		return result;
	},

	onOrderCreated: function(transport)
	{
		return this.requestHiddenFields(transport, review.onComplete);
	},

	requestHiddenFields : function(transport, onComplete)
	{
		var response = false;
		if (transport && transport.responseText) {
			try {
				response = eval('(' + transport.responseText + ')');
			} catch (e) {
				response = {};
			}
		} else if (typeof transport == 'object') {
			response = transport;
		}
		
		if (response && typeof response == 'object') {
			if (!response.success) {
				var msg = response.error_messages;
				if (typeof (msg) == 'object') {
					msg = msg.join("\n");
				}
				onComplete();
				if (msg) {
					alert(msg);
				}
			} else if (this.isAuthorization('hidden')) {
				new Ajax.Request(this.hiddenFieldsUrl, {
					onSuccess : this.onReceivedHiddenFields,
					onFailure: onComplete
				});
			} else if (this.isAuthorization('ajax')) {
				new Ajax.Request(this.javascriptUrl, {
					onSuccess : this.onReceiveJavascript,
					onFailure: onComplete
				});
			} else if (this.isAuthorization('server')) {
				this.sendFieldsToUrl(this.serverUrl);
			} else {
				this.sendFieldsToUrl(response.redirect);
			}
		}
	},

	runAjaxAuthorization : function(transport)
	{
		var data = eval('(' + transport.responseText + ')');
		if (typeof IWD != 'undefined') {
			IWD.OPC.Checkout.hideLoader();
			IWD.OPC.Checkout.unlockPlaceOrder();
			IWD.OPC.saveOrderStatus = false;
		}
		if (data.error == 'no') {
			var javascriptUrl = data.javascriptUrl;
			var callbackFunction = data.callbackFunction;

			this.loadJavascript(javascriptUrl, (function()
			{
				callbackFunction(this.formFields);
			}).bind(this));
		} else {
			alert(data.message);
		}
	},

	gatherHiddenFields : function(transport)
	{
		var formInfo = eval('(' + transport.responseText + ')');

		this.extendMaps(this.formFields, formInfo.fields);
		this.sendFieldsToUrl(formInfo.actionUrl);
	},

	sendFieldsToUrl : function(url, params)
	{
		var me = this,
			tmpForm = new Element('form', {
			'action' : url,
			'method' : 'post',
			'id' : 'customweb_barclaycardcw_form'
		});
		$$('body')[0].insert(tmpForm);
		var fields = $H(this.formFields);
		fields.each(function(pair)
		{
			me.insertHiddenField(tmpForm, pair.key, pair.value);
		}, this);
		if (params) {
			params = $H(params);
			params.each(function(pair)
			{
				me.insertHiddenField(tmpForm, pair.key, pair.value);
			}, this);
		}
		tmpForm.submit();
	},
	
	insertHiddenField: function(form, key, value)
	{
		if (value == null) {
			value = '';
		}
		if (typeof value == 'object') {
			for (var i = 0; i < value.length; i++) {
				form.insert(new Element('input', {
					'type' : 'hidden',
					'name' : key + '[]',
					'value' : value[i]
				}));
			}
		} else {
			form.insert(new Element('input', {
				'type' : 'hidden',
				'name' : key,
				'value' : value
			}));
		}
		
	},
	
	// This function is used for onestepcheckout only
	onestepValidate: function(callOriginal)
	{
		if(this.isModulePaymentMethod()) {
			return false;
		}
		return callOriginal();
	},

	// This function is used for onestepcheckout only
	createOrder : function(event)
	{
		if(this.isModulePaymentMethod()) {
			
			var submitelement = $('onestepcheckout-place-order');
            var loaderelement = new Element('div', {'id': 'onestepcheckout-place-order-loading-barclaycardcw'}).
                addClassName('onestepcheckout-place-order-loading').
                update('Please wait, processing your order...');
            submitelement.parentNode.appendChild(loaderelement);
            submitelement.removeClassName('orange').addClassName('grey');
            submitelement.disabled = true;
            this.formValidation(
					function(valid){this.createOrderSuccessCallback(event)}.bind(this),
					function(errors, valid){this.createOrderFailureCallback(errors,valid)}.bind(this));
			return;
		}
	},
	
	
	
	// This function is used for onestepcheckout only
	createOrderSuccessCallback : function(event)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
					
			var form = $('onestepcheckout-form');
			var formUrl = form.readAttribute('action');
			formUrl = formUrl.slice(0, -1) + '/';
			form.writeAttribute('action', 'javascript:void(0);');
			var params = Form.serialize(form);
				
			this.refillPaymentForm(this.formFields);
				var request = new Ajax.Request(formUrl, {
				method : 'post',
				parameters : params,
				onSuccess : this.checkOrderStatus.bindAsEventListener(this),
				onFailure : checkout.ajaxFailure.bind(checkout)
			});
		}
		else {
			$('onestepcheckout-form').submit();
		}
	},
	
	// This function is used for onestepcheckout only
	createOrderFailureCallback : function(errors, valid)
	{
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		$('onestepcheckout-place-order-loading-barclaycardcw').remove();
		var submitelement = $('onestepcheckout-place-order');
		submitelement.removeClassName('grey').addClassName('orange');
        submitelement.disabled = false;
	},

	// This function is used for onestepcheckout only
	checkOrderStatus : function(transport)
	{
		var html = transport.responseText;
		try {
			response = eval('(' + html + ')');
		} catch (e) {
			response = {};
		}
		if (response.success) {
			this.requestHiddenFields(transport);
		} else {
			// Show the error messages by rendering the returned form
			// content
			var formStartTag = '<form id="onestepcheckout-form"';
			var formEndTag = '</form>';
			var start = html.indexOf(formStartTag);
			var stop = html.indexOf(formEndTag, start) + formEndTag.length;
			var formData = html.substr(start, stop - start);
			$('onestepcheckout-form').replace(formData);
			$('onestepcheckout-place-order').observe('click', this.createOrder.bind(this));
		}
	},

	beforePlaceOrder : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			if (checkout.loadWaiting != false)
				return;
			checkout.setLoadWaiting('review');
			var params = Form.serialize(payment.form);
			if (review.agreementsForm) {
				params += '&' + Form.serialize(review.agreementsForm);
			}
			params.save = true;
			var request = new Ajax.Request(review.saveUrl, {
				method : 'post',
				parameters : params,
				onSuccess : this.onOrderCreated,
				onFailure : function(){
					review.onComplete();
					checkout.ajaxFailure();
				}
			});
		} else {
			callOriginal();
		}
	},

	extendMaps : function(destination, source)
	{
		for ( var property in source) {
			if (source.hasOwnProperty(property)) {
				destination[property] = source[property];
			}
		}
		return destination;
	},

	removeErrorMsg : function()
	{
		var messageContainer = $$('.messages');
		messageContainer.each(function(item)
		{
			item.update("");
		});
	},

	savePaymentInfoInBrowser : function()
	{


		// Get all form elements
		var fields = {};
		var tmp = '#payment_form_' + this.methodCode;
		var remove = this.methodCode + '[';

		var inputs = $$(tmp + ' input');
		inputs.each(function(i)
		{
			var name = i.name.replace(remove, "");
			name = name.replace("]", "");
			if (i.readAttribute('data-cloned-element-id')) {
				i.value = '';
				i.removeClassName('required-entry');
			} else if (name != '') {
				if(i.type == "radio") {
					 if(i.checked) {
						fields[name] = i.value;	 	
					 }
				}
				else{
					fields[name] = i.value;
					i.value = '';
				}
				i.removeClassName('required-entry');
			}
		});

		var selects = $$(tmp + ' select');
		selects.each(function(s)
		{
			var name = s.name.replace(remove, "");
			name = name.replace("]", "");
			fields[name] = s.options[s.selectedIndex].value;
			s.selectedIndex = 0;
			s.removeClassName('required-entry');
			s.removeClassName('validate-select');
		});

		// Remove possible error messages that could confuse the
		// customer.
		this.removeErrorMsg();

		this.formFields = fields;
	},
	
	refillPaymentForm: function(fields) {
		if (fields) {
			var tmp = '#payment_form_' + this.methodCode;
			var remove = this.methodCode + '[';
			
			$$(tmp + ' input').each(function(i){
				if (i.type != 'hidden' || i.readAttribute('originalElement')) {
					var name = i.name.replace(remove, "");
					name = name.replace("]", "");
					if(i.type == "radio") {
						if(i.value == fields[name]){
							i.checked = true;
						}
					}
					else{
						i.value = fields[name];
					}
					if (i.readAttribute('originalElement')) {
						$(i.readAttribute('originalElement')).value = i.value;
					}
				}
			});
	
			$$(tmp + ' select').each(function(s){
				var name = s.name.replace(remove, "");
				name = name.replace("]", "");
				s.value = fields[name];
			});
		}
	},

	//Default magento
	beforePaymentSave : function(callOriginal)
	{
		if(this.isModulePaymentMethod()) {
			checkout.setLoadWaiting('payment');
			this.formValidation(
					function(valid){this.beforePaymentSaveValidationSuccess(callOriginal)}.bind(this),
					function(errors, valid){this.beforePaymentSaveValidationFailure(errors,valid)}.bind(this))
			return false;
		}
		callOriginal();
	},
	
	
	beforePaymentSaveValidationSuccess : function(callOriginal) {
		
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}
		checkout.setLoadWaiting(false);
		callOriginal();
		
	},
	
	beforePaymentSaveValidationFailure : function(errors, valid) {
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		checkout.setLoadWaiting(false);
	},

	// Aheadworks One Step Checkout
	awcheckoutPaymentSave : function(callOriginal)
	{
		// Get all form elements
		var fields = {};
		var tmp = '#payment_form_' + this.methodCode;
		var remove = this.methodCode + '[';

		var inputs = $$(tmp + ' input');
		var selects = $$(tmp + ' select');

		inputs.each(function(i)
		{
			if (i.readAttribute('data-cloned-element-id')) {
				i.value = '';
			} else if (i.type != 'hidden' || i.readAttribute('originalElement')) {
				var name = i.name.replace(remove, "");
				name = name.replace("]", "");
				fields[name] = i.value;
				i.value = '';
			}
		});

		selects.each(function(s)
		{
			var name = s.name.replace(remove, "");
			name = name.replace("]", "");
			fields[name] = s.options[s.selectedIndex].value;
			s.selectedIndex = 0;
		});

		// Remove possible error messages that could confuse the
		// customer.
		this.removeErrorMsg();

		callOriginal();

		this.refillPaymentForm(fields);
	},

	awcheckoutPlaceOrder : function(callOriginal)
	{
		if(this.isModulePaymentMethod()) {
			awOSCForm.showOverlay();
			awOSCForm.showPleaseWaitNotice();
			awOSCForm.disablePlaceOrderButton();
			
			this.formValidation(
					function(valid){this.awcheckoutPlaceOrderValidationSuccess(callOriginal)}.bind(this),
					function(errors, valid){this.awcheckoutPlaceOrderValidationFailure(errors,valid)}.bind(this));
			return false;
		}
		callOriginal();
	},
	
	awcheckoutPlaceOrderValidationSuccess : function(callOriginal){
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			
			this.savePaymentInfoInBrowser();
			var parameters = Form.serialize(awOSCForm.form.form, true);
			this.refillPaymentForm(this.formFields);
			new Ajax.Request(awOSCForm.placeOrderUrl, {
				method : 'post',
				parameters : parameters,
				onComplete : function(transport)
				{
					if (transport && transport.responseText) {
						try {
							response = eval('(' + transport.responseText + ')');
						} catch (e) {
							response = {};
						}
						if (response.redirect) {
							this.requestHiddenFields(transport);
							return;
						}

						var msg = response.messages;
						if (typeof (msg) == 'object') {
							msg = msg.join("\n");
						}
						if (msg) {
							alert(msg);
						}
						awOSCForm.enablePlaceOrderButton();
						awOSCForm.hidePleaseWaitNotice();
						awOSCForm.hideOverlay();
					}
				}.bind(this)
			})
		
		} else {
			callOriginal();
		}
	},
	
	awcheckoutPlaceOrderValidationFailure : function(errors, valid){
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		awOSCForm.enablePlaceOrderButton();
		awOSCForm.hidePleaseWaitNotice();
		awOSCForm.hideOverlay();
	},
	

	// GoMage Lightcheckout only
	lightcheckoutBeforePaymentSave : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}
		callOriginal();
		
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
		
	},
	
	// GoMage Lightcheckout only
	lightcheckoutSaveOrder : function(callOriginal)
	{
		if(this.isModulePaymentMethod()) {
			this.formValidation(
					function(valid){this.lightcheckoutSaveOrderValidationSuccess(callOriginal)}.bind(this),
					function(errors, valid){this.lightcheckoutSaveOrderValidationFailure(errors,valid)}.bind(this));
			return false;
		}
		callOriginal();
	},
	
	// GoMage Lightcheckout only
	lightcheckoutSaveOrderValidationSuccess : function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
			var params = checkout.getFormData();
			var request = new Ajax.Request(checkout.save_order_url, {
				method : 'post',
				parameters : params,
				onSuccess : function(transport)
				{
					eval('var response = ' + transport.responseText);

					if (response.redirect) {
						this.requestHiddenFields(transport, checkout.hideLoadinfo.bind(checkout));
						return;
					} else if (response.error) {
						if (response.message) {
							alert(response.message);
						}
					} else if (response.update_section) {
						this.accordion.currentSection = 'opc-review';
						this.innerHTMLwithScripts($('checkout-update-section'), response.update_section.html);

					}
					checkout.hideLoadinfo();

				}.bind(this),
				onFailure : function()
				{

				}
			});
		} else {
			callOriginal();
		}
		
	},
	
	// GoMage Lightcheckout only
	lightcheckoutSaveOrderValidationFailure : function(errors, valid)
	{
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		checkout.hideLoadinfo();
	},
	
	
	firecheckoutSave: function(callOriginal, urlSuffix, forceSave)
	{
		if(this.isModulePaymentMethod()) {
			checkout.setLoadWaiting(true);
			this.formValidation(
					function(valid){this.firecheckoutSaveValidationSuccess(callOriginal, urlSuffix, forceSave)}.bind(this),
					function(errors, valid){this.firecheckoutSaveValidationFailure(errors,valid)}.bind(this));
			return false;
		}
		callOriginal(urlSuffix, forceSave);
	},
	
	firecheckoutSaveValidationSuccess: function(callOriginal, urlSuffix, forceSave) 
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}
		checkout.setLoadWaiting(false);
		callOriginal(urlSuffix, forceSave);

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},
	
	firecheckoutSaveValidationFailure: function(errors, valid) {
		checkout.setLoadWaiting(false);
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
	},

	firecheckoutUpdate: function(callOriginal, url, params)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}

		callOriginal(url, params);

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},

	firecheckoutSetResponse: function(callOriginal, transport)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			try {
	            response = transport.responseText.evalJSON();
	        } catch (err) {
	            alert('An error has occured during request processing. Try again please');
	            checkout.setLoadWaiting(false);
	            $('review-please-wait').hide();
	            return false;
	        }

	        if (response.redirect || response.order_created) {
	        	this.requestHiddenFields(transport);
	        } else {
	        	callOriginal(transport);
	        }
	    } else {
	    	callOriginal(transport);
	    }
	},
	
	iwdSavePayment: function(callOriginal)
	{
		if(this.isModulePaymentMethod()) {
			
			if(! IWD.OPC.saveOrderStatus) {
				if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
					this.savePaymentInfoInBrowser();
				}
				callOriginal();
				if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
					this.refillPaymentForm(this.formFields);
				}
				
			}
			else{
				setTimeout(function(){IWD.OPC.Checkout.showLoader()}, 600);
				IWD.OPC.Checkout.lockPlaceOrder();
				IWD.OPC.Checkout.showLoader();
				this.defaultFormValidation(
						function(valid){this.iwdSavePaymentValidationSuccess(callOriginal)}.bind(this),
						function(errors, valid){this.iwdSavePaymentValidationFailure(errors,valid)}.bind(this));
			}
			return;
		}
		callOriginal();
	},
	
	iwdSavePaymentValidationSuccess: function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}
		callOriginal();
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
		
		
	},
	
	iwdSavePaymentValidationFailure: function(errors, valid)
	{
		
		IWD.OPC.Checkout.hideLoader();
		IWD.OPC.Checkout.unlockPlaceOrder();
		IWD.OPC.saveOrderStatus = false;
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		return;
	},
	
	iwdSaveOrder: function(callOriginal)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}

		callOriginal();

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},
	
	iwdPrepareOrderResponse: function(callOriginal, response)
	{
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
	        if (response.redirect) {
	        	response.success = true;
	        	this.requestHiddenFields(response);
	        } else {
	        	callOriginal(response);
	        }
	    } else {
	    	callOriginal(response);
	    }
	},
	
	magestoreSaveShippingMethod: function(callOriginal, shipping_method_url, update_shipping_payment, update_shipping_review) {
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();
		}

		callOriginal(shipping_method_url, update_shipping_payment, update_shipping_review);

		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.refillPaymentForm(this.formFields);
		}
	},

	magestorePlaceOrder: function(callOriginal, element) {
		if(this.isModulePaymentMethod()) {
			
			element.disabled = true;
			disable_payment();
			$('onestepcheckout-place-order-loading').show();
			$('onestepcheckout-button-place-order').removeClassName('onestepcheckout-btn-checkout');
			$('onestepcheckout-button-place-order').addClassName('place-order-loader');
			
			this.formValidation(
					function(valid){this.magestorePlaceOrderValidationSuccess(callOriginal, element)}.bind(this),
					function(errors, valid){this.magestorePlaceOrderValidationFailure(element, errors,valid)}.bind(this));
			return false;
		}
		
		callOriginal(element);
	},

	magestorePlaceOrderValidationSuccess: function(callOriginal, element){
		if (this.isAuthorization('hidden') || this.isAuthorization('server') || this.isAuthorization('ajax')) {
			this.savePaymentInfoInBrowser();

			

			var form = $('one-step-checkout-form');
			var formUrl = form.readAttribute('action');
			formUrl = formUrl.slice(0, -1) + '/';
			form.writeAttribute('action', 'javascript:void(0);');
			var params = Form.serialize(form);
			this.refillPaymentForm(this.formFields);

			var request = new Ajax.Request(formUrl, {
				method : 'post',
				parameters : params,
				onSuccess : function(transport)
				{
					eval('var response = ' + transport.responseText);

					if (response.success) {
						this.requestHiddenFields(transport, function(){
							$('onestepcheckout-place-order-loading').hide();
							$('onestepcheckout-button-place-order').removeClassName('place-order-loader');
							$('onestepcheckout-button-place-order').addClassName('onestepcheckout-btn-checkout');
							element.disabled = false;
						});
						return;
					} else if (response.error) {
						if (response.message) {
							alert(response.message);
						}
					}
					$('onestepcheckout-place-order-loading').hide();
					$('onestepcheckout-button-place-order').removeClassName('place-order-loader');
					$('onestepcheckout-button-place-order').addClassName('onestepcheckout-btn-checkout');
					element.disabled = false;

				}.bind(this),
				onFailure : function()
				{

				}
			});
		} else {
			callOriginal(element);
		}
	},
	
	magestorePlaceOrderValidationFailure: function(element, errors, valid){
		if(Object.keys(errors).length > 0) {
			alert(errors[Object.keys(errors)[0]]);
		}
		$('onestepcheckout-place-order-loading').hide();
		$('onestepcheckout-button-place-order').removeClassName('place-order-loader');
		$('onestepcheckout-button-place-order').addClassName('onestepcheckout-btn-checkout');
		element.disabled = false;
	},
	
	loadJavascript : function(url, callback)
	{
		var head = document.getElementsByTagName("head")[0] || document.documentElement;
		var script = document.createElement("script");
		script.src = url;

		// Handle Script loading
		var done = false;

		// Attach handlers for all browsers
		script.onload = script.onreadystatechange = function()
		{
			if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
				done = true;
				callback();

				// Handle memory leak in IE
				script.onload = script.onreadystatechange = null;
				if (head && script.parentNode) {
					head.removeChild(script);
				}
			}
		};

		// Use insertBefore instead of appendChild to circumvent an IE6
		// bug.
		// This arises when a base node is used (#2709 and #4378).
		head.insertBefore(script, head.firstChild);

	}
});

if (!Customweb.CheckoutPreload) {
	Customweb.CheckoutPreloadFlag = false;
	Customweb.CheckoutPreload = Class.create({
		initialize : function(onepagePreloadUrl) {
			this.onepagePreloadUrl = onepagePreloadUrl;
	
			if (!Customweb.CheckoutPreloadFlag) {
				this.preloadCheckout();
				Customweb.CheckoutPreloadFlag = true;
			}
		},
	
		hasLoadFailed : function()
		{
			if (typeof customweb_on_load_called == 'undefined') {
				var params = document.URL.toQueryParams();
				if (params.hasOwnProperty('loadFailed')) {
					var loadFailed = params['loadFailed'];
					if (loadFailed != 'undefined' && loadFailed == 'true') {
						return true;
					}
				}
			}
			return false;
		},
	
		preloadCheckout : function()
		{
			var me = this;
			if (this.hasLoadFailed()) {
				if (checkout && checkout.gotoSection) {
					checkout.gotoSection('payment');
	
					if (this.onepagePreloadUrl) {
						checkout.setLoadWaiting('payment');
						new Ajax.Request(this.onepagePreloadUrl, {
							onSuccess : function(transport)
							{
								if (transport && transport.responseText) {
									try {
										response = eval('(' + transport.responseText + ')');
									} catch (e) {
										response = {};
									}
								}
								if (response.update_section) {
									for ( var i = 0; i < response.update_section.length; i++) {
										if ($('checkout-' + response.update_section[i].name + '-load')) {
											$('checkout-' + response.update_section[i].name + '-load').update(response.update_section[i].html);
										}
									}
								}
								me.allowCheckoutSteps('payment');
								checkout.setLoadWaiting(false);
							}
						});
					} else {
						me.allowCheckoutSteps('payment');
					}
				}
			}
		},
	
		allowCheckoutSteps : function(gotoSection)
		{
			for ( var s = 0; s < checkout.steps.length; s++) {
				if (checkout.steps[s] == gotoSection) {
					break;
				}
				if (document.getElementById('opc-' + checkout.steps[s])) {
					document.getElementById('opc-' + checkout.steps[s]).addClassName('allow');
				}
			}
		}
	});
}
