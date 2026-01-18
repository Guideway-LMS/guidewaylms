/**
 * Guideway LMS - AI Assistant Logic
 * Structure: Modular Pattern (IIFE) for Legacy J3 Compability and namespace protection.
 * 
 * Este arquivo gerencia a interação do frontend do chat de IA:
 * - Validação de arquivos (PDF, tamanho)
 * - Exibição de alertas nativos do Joomla
 * - Envio da solicitação (Simulado por enquanto)
 */

var GuidewayAI = (function($) {
    
    // === Configurações e Seletores ===
    var selectors = {
        generateBtn: '#gw-ai-generate-btn',
        fileInput: '#gw-ai-file',
        promptInput: '#gw-ai-prompt',
        loadingContainer: '#div-gw-ai-loading'
    };

    var config = {
        maxFileSize: 5 * 1024 * 1024, // Limite de 5MB
        allowedExtensions: ['pdf']    // Apenas PDF permitido
    };

    // === Métodos Privados ===
    
    /**
     * Exibe alertas usando a API nativa de Mensagens do Joomla.
     * @param {string} message - O texto da mensagem.
     * @param {string} type - O tipo ('success' ou 'error').
     */
    function showAlert(message, type) {
        var msgObj = {};
        
        // Mapeia 'success' para 'message' (que é o verde padrão do Joomla)
        // 'error' continua 'error' (vermelho)
        var joomlaType = type === 'error' ? 'error' : 'message'; 
        
        msgObj[joomlaType] = [message];
        
        // Renderiza a mensagem no container padrão do sistema
        Joomla.renderMessages(msgObj);
        
        // Rola a página para o topo para garantir que o usuário veja o alerta
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * Limpa as mensagens de alerta existentes.
     */
    function clearAlerts() {
        Joomla.removeMessages();
    }

    /**
     * Valida o arquivo selecionado pelo usuário.
     * Verifica a extensão (.pdf) e o tamanho (max 5MB).
     * @returns {boolean} True se válido ou nenhum arquivo, False se inválido.
     */
    function validateFile() {
        var fileInput = $(selectors.fileInput)[0];
        
        // Se nenhum arquivo foi selecionado, passa (pois é opcional se houver texto)
        if (fileInput.files.length === 0) return true; 

        var file = fileInput.files[0];
        var fileName = file.name;
        var fileSize = file.size;
        var fileExt = fileName.split('.').pop().toLowerCase();

        // Verifica extensão
        if ($.inArray(fileExt, config.allowedExtensions) === -1) {
            showAlert('Formato inválido. Apenas arquivos PDF são permitidos.', 'error');
            return false;
        }

        // Verifica tamanho
        if (fileSize > config.maxFileSize) {
            showAlert('Arquivo muito grande. O tamanho máximo permitido é 5MB.', 'error');
            return false;
        }

        return true;
    }

    /**
     * Lida com o clique no botão "Gerar Descrição".
     * Centraliza a validação e fluxo de envio.
     */
    function handleGenerate() {
        clearAlerts();

        // 1. Valida o arquivo primeiro
        if (!validateFile()) return;

        var prompt = $(selectors.promptInput).val();
        var hasFile = $(selectors.fileInput)[0].files.length > 0;

        // 2. Valida se há pelo menos um input (Texto OU Arquivo)
        if (prompt.trim() === '' && !hasFile) {
            showAlert('Por favor, descreva a atividade no campo de texto ou anexe um PDF para continuar.', 'error');
            return;
        }

        // 3. Sucesso (Simulado)
        console.log('Validação OK. Iniciando processamento...', { prompt: prompt, file: hasFile });
        showAlert('Sua solicitação está sendo processada... (Simulação)', 'success');
        
        // Simula um loading visual (se o container existir)
        if($(selectors.loadingContainer).length) {
             $(selectors.loadingContainer).show();
             setTimeout(function() { $(selectors.loadingContainer).hide(); }, 2000);
        }
    }

    /**
     * Registra os ouvintes de eventos do DOM.
     */
    function bindEvents() {
        $(document).on('click', selectors.generateBtn, handleGenerate);
        
        // Revalida automaticamente ao trocar o arquivo
        $(document).on('change', selectors.fileInput, function() {
            clearAlerts();
            validateFile();
        });
    }

    // === Public API ===
    return {
        /**
         * Inicializa o módulo GUID EWAY AI.
         */
        init: function() {
            bindEvents();
            console.log('GuidewayAI Refactored Init');
        }
    };

})(jQuery);

// Inicializa quando o documento estiver pronto
jQuery(document).ready(function() {
    GuidewayAI.init();
});
