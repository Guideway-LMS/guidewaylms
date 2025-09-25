// Turn radios into btn-group
jQuery(function($) {
	/**
	 * Turn radios into btn-group
	 */
	var container = document.querySelectorAll('.btn-group');
	for (var i = 0; i < container.length; i++) {
		var labels = container[i].querySelectorAll('label');
		for (var j = 0; j < labels.length; j++) {
			labels[j].classList.add('btn');
			var inputValue = $('input[type=radio]', labels[j]).val(); 
			if ((j % 2) == 1 && inputValue !== '') {
				labels[j].classList.add('btn-outline-danger');
			} if ((j % 2) == 1 && inputValue === '') {
				labels[j].classList.add('btn-outline-primary');
			} else {
				labels[j].classList.add('btn-outline-success');
			}
		}
	}

	var btsGrouped = document.querySelectorAll('.btn-group input[checked=checked]');
	for (var i = 0, l = btsGrouped.length; l>i; i++) {
		var self   = btsGrouped[i],
		    attrId = self.id,
		    label = document.querySelector('label[for=' + attrId + ']');
		if (self.parentNode.parentNode.classList.contains('btn-group-reversed')) {
			if (self.value === 0) {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-success');
			} else {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-danger');
			}
		} else {
			if (self.value === 0) {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-danger');
			} else {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-primary');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				}
			}
		}
	}
	
	// Perform columns ordering
	$('a[data-ordering-form]').on('click', function(jqEvent){
		let orderingOrder = $(jqEvent.target).attr('data-ordering-order');
		let orderingDirection = $(jqEvent.target).attr('data-ordering-direction');
		let orderingTask = $(jqEvent.target).attr('data-ordering-task');
		Joomla.tableOrdering(orderingOrder, orderingDirection, orderingTask);
		return false;
	});
	
	// Always ensure to reset the other switcher button
	$(document).on('click', "fieldset[data-bs-toggle=buttons] label.btn", function(jqEvent) {
		if(jqEvent.target.nodeName.toUpperCase() == 'INPUT' || $(jqEvent.target).attr('disabled')) {
			return true;
		}
		
		var label = $(jqEvent.target).addClass('active');
		var input = $('input[type=radio]', label);

		var otherLabel = label.parents('fieldset').find("label").not(label);
		if (otherLabel.hasClass('active')) {
			otherLabel.removeClass('active btn-success btn-danger btn-primary');
		}
	});

	// Override the default switcher button colors/class for multiple selection switcher buttons
	var multipleSwitchers = $("div.controls > fieldset > label:nth-child(3)");
	multipleSwitchers.each(function(index, elem){
		var parentContainer = $(elem).parent();
		$('label', parentContainer).removeClass('btn-outline-success btn-outline-danger')
		// We are not in the configuration view
		if($('label:first-child > input', parentContainer).val() == 0) {
			$('label', parentContainer).addClass('btn-outline-success');
			$('label:first-child', parentContainer).addClass('btn-outline-danger').removeClass('btn-outline-success');
		} else {
			$('label', parentContainer).addClass('btn-outline-success');
		}
	});
	
	// Ensure that only input labels with 'No' value will be dangered 
	var doubledSwitchers = $("div.controls > fieldset > label:nth-child(2)");
	doubledSwitchers.each(function(index, elem){
		var inputValue = $('input', elem).val();
		if(inputValue != 0 && inputValue != '' && inputValue != '') {
			$(elem).removeClass('btn-outline-danger').addClass('btn-outline-success');
		}
	});
	
	/**
	 * Enables bootstrap popover
	 */
	[].slice.call(document.querySelectorAll('#updatestatus label.hasPopover, #checker_start')).map(function (popoverEl) {
		let popoverInstance = new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'right',
			html : true
		});
		return popoverInstance;
	});
	[].slice.call(document.querySelectorAll('a.hasPopover.google, span.hasPopover.google')).map(function (popoverEl) {
		let popoverInstance = new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'left',
			html : true
		});
		return popoverInstance;
	});
	[].slice.call(document.querySelectorAll('label.hasPopover, button.hasPopover, div.hasPopover, span.hasPopover, img.hasPopover')).map(function (popoverEl) {
		let popoverInstance = new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'top',
			html : true
		});
		return popoverInstance;
	});
	[].slice.call(document.querySelectorAll('thead a.hasPopover')).map(function (popoverEl) {
		return new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'top',
			html : true
		});
	});
	
	/**
	 * Enables bootstrap tooltip
	 */
	[].slice.call(document.querySelectorAll('label.hasTooltip, img.hasTooltip, a.hasTooltip, span.hasTooltip, a.hasTip, a.hasTip, *[rel=tooltip], a.page-link')).map(function (tooltipEl) {
		let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
			trigger : 'hover',
			placement : 'top',
			html : true
		});
		
		return tooltipInstance;
	});
	
	/**
	 * Remove custom select
	 */
	$('table.headerlist select').removeClass('form-select');
	
	/**
	 * Calendars
	 */
	if ($.datepicker) {
		$('input[data-role=calendar]').datetimepicker({
			dateFormat : 'yy-mm-dd',
			timeFormat: 'HH:mm:ss',
			firstDay : 1
		}).prev('span').on('click', function() {
			$(this).datetimepicker('show');
		});
	}

	// Remove configuration spacer empty div
	$('span.spacer').parent('div.control-label').next('div.controls').remove();
	
	/**
	 * Accordion panels local storage memoize and set open
	 */
	var defaultAccordionObject = {
		'gptranslate_accordion_cpanel' : 'gptranslate_stats'
	};
	
	// To store the last clicked dropdown-toggle button
	let lastClickedDropdown = null;

	// Listen for Bootstrap's dropdown events and exclude Joomla dropdown-toggle
	document.querySelectorAll('button.dropdown-toggle').forEach(function (element) {
	    element.addEventListener('show.bs.dropdown', function (targetElement) {
	    	// Set the clicked dropdown button before it triggers collapse
	    	lastClickedDropdown = this;
	    });
	    element.addEventListener('hide.bs.dropdown', function (targetElement) {
	    	// Set the clicked dropdown button before it triggers collapse
	        lastClickedDropdown = this;
	    });
	});
	
	[].slice.call(document.querySelectorAll('div.accordion')).map(function (accordionEl) {
		accordionEl.addEventListener('shown.bs.collapse', function(event) {
			if (!$(event.target).hasClass('card-block')) {
				return;
			}
			event.stopPropagation();
			
			// Trigger window resize to repaint the chart
			if ($(event.target).hasClass('accordion-chart')) {
				$(window).trigger('resize');
			}
			
			
			var localStorageAccordion = $.jStorage.get('gptranslateAccordionOpened', defaultAccordionObject);
			localStorageAccordion[this.id] = event.target.id;
			$.jStorage.set('gptranslateAccordionOpened', localStorageAccordion);
	
			// Scroll to accordion header if needed
			if (document.body.scrollHeight > window.innerHeight) {
				$('html, body').animate({
					scrollTop : parseInt($("#" + event.target.id).prev().offset().top) - 185
				}, 500);
			}
			// Add open state
			$(event.target).prev().addClass('opened');
		});
		
		accordionEl.addEventListener('hide.bs.collapse', function(event) {
			// Check if the Joomla dropdown-toggle has been clicked and prevent collapsing 
			if(lastClickedDropdown) {
				lastClickedDropdown = null;
				event.stopPropagation();
				event.preventDefault();
				return false;
			}
			
			if (!$(event.target).hasClass('card-block')) {
				return;
			}
			event.stopPropagation();
			
			// Remove open state
			$(event.target).prev().removeClass('opened');
		});
	});
	

	$.each($.jStorage.get('gptranslateAccordionOpened', defaultAccordionObject), function(namespace, element) {
		if ($('#' + element, '#' + namespace).length) {
			$('#' + element, '#' + namespace).addClass('show').prev().addClass('opened');
		}
	});


	/**
	 * Tab panels local storage memoize and set open
	 */
	var defaultTabObject = {
		'tab_configuration' : 'translator'
	};
	[].slice.call(document.querySelectorAll('#adminForm .nav.nav-tabs')).map(function (tabEl) {
		tabEl.addEventListener('shown.bs.tab', function(event) {
			var localStorageTab = $.jStorage.get('gptranslateTabOpened', defaultTabObject);
			localStorageTab[this.id] = $(event.target).data('element');
			$.jStorage.set('gptranslateTabOpened', localStorageTab);
			
			// Add accessibility ARIA
			$('li.nav-item', this).removeAttr('aria-selected').attr('aria-selected', 'false');
			$('li.nav-item', this).removeAttr('tabindex').attr('tabindex', -1);
			$(event.target).parent('li').attr({'aria-selected':'true', 'tabindex':0});
			
			// Ensure that the label input checked will be active
			$("fieldset[data-bs-toggle=buttons] > label.btn > input:checked").each(function(i, element) {
				var parentLabel = $(element).parent('label.btn');
				if (!parentLabel.hasClass('active')) {
					parentLabel.addClass('active');
				}
			});
		});
	});

	// Parse query string to search if any anchor force tab opening
	var hashQueryString = window.location.hash.substr(2);
	if (hashQueryString) {
		var nodeElement = document.querySelector('ul.nav.nav-tabs li a[data-element=' + hashQueryString + ']');
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
	}

	if (hashQueryString == 'licensepreferences') {
		var nodeElement = document.querySelector('a[data-element=translator]');
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
		$('#params_registration_email-lbl').css('color', 'red');
		$('#params_registration_email').css('border', '2px solid red');
	}

	$.each($.jStorage.get('gptranslateTabOpened', defaultTabObject), function(namespace, element) {
		var nodeElement = $('a[data-element=' + element + ']', '#' + namespace).get(0);
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
	});
	
	// Slide down and hide advanced controls
	var translationEngine = $('select[name=params\\[google_translate_engine\\]]');
	if(translationEngine.val() == 1) {
		$('*.chatgpt_ctrl').parents('div.control-group').hide();
	} else {
		$('#params_chatgpt_apikey').attr('data-validation', 'required');
	}
	$(translationEngine).on('change', function(){
		var selectValue = $(this).val();
		if(selectValue == 0) {
			$('*.chatgpt_ctrl').parents('div.control-group').slideDown();
			$('#params_chatgpt_apikey').attr('data-validation', 'required');
		} else {
			$('*.chatgpt_ctrl').parents('div.control-group').slideUp();
			$('#params_chatgpt_apikey').removeAttr('data-validation');
		}
	});
	
	$('input.field-media-input').each(function(index, elem){
	    $(elem).css('visibility','hidden');
	});
	// Observe media field image selection change
	$('div.field-media-preview').each(function(index, elem){
		// Create an observer instance for each element to observe
		var observer = new MutationObserver(function(mutations) {
			var image = $('img', elem);
			if(image.length) {
				let relatedInputField = $(elem).next('div').find('input.field-media-input');
				relatedInputField.val(relatedInputField.val().split('#')[0]);
			}
		});
		observer.observe(elem, { childList: true });
	});
	setTimeout(function(){
	    $('input.field-media-input').each(function(index, elem){
    		elem.value = elem.value.split('#')[0];
    		$(elem).css('visibility','visible');
	    }); 
	}, 300);
	
	// Add or remove rows for translations only in edit translations view
	if($('body').is('.com_gptranslate.task-editEntity')) {
		var snippetHtml = `<div class="translation-row">
			<label class="badge bg-primary">${COM_GPTRANSLATE_ORIGINAL_TEXT}</label>
			<textarea name="translations[original][]" data-role="original" data-validation="required" aria-required="true"></textarea>
			<label class="badge bg-primary">${COM_GPTRANSLATE_TRANSLATED_TEXT}</label>
			<textarea name="translations[translated][]" data-role="translated" data-validation="required" aria-required="true"></textarea>
			<button type="button" class="group-remove btn btn-sm btn-danger" aria-label="${COM_GPTRANSLATE_DELETE}">
			<span class="icon-minus" aria-hidden="true"></span> ${COM_GPTRANSLATE_DELETE}
			</button>
			<button type="button" class="group-sync btn btn-sm btn-warning btn-invisible hasPopover" data-bs-title="${COM_GPTRANSLATE_SYNC_TITLE}" data-bs-content="${COM_GPTRANSLATE_SYNC_DESC}" aria-label="${COM_GPTRANSLATE_SYNC}">
			<span class="icon-refresh" aria-hidden="true"></span> ${COM_GPTRANSLATE_SYNC}
			</button>
			</div>`
			$('button.group-add-start').on('click', function(jqEvent){
				$(this).after(snippetHtml);
				[].slice.call(document.querySelectorAll('button.hasPopover')).map(function (popoverEl) {
					let popoverInstance = new bootstrap.Popover(popoverEl,{
						template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
						trigger : 'hover',
						placement : 'top',
						html : true
					});
					return popoverInstance;
				});
			});
		
		$(document).on('click', 'button.group-remove', function(jqEvent){
			$(this).parents('div.translation-row').remove();
		});
		
		$(document).on('click', 'button.group-sync', function(jqEvent){
			var currentRow = $(event.target).parents('div.translation-row');
			var originalRowText = $('textarea[data-role="original"]', currentRow).val();
			var translatedRowText = $('textarea[data-role="translated"]', currentRow).val();
			var clickedSyncButton = this;
			var clickedSyncButtonIcon = $('span.icon-refresh', this);
			clickedSyncButtonIcon.addClass('icon-working');
			
			// Object to send to server
			var ajaxparams = {
			    idtask : 'syncTranslation',
			    template : 'json',
			    DIModels : [{'modelside':1,'modelname':'Translations'}],
			    param : {
			        'original' : originalRowText,
			        'translated' : translatedRowText,
			        'languagetranslated' : $('#languagetranslated').val()
			    }
			};
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Async request
			var syncPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_gptranslate&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					// Check response HTTP status code
					defer.resolve(data, jqXHR.status, jqXHR.getAllResponseHeaders());
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					defer.resolve(null, jqXHR.status + ' ' + errorThrown);
				});
			}).promise();

			syncPromise.then(function(responseData, status, headers) {
				if(status == 200 && responseData.result) {
					$(clickedSyncButton).after('<label class="gpt-synclabel badge bg-success">' + COM_GPTRANSLATE_SYNC_COMPLETED + '</label>');
				} else {
					$(clickedSyncButton).after('<label class="gpt-synclabel badge bg-danger">' + COM_GPTRANSLATE_SYNC_ERROR + '</label>');
				}
			}).always(function(){
				$(clickedSyncButton).addClass('btn-invisible');
				clickedSyncButtonIcon.removeClass('icon-working');
				
				setTimeout(function(){
					$('label.gpt-synclabel').remove();
				}, 2000)
			});
		});
		
		// Bind an event handler for editing of translation rows
		$(document).on('keyup', 'textarea[data-role="translated"]', function(jqEvent){
			var currentRow = $(event.target).parents('div.translation-row');
			$('button.group-sync', currentRow).removeClass('btn-invisible');
		});
		
		$(document).on('click', 'button[data-role="search-translations"]', function(jqEvent){
			const searchWord = $('div.translations-container #search').val();
			const translationsRow = $('div.translation-row');
			const searchRegex = new RegExp(searchWord, 'i');

			$.each(translationsRow, function(index, row){
				let originalTextareaText = $('textarea[data-role="original"]', row).val();
		        let translatedTextareaText = $('textarea[data-role="translated"]', row).val();
				
				if (searchRegex.test(originalTextareaText) || searchRegex.test(translatedTextareaText)) {
					$(row).removeClass('translation-row-hidden');
				} else {
					$(row).addClass('translation-row-hidden');
				}
			});
		});
		
		$(document).on('click', 'button[data-role="reset-search"]', function(jqEvent){
			$('div.translations-container #search').val('');
			$('div.translation-row').removeClass('translation-row-hidden');
		});
	}
});