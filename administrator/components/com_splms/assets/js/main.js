/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2020 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

(function ($) {
    $(document).on('click', '#splms-remove-attachment', function(event) {
        event.preventDefault()
        var $this = $(this);
        var $target = $('#splms-remove-attachment');
        var $attachment_file = $target.attr('data-file');
        var $lessonId = $target.attr('data-id');

        if (confirm('Are you sure want to delete this attachtment file?') == true) {
          var formToken = '';
          if (typeof Joomla !== 'undefined' && Joomla.getOptions) {
              var jopt = Joomla.getOptions('csrf.token');
              if (jopt) formToken = jopt;
          }
          if(!formToken) {
              var tokenInput = $('form#adminForm input[type="hidden"]').filter(function() { return this.name.length === 32 && this.value === '1'; });
              if(tokenInput.length) formToken = tokenInput.attr('name');
          }
          
          var postData = {filePath: $attachment_file, itemId: $lessonId};
          if (formToken) postData[formToken] = '1';

          $.ajax({
            type: "POST",
            url: 'index.php?option=com_splms&task=lesson.delete_media',
            data: postData,
            beforeSend: function() {
                $('<p id="attachment-before-delete" class="text-center" style="max-width: 100%; width: 260px;">Please wait ...<strong>').insertBefore($('#splms-attachment-file'));
            },
            success: function(response) {
            var data = $.parseJSON(response);
                if (data.status) {
                    $('<p class="alert alert-success text-center" style="max-width: 100%; width: 260px;">'+ data.message +'<strong>').insertBefore($('#splms-attachment-file'));

                    $('#splms-attachment-file, #splms-remove-attachment, #attachment-before-delete').remove();
                    
                    //$('#splms-attachment-file').apend(data.message)
                } else{
                    $('<p class="alert alert-error text-center" style="max-width: 100%; width: 260px;">'+ data.message +'<strong>').insertBefore($('#splms-attachment-file'));

                    $('#splms-attachment-file, #splms-remove-attachment, #attachment-before-delete').remove();
                }
            }
          })
        }

    })
})(jQuery)