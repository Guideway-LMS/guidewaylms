/**
 * Guideway LMS - AI Assistant Logic
 * Structure: Modular Pattern (IIFE) for Legacy J3 Compability and namespace protection.
 * 
 * Este arquivo gerencia a interação do frontend do chat de IA:
 * - Validação de arquivos (PDF, tamanho)
 * - Exibição de alertas nativos do Joomla
 * - Envio da solicitação (Simulado por enquanto)
 */

var GuidewayAI = (function ($) {

    // === Configurações e Seletores ===
    var selectors = {
        generateBtn: '#gw-ai-generate-btn',
        fileInput: '#gw-ai-file',
        promptInput: '#gw-ai-prompt',
        loadingContainer: '#div-gw-ai-loading',
        settingsBtn: '#gw-ai-quiz-settings-btn',
        quizDropdown: '#gw-ai-quiz-dropdown',
        quizSubmitBtn: '#gw-ai-quiz-submit-btn',
        difficulty: '#gw-ai-difficulty',
        qcount: '#gw-ai-qcount',
        qcount: '#gw-ai-qcount',
        qcount: '#gw-ai-qcount',
        qtype: '#gw-ai-qtype',
        fileName: '#gw-ai-file-name',
        dropZone: '#gw-ai-drop-zone'
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

        var joomlaType;
        if (type === 'error') {
            joomlaType = 'error';
        } else if (type === 'warning') {
            joomlaType = 'warning';
        } else {
            joomlaType = 'message';
        }

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
     * Valida os arquivos selecionados pelo usuário.
     * Verifica a extensão (.pdf) e o tamanho (max 5MB por arquivo).
     * @returns {boolean} True se válido ou nenhum arquivo, False se inválido.
     */
    function validateFile() {
        var fileInput = $(selectors.fileInput)[0];

        // Se nenhum arquivo foi selecionado, passa (pois é opcional se houver texto)
        if (fileInput.files.length === 0) return true;

        for (var i = 0; i < fileInput.files.length; i++) {
            var file = fileInput.files[i];
            var fileName = file.name;
            var fileSize = file.size;
            var fileExt = fileName.split('.').pop().toLowerCase();

            // Verifica extensão
            if ($.inArray(fileExt, config.allowedExtensions) === -1) {
                showAlert('Formato inválido. Apenas arquivos PDF são permitidos ("' + fileName + '").', 'error');
                return false;
            }

            // Verifica tamanho
            if (fileSize > config.maxFileSize) {
                showAlert('Arquivo muito grande ("' + fileName + '"). O tamanho máximo permitido por arquivo é 5MB.', 'error');
                return false;
            }
        }

        return true;
    }

    /**
     * Converte o retorno JSON da IA em HTML formatado legível.
     * @param {string|array} jsonData - Tenta processar o retorno que deveria ser Array/JSON.
     * @returns {string} HTML final montado
     */
    function formatQuizToHTML(jsonData) {
        var questions = [];
        try {
            questions = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        } catch (e) {
            console.error('Erro ao parsear JSON do Quiz', e);
            // Fallback para exibir o texto bruto se não for json
            return '<p>O conteúdo gerado não é um formato de quiz padrão.</p><pre>' + jsonData + '</pre>';
        }

        if (!Array.isArray(questions)) {
            return '<p>O formato das questões retornado pela IA está incorreto.</p>';
        }

        // Detecta tipo de questão baseado na primeira entrada (aproximação para exibir os formatos corretos)
        var isEssay = (questions.length > 0 && typeof questions[0].answer !== 'undefined' && typeof questions[0].options === 'undefined');
        var isMultipleChoice = (questions.length > 0 && typeof questions[0].options !== 'undefined');

        var html = '<div class="gw-quiz-container">';
        html += '<h3>Questões sobre o Conteúdo</h3>';

        var gabarito = [];

        $.each(questions, function (i, q) {
            var num = i + 1;
            html += '<div class="gw-quiz-question" style="margin-bottom: 20px;">';
            html += '<h4>' + num + '. ' + q.question + '</h4>';

            if (isMultipleChoice && q.options && q.options.length) {
                // Múltipla Escolha
                html += '<ul>';
                var letters = ['A', 'B', 'C', 'D', 'E'];

                $.each(q.options, function (j, opt) {
                    var letter = letters[j] || '?';
                    html += '<li style="margin-bottom: 5px;"><strong>' + letter + ')</strong> ' + opt + '</li>';
                });
                html += '</ul>';

                if (q.answer) {
                    gabarito.push('<strong>Q' + num + ':</strong> ' + q.answer);
                }
            } else {
                // Dissertativa
                if (q.answer) {
                    gabarito.push('<strong>Q' + num + ' - Expectativa de Resposta:</strong><br>' + q.answer);
                }
            }
            html += '</div>';
        });

        if (gabarito.length > 0) {
            html += '<div class="gw-quiz-gabarito" style="margin-top: 30px; padding-top: 20px; border-top: 2px dashed #ccc;">';
            html += '<h3>Gabarito Esperado</h3>';
            $.each(gabarito, function (k, item) {
                html += '<div style="margin-bottom: 15px; padding: 10px; border-left: 4px solid #4CAF50;">' + item + '</div>';
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }
    /**
     * Lida com o clique no botão "Gerar Descrição" ou "Gerar Questão".
     * Centraliza a validação e fluxo de envio.
     * @param {boolean} forceQuiz - Se true, força o modo questão ignorando outros estados.
     */
    function handleGenerate(forceQuiz) {
        // Garantir booleano
        var isQuizMode = (forceQuiz === true);

        // 1. Valida o arquivo primeiro
        if (!validateFile()) return;

        var prompt = $(selectors.promptInput).val();
        var hasFile = $(selectors.fileInput)[0].files.length > 0;

        // Se for Questão, ao menos um arquivo é obrigatório
        if (isQuizMode && !hasFile) {
            showAlert('Para gerar uma Questão, é obrigatório anexar pelo menos um arquivo PDF.', 'error');
            return;
        }

        // 2. Valida se há pelo menos um input (Texto OU Arquivos)
        if (prompt.trim() === '' && !hasFile) {
            showAlert('Por favor, descreva a atividade no campo de texto ou anexe Pdfs para continuar.', 'error');
            return;
        }

        // 3. Preparação do Payload
        var formData = new FormData();
        var csrfToken = Joomla.getOptions('csrf.token');

        formData.append(csrfToken, 1); // CSRF Token
        formData.append('option', 'com_splms');
        formData.append('task', 'lesson.uploadPDF');

        // Adiciona arquivos se existirem (Pode ser array de arquivos)
        if (hasFile) {
            var files = $(selectors.fileInput)[0].files;
            for (var i = 0; i < files.length; i++) {
                formData.append('gw_ai_file[]', files[i]);
            }
        }

        // Adiciona parâmetros de Questão ou Prompt
        if (isQuizMode) {
            formData.append('gw_ai_difficulty', $(selectors.difficulty).val());
            formData.append('gw_ai_qcount', $(selectors.qcount).val());
            formData.append('gw_ai_qtype', $(selectors.qtype).val());

            // Verifica o toggle de inclusão de descrição
            var includeDesc = $('input[name="gw_ai_include_desc_radio"]:checked').val() || "0";
            formData.append('gw_ai_include_desc', includeDesc);

        } else if (prompt.trim() !== '') {
            formData.append('gw_ai_prompt', prompt);
        }

        // UI Loading
        // Decidir qual botão mostrar loading
        var $btn = isQuizMode ? $(selectors.quizSubmitBtn) : $(selectors.generateBtn);
        var originalBtnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="icon-loop spinner"></span> ...');
        if ($(selectors.loadingContainer).length) $(selectors.loadingContainer).show();

        // 4. Envio AJAX
        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    var content = response.data || '';
                    var descHtml = response.data_desc || '';
                    console.log('PDF Processado com sucesso');

                    // Se o modo ativado for de gerar questões, formatamos o retorno JSON para HTML bonito.
                    if (isQuizMode) {
                        content = formatQuizToHTML(content);

                        // Prepends description if requested and generated
                        if (descHtml !== '') {
                            content = descHtml + content;
                        }
                    }
                    // Integração com o Editor TinyMCE
                    if (window.tinymce && tinymce.activeEditor) {
                        tinymce.activeEditor.setContent(content);
                        var successMsg = response.message ? response.message : (isQuizMode ? 'Questões geradas e inseridas no editor!' : 'Conteúdo importado com sucesso!');

                        // Se houver "Aviso:" na mensagem, mostramos como alerta visual melhor (laranja/warning), 
                        // O joomla default de 'success/message' é verde. O joomla tem 'warning' tbm.
                        var msgType = successMsg.indexOf('Aviso:') !== -1 ? 'warning' : 'success';
                        showAlert(successMsg, msgType);
                    } else {
                        console.warn('TinyMCE não detectado.');
                        showAlert('Conteúdo extraído (Editor não encontrado).', 'success');
                        console.log(content);
                    }

                    // TODO: Futuramente integrar com IA para gerar a descrição usando o texto extraído + prompt

                } else {
                    showAlert('Erro no processamento: ' + response.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                var errorMsg = 'Erro de conexão com o servidor.';

                // Tenta extrair mensagem JSON do servidor (ex: erro 403)
                if (xhr.responseText) {
                    try {
                        var jsonResp = JSON.parse(xhr.responseText);
                        if (jsonResp && jsonResp.message) {
                            errorMsg = jsonResp.message;
                        }
                    } catch (e) {
                        console.error('Falha ao fazer parse do erro JSON:', e);
                    }
                }

                showAlert(errorMsg, 'error');
                console.error('AJAX Error:', error);
            },
            complete: function () {
                $btn.prop('disabled', false).html(originalBtnText);
                if ($(selectors.loadingContainer).length) $(selectors.loadingContainer).hide();
            }
        });
    }

    /**
     * Atualiza o estado visual da interface baseado no Modo Quiz.
     */
    /**
     * Registra os ouvintes de eventos do DOM.
     */
    function bindEvents() {
        // --- Drag and Drop Logic ---
        var $dropZone = $(selectors.dropZone);
        var $fileInput = $(selectors.fileInput);

        // Click on dropzone triggers hidden input
        $dropZone.on('click', function (e) {
            // Prevent recursive click if clicking the input itself (bubbling)
            if (e.target.id !== selectors.fileInput.replace('#', '')) {
                $fileInput.click();
            }
        });

        // Drag Over
        $dropZone.on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        // Drag Leave
        $dropZone.on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        // Drop
        $dropZone.on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                // Assign to hidden input using DataTransfer (modern browsers)
                $fileInput[0].files = files;
                // Trigger change to update UI
                $fileInput.trigger('change');
            }
        });

        // File Input Change (Standard or Drop)
        $fileInput.on('change', function () {
            var files = $(this)[0].files;
            var display = '';

            if (files.length === 1) {
                display = files[0].name;
            } else if (files.length > 1) {
                display = files.length + ' arquivos selecionados: ' + files[0].name + ' (+ ' + (files.length - 1) + ')';
            }

            // Update the centered text span
            $(selectors.fileName).text(display);

            // Update dropzone styling to indicate active file
            if (files.length > 0) {
                $dropZone.css('border-color', '#27ae60');
                $(selectors.fileName).css('color', '#27ae60');
            } else {
                $dropZone.css('border-color', '#555');
            }
        });

        // --- Botão Principal ---
        $(document).on('click', selectors.generateBtn, function () {
            handleGenerate(false);
        });

        // Funções auxiliares para o modal
        function showAIModal() {
            var $dropdown = $(selectors.quizDropdown);
            var $overlay = $('#gw-ai-dropdown-overlay');

            // Move para o body para evitar problemas com transformações CSS de elementos pais
            $('body').append($overlay).append($dropdown);

            $dropdown.addClass('gw-ai-modal-mode').fadeIn('fast');
            $overlay.fadeIn('fast');
            $('body').addClass('gw-ai-modal-active');
        }

        function hideAIModal() {
            $(selectors.quizDropdown).removeClass('gw-ai-modal-mode').hide();
            $('#gw-ai-dropdown-overlay').hide();
            $('body').removeClass('gw-ai-modal-active');
        }

        // Botão Interno do Quiz (Geração de Quiz)
        $(document).on('click', selectors.quizSubmitBtn, function () {
            handleGenerate(true); // Força modo quiz
            hideAIModal();
        });

        // Revalida automaticamente ao trocar o arquivo
        $(document).on('change', selectors.fileInput, function () {
            clearAlerts();
            validateFile();
        });

        // Toggle do Dropdown ao clicar no botão de configurações
        $(document).on('click', selectors.settingsBtn, function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $dropdown = $(selectors.quizDropdown);

            if ($dropdown.hasClass('gw-ai-modal-mode') || $dropdown.is(':visible')) {
                hideAIModal();
            } else {
                showAIModal();
            }
        });

        // Fechar modal ao clicar no overlay
        $(document).on('click', '#gw-ai-dropdown-overlay', function (e) {
            hideAIModal();
        });

        // Legado: Fechar dropdown ao clicar fora (caso ainda não esteja como modal)
        $(document).on('click', function (e) {
            // Se o clique não foi no wrapper do upload nem no dropdown/overlay em si
            if (!$(e.target).closest('.gw-ai-upload-wrapper').length && !$(e.target).closest('#gw-ai-dropdown-overlay').length) {
                if ($(selectors.quizDropdown).is(':visible') && !$('body').hasClass('gw-ai-modal-active')) {
                    $(selectors.quizDropdown).hide();
                }
            }
        });

        // Evita fechar ao clicar dentro do dropdown
        $(document).on('click', selectors.quizDropdown, function (e) {
            e.stopPropagation();
        });
    }

    // === Public API ===
    return {
        /**
         * Inicializa o módulo GUID EWAY AI.
         */
        init: function () {
            bindEvents();
            console.log('GuidewayAI Chat Module Initialized');
        }
    };

})(jQuery);

// Inicializa quando o documento estiver pronto
jQuery(document).ready(function () {
    GuidewayAI.init();
});
