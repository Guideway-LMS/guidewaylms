<div class="control-group">
    <div class="control-label">
        <label id="gw-ai-prompt-lbl" for="gw-ai-prompt" title="Descreva a atividade ou anexe um PDF para que a IA gere a descrição.">
            <span class="icon-stars" style="color: #f0ad4e;" aria-hidden="true"></span>
            <strong>Crie a descrição com IA</strong>
        </label>
    </div>
    <div class="controls">
        <!-- Contêiner Principal -->
        <div class="gw-ai-chat-container">
            <input type="hidden" id="gw_ai_context" value="<?php echo isset($aiContext) ? $aiContext : 'lesson'; ?>">
            
            <!-- Área de Texto do Prompt - Corresponde aos inputs padrões -->
            <textarea id="gw-ai-prompt" name="gw_ai_prompt" class="form-control" rows="3" placeholder="Digite seu comando para a IA..." style="resize: vertical;"></textarea>
            <div id="gw-ai-prompt-desc" class="form-text">Descreva a atividade ou anexe um PDF para que a IA gere a descrição.</div>

            <!-- Linha de Ações -->
            <div class="gw-ai-actions">
                
                <!-- Zona de Upload de Arquivos -->
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

                    <!-- Botão Principal de IA -->
                    <button type="button" id="gw-ai-quiz-settings-btn" class="btn btn-primary" style="height: 100%; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 5px;" title="Gerar com IA">
                        <span class="icon-magic"></span>Gerar com IA
                    </button>

                    <!-- Overlay do Modal (Bloqueio de tela e desfoque) -->
                    <div id="gw-ai-dropdown-overlay" class="gw-ai-dropdown-overlay"></div>

                    <!-- Conteúdo do Dropdown (Transformado em Modal via JS/CSS) -->
                    <div id="gw-ai-quiz-dropdown" class="gw-ai-dropdown gw-ai-glass-dropdown">
                        <div class="gw-ai-dropdown-title">Gerar com IA</div>

                        <div id="gw-ai-dropdown-params">
                            <?php if (empty($hideGenerateQuestions)): ?>
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

                            <!-- Acordeon Dinâmico do Tamanho de Resumo na Lição -->
                            <div id="gw-ai-summary-params-container" style="display: block;">
                                <div class="control-group gw-mb-15">
                                    <label class="gw-ai-label-small">Tamanho do Resumo / Texto</label>
                                    <div class="gw-ai-segmented-control" role="group">
                                        <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-curto-lesson" value="sucinto" autocomplete="off">
                                        <label class="btn btn-outline-primary" for="gw-ai-sum-curto-lesson" title="Curto e direto">Sucinto</label>

                                        <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-medio-lesson" value="medio" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" for="gw-ai-sum-medio-lesson" title="Tamanho padrão">Médio</label>

                                        <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-longo-lesson" value="explicativo" autocomplete="off">
                                        <label class="btn btn-outline-primary" for="gw-ai-sum-longo-lesson" title="Muito detalhado">Explicativo</label>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- MODO TEXTO: Configuração de Tamanho do Resumo -->
                            <input type="radio" name="gw_ai_action_type" value="desc" checked style="display:none;">
                            
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small">Tamanho do Resumo / Texto</label>
                                <div class="gw-ai-segmented-control" role="group">
                                    <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-curto" value="sucinto" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="gw-ai-sum-curto" title="Curto e direto">Sucinto</label>

                                    <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-medio" value="medio" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="gw-ai-sum-medio" title="Tamanho padrão">Médio</label>

                                    <input type="radio" class="btn-check" name="gw_ai_summary_length" id="gw-ai-sum-longo" value="explicativo" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="gw-ai-sum-longo" title="Muito detalhado">Explicativo</label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Botão de Ação dentro do Menu -->
                        <button type="button" id="gw-ai-quiz-submit-btn" class="gw-ai-btn-glow">
                            <span class="icon-magic"></span> Gerar Agora
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
