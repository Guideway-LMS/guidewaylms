<div class="control-group">
    <div class="control-label">
        <label id="gw-ai-prompt-lbl" for="gw-ai-prompt" title="Descreva a atividade ou anexe um PDF para que a IA gere a descrição.">
            <span class="icon-stars" style="color: #f0ad4e;" aria-hidden="true"></span>
            <strong>Crie a descrição com IA</strong>
        </label>
    </div>
    <div class="controls">
        <!-- Main Container -->
        <div class="gw-ai-chat-container">
            
            <!-- Prompt Textarea - Matches standard inputs -->
            <textarea id="gw-ai-prompt" name="gw_ai_prompt" class="form-control" rows="3" placeholder="Digite seu comando para a IA..." style="resize: vertical;"></textarea>
            <div id="gw-ai-prompt-desc" class="form-text">Descreva a atividade ou anexe um PDF para que a IA gere a descrição.</div>

            <!-- Linha de Ações -->
            <div class="gw-ai-actions">
                
                <!-- Drag and Drop Dropzone -->
                <div class="gw-ai-upload-wrapper">
                    <div id="gw-ai-drop-zone" class="gw-ai-drop-zone">
                        <input type="file" id="gw-ai-file" name="gw_ai_file[]" accept=".pdf" class="gw-ai-input-hidden" multiple>
                        <div class="gw-ai-drop-content">
                            <span class="icon-upload" style="font-size: 24px; margin-bottom: 5px; color: #666;"></span>
                            <span class="gw-ai-drop-text">Arraste e solte os PDFs aqui ou clique para selecionar</span>
                            <span class="gw-ai-drop-text" style="font-size: 11px; margin-top: 2px;">(Máx: 5MB por arquivo | Múltiplos Arquivos)</span>
                            <span id="gw-ai-file-name" class="gw-ai-file-name-centered"></span>
                        </div>
                    </div>

                    <?php if (empty($hideGenerateQuestions)): ?>

                    <!-- Botão de Configurações da Questão -->
                    <button type="button" id="gw-ai-quiz-settings-btn" class="btn btn-primary" style="height: 100%; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 5px;" title="Gerar Questão a partir do PDF">
                        <span class="icon-list-view"></span> Gerar Questões
                    </button>

                    <!-- Overlay do Modal (Bloqueio de tela e desfoque) -->
                    <div id="gw-ai-dropdown-overlay" class="gw-ai-dropdown-overlay"></div>

                    <!-- Conteúdo do Dropdown (Transformado em Modal via JS/CSS) -->
                    <div id="gw-ai-quiz-dropdown" class="gw-ai-dropdown gw-ai-glass-dropdown">
                        <div class="gw-ai-dropdown-title">Configurar e Gerar Questões</div>

                        <div id="gw-ai-dropdown-params">
                            <!-- Tipo oculto -->
                            <input type="hidden" id="gw-ai-qtype" value="dissertativa">

                            <!-- Dificuldade Segmentada -->
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small">Dificuldade</label>
                                <div class="gw-ai-segmented-control" role="group">
                                    <input type="radio" class="btn-check" name="gw_ai_difficulty_radio" id="gw-ai-diff-facil" value="facil" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="gw-ai-diff-facil">Fácil</label>

                                    <input type="radio" class="btn-check" name="gw_ai_difficulty_radio" id="gw-ai-diff-medio" value="medio" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="gw-ai-diff-medio">Médio</label>

                                    <input type="radio" class="btn-check" name="gw_ai_difficulty_radio" id="gw-ai-diff-dificil" value="dificil" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="gw-ai-diff-dificil">Difícil</label>
                                </div>
                                <!-- Input oculto para manter compatibilidade com o JS existente -->
                                <input type="hidden" id="gw-ai-difficulty" value="medio">
                            </div>

                            <!-- Quantidade -->
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small">Quantidade (ex: 5, 7, 10)</label>
                                <div class="gw-ai-number-input-wrapper">
                                    <input type="number" id="gw-ai-qcount" class="gw-ai-number-input" value="5" min="1" max="50">
                                </div>
                            </div>
                            
                            <!-- Gerar Descrição Antes das Questões -->
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small" style="margin-bottom: 8px;">Incluir resumo do conteúdo antes das questões?</label>
                                <div class="gw-ai-segmented-control" role="group">
                                    <input type="radio" class="btn-check" name="gw_ai_include_desc_radio" id="gw-ai-desc-sim" value="1" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="gw-ai-desc-sim">Sim</label>

                                    <input type="radio" class="btn-check" name="gw_ai_include_desc_radio" id="gw-ai-desc-nao" value="0" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="gw-ai-desc-nao">Não</label>
                                </div>
                                <input type="hidden" id="gw-ai-include-desc" value="0">
                            </div>
                        </div>

                        <!-- Botão de Ação dentro do Menu -->
                        <button type="button" id="gw-ai-quiz-submit-btn" class="gw-ai-btn-glow">
                            <span class="icon-magic"></span> Gerar Agora
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Generate Button -->
                <button type="button" class="btn btn-primary" id="gw-ai-generate-btn" style="height: 100%; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 5px;">
                    <span class="icon-magic"></span> Gerar Descrição
                </button>
            </div>

        </div>
    </div>
</div>
