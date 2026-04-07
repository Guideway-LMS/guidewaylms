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
        fileInput: '#gw-ai-file',
        promptInput: '#gw-ai-prompt',
        loadingContainer: '#div-gw-ai-loading',
        settingsBtn: '#gw-ai-quiz-settings-btn',
        quizDropdown: '#gw-ai-quiz-dropdown',
        quizSubmitBtn: '#gw-ai-quiz-submit-btn',
        difficulty: '#gw-ai-difficulty',
        qcount: '#gw-ai-qcount',
        qtype: '#gw-ai-qtype',
        fileName: '#gw-ai-file-name',
        dropZone: '#gw-ai-drop-zone',
        actionTypeRadio: 'input[name="gw_ai_action_type"]',
        quizParamsContainer: '#gw-ai-quiz-params-container'
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
     * Lida com o clique no botão "Gerar Agora" do modal.
     * Centraliza a validação e fluxo de envio.
     */
    function handleGenerate() {
        var actionType = $(selectors.actionTypeRadio + ':checked').val(); // desc, quest, ou ambos
        var isAbstractOnly = (actionType === 'desc');
        var isQuizMode = !isAbstractOnly;

        // 1. Valida o arquivo primeiro
        if (!validateFile()) return;

        var prompt = $(selectors.promptInput).val();
        var hasFile = $(selectors.fileInput)[0].files.length > 0;

        // Se for Questão, ao menos um arquivo ou texto é obrigatório
        // Removida trava antiga que exigia PDF obrigatório
        // 2. Valida se há pelo menos um input (Texto OU Arquivos)
        if (prompt.trim() === '' && !hasFile) {
            showAlert('Por favor, descreva a atividade no campo de texto ou anexe PDFs para continuar.', 'error');
            return;
        }

        // 2.5 Intercepta configurações avançadas do Tamanho de Resumo para Views Auxiliares ou quando exigido na Lição Mestra
        if (isAbstractOnly || actionType === 'ambos') {
            var summaryLengthEl = $('input[name="gw_ai_summary_length"]:checked');
            var context = $('#gw_ai_context').val() || 'lesson'; // Pode ser lesson, course, announcement

            if (summaryLengthEl.length > 0) {
                var lengthVal = summaryLengthEl.val();
                var lengthInstruction = "";
                
                //=========================================================
                // 1. CONTEXTO: AVISOS (MURAL)
                //=========================================================
                if (context === "announcement") {
                    if (lengthVal === "sucinto") {
                        lengthInstruction = `[DIRETRIZ DE COMUNICAÇÃO - SUCINTO]
Você atua como um prestativo assistente de comunicação interna. Resuma o recado a seguir.
Regras de Tamanho e Conteúdo: No máximo 3 sentenças curtas. Vá direto ao aviso central, de forma clara, amigável e informativa, sem usar jargões educacionais de aulas.`;
                    } else if (lengthVal === "explicativo") {
                        lengthInstruction = `[DIRETRIZ DE COMUNICAÇÃO - EXPLICATIVO]
Você é um excelente redator e gestor de comunidade em uma instrução. Sua tarefa é produzir uma mensagem ou comunicado elaborado, humano, rico em detalhes relevantes e motivador (ex: avisos extensos, mensagens sazonais ou de boas-vindas).
Regras de Tamanho e Conteúdo: Produzir texto detalhado. Explore os pontos-chave da mensagem original em seções usando tags HTML (<h3>), traga informações vitais como prazos ou procedimentos, elaborando o comunicado de forma envolvente e apropriada.`;
                    } else if (lengthVal === "medio") {
                        lengthInstruction = `[DIRETRIZ DE COMUNICAÇÃO - MÉDIO]
Você é um auxiliar da comunicação interna. Formate o aviso para um formato de leitura rápida para o público da plataforma.
Regras de Tamanho e Conteúdo: Comece com um parágrafo claro sobre o teor do aviso e apresente os detalhes mais importantes (pontos de ação, datas ou lembretes cruciais) em formato de lista (<ul><li>).`;
                    }
                
                //=========================================================
                // 2. CONTEXTO: CURSOS (Ementa/Vendas)
                //=========================================================
                } else if (context === "course") {
                    if (lengthVal === "sucinto") {
                        lengthInstruction = `[DIRETRIZ DE APRESENTAÇÃO E VENDAS - SUCINTO]
Você redige textos persuasivos e engajadores para atrair novos alunos (Pitch do Curso). Resuma e apresente a proposta.
Regras de Tamanho e Conteúdo: Máximo de 3 sentenças focando no diferencial competitivo, no resultado desejado ou no maior benefício que o aluno ganhará matriculando-se. O tom deve ser atraente.`;
                    } else if (lengthVal === "explicativo") {
                        lengthInstruction = `[DIRETRIZ DE APRESENTAÇÃO E VENDAS - EXPLICATIVO]
Você é um especialista em marketing do conhecimento criando a apresentação principal, ementa e proposta de valor deste curso.
Regras de Tamanho e Conteúdo: Texto extenso (página de vendas). Contextualize a importância deste tema no mercado/vida, descreva quem é o público-alvo ou perfil ideal esperado para a ementa base e detalhe claramente as competências práticas que serão desenvolvidas utilizando subtítulos (<h3>).`;
                    } else if (lengthVal === "medio") {
                        lengthInstruction = `[DIRETRIZ DE APRESENTAÇÃO E VENDAS - MÉDIO]
Você é um estrategista engajando propects com uma visão geral deste curso (Landing Page description).
Regras de Tamanho e Conteúdo: Comece com 1 ou 2 parágrafos introdutórios cativantes. Em seguida, destaque de 4 a 6 "Benefícios e Highlights" que o curso ensinará em bullet points (<ul><li>), destacando termos chaves em (<strong>).`;
                    }
                
                //=========================================================
                // 3. CONTEXTO: LIÇÕES (Pedagógico) - Default fallback
                //=========================================================
                } else {
                    if (lengthVal === "sucinto") {
                        lengthInstruction = `[DIRETRIZ PEDAGÓGICA - NÍVEL SUCINTO]
Você é um assistente pedagógico de IA. Sua tarefa é criar uma descrição do conteúdo em anexo.
Regras de Tamanho e Conteúdo: Escreva no máximo 3 sentenças curtas ou use uma lista breve (<ul><li>). Vá direto ao ponto central: O que o aluno vai aprender aqui de fato?`;
                    } else if (lengthVal === "explicativo") {
                        lengthInstruction = `[DIRETRIZ PEDAGÓGICA - NÍVEL EXPLICATIVO]
Você é um professor especialista no assunto tratado no texto. Sua tarefa é produzir uma descrição detalhada e explicativa base para uma lição deste LMS.
Regras de Tamanho e Conteúdo: 
- Introdução: Explique a relevância do tema na vida acadêmica em um parágrafo.
- Desenvolvimento: Divida em seções (mínimo 2) usando subtítulos (<h3>), focando e explorando causas, nuances ou consequências (o 'porquê' e não apenas 'o quê').
- Conceitos-Chave: Defina termos técnicos encontrados destacando com <strong>.
- Conclusão conectando a prática e os próximos passos.`;
                    } else if (lengthVal === "medio") {
                        lengthInstruction = `[DIRETRIZ PEDAGÓGICA - NÍVEL MÉDIO]
Você é um tutor focado em eficiência na aprendizagem. Crie um guia de estudo/introdução para o aluno.
Regras de Tamanho e Conteúdo: Um parágrafo inicial (<p>) de contextualização (2 a 3 linhas), depois uma lista (<ul><li>) extraindo de 4 a 6 "Key Takeaways" (Aperitivos/tópicos principais abordados na aula original).`;
                    }
                }
                
                if (lengthInstruction !== "") {
                    // Prepara as regras base intocáveis
                    var globalRules = `

Regras Globais Obrigatórias (Se não as seguir, a plataforma quebrará):
1. Proibido usar introduções conversacionais como "Este texto fala sobre...", "Aqui está o seu texto...", "Abaixo a sua resposta". Comece SEMPRE DIRETAMENTE entregando o material solicitado no Nível desejado.
2. Formate APENAS retornando o texto validado em HTML (tags <p>, <h3>, <ul>, <li>, <strong>, etc).
3. Não insira "\`\`\`html" (blocos markdown) antes e nunca coloque marcação ao final, apenas o próprio HTML limpo sendo renderizado no nó.`;

                    // Cria variável isolada ao invés de acoplar no prompt global, preservando a pureza de JSON caso seja Questão
                    var finalDescRules = lengthInstruction + globalRules;
                }
            }
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

            // Verifica inclusão de descrição a partir da seleção principal
            var includeDesc = (actionType === 'ambos') ? "1" : "0";
            formData.append('gw_ai_include_desc', includeDesc);
            
            if (prompt.trim() !== '') {
                formData.append('gw_ai_prompt', prompt);
            }
        } else if (prompt.trim() !== '') {
            formData.append('gw_ai_prompt', prompt);
        }

        // Adiciona a instrução HTML/Tamanho unicamente para processamentos focados em Descrição 
        if (typeof finalDescRules !== 'undefined') {
            formData.append('gw_ai_desc_rules', finalDescRules);
        }

        // UI: Estado de Carregamento
        var $btn = $(selectors.quizSubmitBtn);
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

        // Alternar visibilidade das opções de quiz (dificuldade e quantidade) basenado na seleção de tipo de ação
        $(document).on('change', selectors.actionTypeRadio, function () {
            var val = $(this).val();
            var $summaryContainer = $('#gw-ai-summary-params-container');
            if (val === 'desc') {
                $(selectors.quizParamsContainer).slideUp('fast');
                if ($summaryContainer.length) $summaryContainer.slideDown('fast');
            } else if (val === 'quest') {
                $(selectors.quizParamsContainer).slideDown('fast');
                if ($summaryContainer.length) $summaryContainer.slideUp('fast');
            } else { // Ambos
                $(selectors.quizParamsContainer).slideDown('fast');
                if ($summaryContainer.length) $summaryContainer.slideDown('fast');
            }
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

        // Botão Interno do Modal (Geração unificada)
        $(document).on('click', selectors.quizSubmitBtn, function () {
            handleGenerate(); // Lê configurações a partir do form unificado
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
