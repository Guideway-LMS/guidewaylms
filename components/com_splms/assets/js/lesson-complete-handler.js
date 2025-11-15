jQuery(function ($) {
    'use strict';

    $(document).on('click', '#splms-completed-item', function (event) {
        event.preventDefault();

        var $this = $(this);
        var form = $('#splms-completed-item-form');

        var item_id = form.find('input[name="item_id"]').val();
        var item_type = form.find('input[name="item_type"]').val();

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_splms&task=lesson.completeditem',
            data: {
                item_id: item_id,
                item_type: item_type
            },
            beforeSend: function () {
                $this.prop('disabled', true).text(Joomla.JText._('COM_SPLMS_LOADING') || '...');
            },
            success: function (response) {
                var data = $.parseJSON(response);

                if (data.status) {
                    $this.text(data.content).removeClass('btn-primary').addClass('btn-success');
                } else {
                    alert(data.content);
                    $this.prop('disabled', false);
                }
            },
            error: function () {
                alert('Erro ao enviar a solicitação.');
                $this.prop('disabled', false);
            }
        });
    });
});
