/**
 * Sistema de Progresso de Aulas
 * GUIDEWAY CUSTOM - 2025-11-11 - Joshua (Grupo 1)
 */

/**
 * Marca uma lição como concluída
 * @param {number} lessonId - ID da lição
 */
function completedItem(lessonId) {
    const userId = document.querySelector('input[name="user_id"]')?.value;
    
    if (!userId || !lessonId) {
        console.error('❌ Dados inválidos:', {userId, lessonId});
        alert('Erro: Dados inválidos');
        return;
    }
    
    console.log('📤 Marcando aula como concluída...', {userId, lessonId});
    
    jQuery.ajax({
        url: 'index.php?option=com_splms&task=lesson.completeditem',
        type: 'POST',
        data: {
            user_id: userId,
            item_id: lessonId,
            item_type: 'lesson'
        },
        dataType: 'json',
        success: function(response) {
            console.log('✅ Resposta recebida:', response);
            
            if (response.status) {
                console.log('✅ Aula marcada como concluída!');
                
                // Atualizar UI do botão
                updateButtonState(true);
                
                // Mostrar mensagem de sucesso
                if (response.content) {
                    alert(response.content);
                }
                
            } else {
                console.error('❌ Erro:', response.content);
                alert('Erro: ' + response.content);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Erro AJAX:', {xhr, status, error});
            console.log('Response:', xhr.responseText);
            alert('Erro ao marcar aula como concluída');
        }
    });
}

/**
 * Verifica se a lição já está concluída
 * @param {number} lessonId - ID da lição
 * @param {function} callback - Função callback (opcional)
 */
function hasCompleted(lessonId, callback) {
    const userId = document.querySelector('input[name="user_id"]')?.value;
    
    if (!userId || !lessonId) {
        console.error('❌ Dados inválidos:', {userId, lessonId});
        if (callback) callback(false);
        return;
    }
    
    console.log('🔍 Verificando status da aula...', {userId, lessonId});
    
    jQuery.ajax({
        url: 'index.php?option=com_splms&task=lesson.hascompleted',
        type: 'GET',
        data: {
            user_id: userId,
            lesson_id: lessonId
        },
        dataType: 'json',
        success: function(response) {
            console.log('✅ Status verificado:', response);
            
            const isCompleted = response.completed === true;
            
            // Atualizar UI baseado no status
            updateButtonState(isCompleted);
            
            // Executar callback se fornecido
            if (callback) {
                callback(isCompleted);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Erro ao verificar status:', {xhr, status, error});
            if (callback) {
                callback(false);
            }
        }
    });
}

/**
 * Atualiza o estado visual do botão
 * @param {boolean} completed - Se está concluída ou não
 */
function updateButtonState(completed) {
    const btn = jQuery('#splms-completed-item');
    
    if (!btn.length) {
        console.warn('⚠️ Botão não encontrado');
        return;
    }
    
    if (completed) {
        btn.text('✓ Concluída');
        btn.addClass('completed btn-success');
        btn.removeClass('btn-primary');
        btn.prop('disabled', true);
        btn.css('cursor', 'not-allowed');
        console.log('✅ Botão atualizado: Concluída');
    } else {
        btn.text('Completar');
        btn.removeClass('completed btn-success');
        btn.addClass('btn-primary');
        btn.prop('disabled', false);
        btn.css('cursor', 'pointer');
        console.log('📝 Botão atualizado: Não concluída');
    }
}

/**
 * Inicialização ao carregar a página
 */
jQuery(document).ready(function() {
    console.log('🚀 Sistema de Progresso de Aulas carregado');
    
    const lessonId = jQuery('input[name="item_id"]').val();
    
    if (lessonId) {
        console.log('📚 Aula detectada:', lessonId);
        
        // Verificar se já está concluída ao carregar
        hasCompleted(lessonId);
        
        // Vincular evento de click ao botão
        jQuery('#splms-completed-item').on('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Botão clicado');
            completedItem(lessonId);
        });
        
        console.log('✅ Event listener adicionado ao botão');
    } else {
        console.warn('⚠️ ID da lição não encontrado');
    }
});