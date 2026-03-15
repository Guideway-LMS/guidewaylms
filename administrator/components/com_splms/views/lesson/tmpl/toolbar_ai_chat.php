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

                    <!-- Botão Principal de IA -->
                    <button type="button" id="gw-ai-quiz-settings-btn" class="btn btn-primary" style="height: 100%; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 5px;" title="Configurar e Gerar com IA">
                        <span class="icon-magic"></span> Configurar e Gerar com IA
                    </button>

                    <!-- Overlay do Modal (Bloqueio de tela e desfoque) -->
                    <div id="gw-ai-dropdown-overlay" class="gw-ai-dropdown-overlay"></div>

                    <!-- Conteúdo do Dropdown (Transformado em Modal via JS/CSS) -->
                    <div id="gw-ai-quiz-dropdown" class="gw-ai-dropdown gw-ai-glass-dropdown">
                        <div class="gw-ai-dropdown-title">Configurar e Gerar com IA</div>

                        <div id="gw-ai-dropdown-params">
                            <!-- O que gerar? -->
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small">Qual conteúdo você deseja gerar?</label>
                                <div class="gw-ai-segmented-control" role="group" style="display: flex; flex-direction: column; gap: 5px;">
                                    <div style="width: 100%;">
                                        <input type="radio" class="btn-check" name="gw_ai_action_type" id="gw-ai-action-desc_lesson" value="desc" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" style="width: 100%; text-align: center;" for="gw-ai-action-desc_lesson">Apenas Descrição/Resumo</label>
                                    </div>
                                    <div style="width: 100%;">
                                        <input type="radio" class="btn-check" name="gw_ai_action_type" id="gw-ai-action-quest_lesson" value="quest" autocomplete="off">
                                        <label class="btn btn-outline-primary" style="width: 100%; text-align: center;" for="gw-ai-action-quest_lesson">Apenas Questões</label>
                                    </div>
                                    <div style="width: 100%;">
                                        <input type="radio" class="btn-check" name="gw_ai_action_type" id="gw-ai-action-ambos_lesson" value="ambos" autocomplete="off">
                                        <label class="btn btn-outline-primary" style="width: 100%; text-align: center;" for="gw-ai-action-ambos_lesson">Descrição + Questões</label>
                                    </div>
                                </div>
                            </div>

                            <div id="gw-ai-quiz-params-container" style="display: none;">
                                <!-- Tipo oculto -->
                                <input type="hidden" id="gw-ai-qtype" value="dissertativa">

                                <!-- Dificuldade Segmentada -->
                                <div class="control-group gw-mb-15">
                                    <label class="gw-ai-label-small">Dificuldade das Questões</label>
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
                            
                            </div>
                        </div>

                        <!-- Botão de Ação dentro do Menu -->
                        <button type="button" id="gw-ai-quiz-submit-btn" class="gw-ai-btn-glow">
                            <span class="icon-magic"></span> Gerar Agora
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
