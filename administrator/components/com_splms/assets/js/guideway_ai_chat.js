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
        qtype: '#gw-ai-qtype'
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
     * Formata o JSON de questões para HTML.
     * @param {string|Array} jsonData - String JSON ou Objeto Array.
     * @returns {string} HTML formatado.
     */
    function formatQuizToHTML(jsonData) {
        var questions = [];
        try {
            questions = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        } catch (e) {
            console.error('Erro ao parsear JSON do Quiz', e);
            return '<p>Erro: O retorno da IA não é um JSON válido.</p><pre>' + jsonData + '</pre>';
        }

        if (!Array.isArray(questions)) {
            return '<p>Erro: O formato das questões está incorreto.</p>';
        }

        // Detecta tipo de questão baseado na primeira entrada
        var isEssay = (questions.length > 0 && typeof questions[0].answer !== 'undefined');
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

                $.each(q.options, function (idx, opt) {
                    var letter = letters[idx] || '?';
                    html += '<li>' + letter + ') ' + opt + '</li>';

                    if (idx === q.correct_answer) {
                        gabarito.push(num + ')' + letter);
                    }
                });
                html += '</ul>';
            } else if (isEssay && q.answer) {
                // Dissertativa
                html += '<br>'; // Espaço visual para aluno escrever
                gabarito.push('<strong>' + num + ')</strong> ' + q.answer);
            }

            html += '</div>';
            html += '<hr>';
        });

        // Adiciona Gabarito no Rodapé
        if (gabarito.length > 0) {
            html += '<div class="gw-quiz-footer" style="margin-top: 30px; padding: 15px; background: #f9f9f9; border: 1px solid #eee;">';
            html += '<h4>Gabarito / Respostas Esperadas</h4>';

            if (isMultipleChoice) {
                // Múltipla Escolha: Inline
                html += '<p>' + gabarito.join(' / ') + '</p>';
            } else {
                // Dissertativa: Lista
                html += '<ul>';
                $.each(gabarito, function (i, item) {
                    html += '<li style="margin-bottom: 10px;">' + item + '</li>';
                });
                html += '</ul>';
            }

            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    /**
     * Lida com o clique no botão "Gerar Descrição" ou "Gerar Quiz".
     * Centraliza a validação e fluxo de envio.
     * @param {boolean} forceQuiz - Se true, força o modo quiz ignorando outros estados.
     */
    function handleGenerate(forceQuiz) {
        // Garantir booleano
        var isQuizMode = (forceQuiz === true);

        // 1. Valida o arquivo primeiro
        if (!validateFile()) return;

        var prompt = $(selectors.promptInput).val();
        var hasFile = $(selectors.fileInput)[0].files.length > 0;

        // Se forceQuiz não foi passado, verificamos se há algum estado legado (opcional)
        // mas na nova UI, os botões são distintos.

        // Se for Quiz, arquivo é obrigatório
        if (isQuizMode && !hasFile) {
            showAlert('Para gerar um Quiz, é obrigatório anexar um arquivo PDF.', 'error');
            return;
        }

        // 2. Valida se há pelo menos um input (Texto OU Arquivo)
        if (prompt.trim() === '' && !hasFile) {
            showAlert('Por favor, descreva a atividade no campo de texto ou anexe um PDF para continuar.', 'error');
            return;
        }

        // 3. Preparação do Payload
        var formData = new FormData();
        var csrfToken = Joomla.getOptions('csrf.token');

        formData.append(csrfToken, 1); // CSRF Token
        formData.append('option', 'com_splms');
        formData.append('task', 'lesson.uploadPDF');

        // Adiciona arquivo se existir
        if (hasFile) {
            formData.append('gw_ai_file', $(selectors.fileInput)[0].files[0]);
        }

        // Adiciona parâmetros de Quiz ou Prompt
        if (isQuizMode) {
            formData.append('gw_ai_difficulty', $(selectors.difficulty).val());
            formData.append('gw_ai_qcount', $(selectors.qcount).val());
            formData.append('gw_ai_qtype', $(selectors.qtype).val());
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
                    console.log('PDF Processado com sucesso');

                    // Se for modo Quiz, formata o JSON
                    if (isQuizMode) {
                        content = formatQuizToHTML(content);
                    }

                    // Integração com o Editor TinyMCE
                    if (window.tinymce && tinymce.activeEditor) {
                        tinymce.activeEditor.setContent(content);
                        var successMsg = isQuizMode ? 'Questões geradas e inseridas no editor!' : 'Conteúdo importado com sucesso!';
                        showAlert(successMsg, 'success');
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
        // Botão Principal (Geração Padrão)
        $(document).on('click', selectors.generateBtn, function () {
            handleGenerate(false);
        });

        // Botão Interno do Quiz (Geração de Quiz)
        $(document).on('click', selectors.quizSubmitBtn, function () {
            handleGenerate(true); // Força modo quiz
            $(selectors.quizDropdown).hide(); // Fecha o menu apôs clique
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

            if ($dropdown.is(':visible')) {
                $dropdown.hide();
            } else {
                $dropdown.show();
            }
        });

        // Fechar dropdown ao clicar fora
        $(document).on('click', function (e) {
            // Se o clique não foi no wrapper do upload nem no dropdown em si
            if (!$(e.target).closest('.gw-ai-upload-wrapper').length) {
                $(selectors.quizDropdown).hide();
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
