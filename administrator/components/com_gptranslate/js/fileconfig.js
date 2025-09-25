/**
 * Import/export configuration file utility
 * 
 * @package GPTRANSLATE::CONFIG::administrator::components::COM_GPTRANSLATE
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2024 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var FileConfig = function() {
		/**
		 * Snippet to append for file uploader
		 * 
		 * @access private
		 * @var String
		 */
		var uploaderSnippet ='<div id="uploadrow" class="config-import" style="display: none;">' +
								'<span class="input-group">' +
									'<span class="input-group-text"><span class="fas fa-upload" aria-hidden="true"></span> ' + COM_GPTRANSLATE_PICKFILE + '</span>' +
									'<input type="file" class="form-control" id="configurationimport" name="configurationimport" value="">' +
								'</span>' +
								'<button class="btn btn-primary btn-sm" id="startimport">' + COM_GPTRANSLATE_STARTIMPORT + '</button> ' +
								'<button class="btn btn-primary btn-sm" id="cancelimport">' + COM_GPTRANSLATE_CANCELIMPORT + '</button>' +
							'</div>';
		
		/**
		 * Function dummy constructor
		 * 
		 * @access private
		 * @param String
		 *            contextSelector
		 * @method <<IIFE>>
		 * @return Void
		 */
		(function __construct() {
			// Remove predefined Joomla behavior
			$('#toolbar-upload').removeAttr('task');
			$('#toolbar-upload button').removeAttr('onclick');
			
			// Append uploader row
			$('#uploadrow').remove();
			$('#adminForm #tab_configuration, #adminForm table.headerlist').before(uploaderSnippet)
			
			// Attach custom feature
			$('#toolbar-upload button').removeAttr('onclick').on('click', function(jqEvent){
				jqEvent.preventDefault();
			
				// Append uploader row
				$('#uploadrow').slideDown();
				
				return false;
			});
			
			// Bind the uploader button
			$('#startimport').on('click', function(jqEvent){
				// Validate input
				var fileInput = $('#configurationimport');
				var targetButton = $('#cancelimport');
				if(!fileInput.val()) {
					targetButton.next('span.validation.bg-danger').remove();
					targetButton.after('<span class="validation badge bg-danger">' + COM_GPTRANSLATE_REQUIRED + '</span>');
					fileInput.css('border', '1px solid #F00');
					fileInput.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc');
						targetButton.next('span.validation.bg-danger').remove();
					});
					return false;
				}
				
				// Change the task and submit miniform uploader
				var currentMvcCore = $('#adminForm input[name=task]').val().split('.');
				
				$('#adminForm').attr('enctype', 'multipart/form-data');
				$('#adminForm input[name=task]').val(currentMvcCore[0] + '.importConfig');
				$('#adminForm').trigger('submit');
			});
			
			// Bind the file select event to the label display name
			$('#configurationimport').on('change', function(jqEvent){
				var selectedFileName = $(this).get(0).files.item(0).name;
				$(this).next('label.custom-file-label').text(selectedFileName);
			});
			
			// Cancel upload operation
			$('#cancelimport').on('click', function(jqEvent){
				jqEvent.preventDefault();
				$('#uploadrow').slideUp();
				
				return false;
			});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.GPTranslateFileConfig = new FileConfig();
	});
})(jQuery);