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
                        <input type="file" id="gw-ai-file" name="gw_ai_file" accept=".pdf" class="gw-ai-input-hidden">
                        <div class="gw-ai-drop-content">
                            <span class="icon-upload" style="font-size: 24px; margin-bottom: 5px; color: #666;"></span>
                            <span class="gw-ai-drop-text">Arraste e solte o PDF aqui ou clique para selecionar</span>
                            <span id="gw-ai-file-name" class="gw-ai-file-name-centered"></span>
                        </div>
                    </div>

                    <!-- Botão de Configurações do Quiz -->
                    <button type="button" id="gw-ai-quiz-settings-btn" class="btn btn-small gw-ai-btn-quiz" title="Gerar Questões a partir do PDF">
                        <span class="icon-list-view"></span> Gerar Quiz
                    </button>

                    <!-- Conteúdo do Dropdown (Oculto) -->
                    <div id="gw-ai-quiz-dropdown" class="gw-ai-dropdown">
                        
                        <div class="gw-ai-dropdown-title">
                            Configurar e Gerar Questões
                        </div>

                        <!-- Parâmetros (Sempre Ativos) -->
                        <div id="gw-ai-dropdown-params">
                            <div class="control-group gw-mb-10">
                                <label class="gw-ai-label-small">Tipo de Questão</label>
                                <select id="gw-ai-qtype" class="form-control form-control-sm gw-ai-select">
                                    <option value="optativa" selected>Optativa (Múltipla Escolha)</option>
                                    <option value="dissertativa">Dissertativa</option>
                                </select>
                            </div>

                            <div class="control-group gw-mb-10">
                                <label class="gw-ai-label-small">Dificuldade</label>
                                <select id="gw-ai-difficulty" class="form-control form-control-sm gw-ai-select">
                                    <option value="facil">Fácil</option>
                                    <option value="medio" selected>Médio</option>
                                    <option value="dificil">Difícil</option>
                                </select>
                            </div>
                            
                            <div class="control-group gw-mb-15">
                                <label class="gw-ai-label-small">Quantidade</label>
                                <select id="gw-ai-qcount" class="form-control form-control-sm gw-ai-select">
                                    <option value="3">3 Questões</option>
                                    <option value="5" selected>5 Questões</option>
                                    <option value="10">10 Questões</option>
                                    <option value="15">15 Questões</option>
                                </select>
                            </div>
                        </div>

                        <hr class="gw-hr-margin">

                        <!-- Botão de Ação dentro do Menu -->
                         <button type="button" id="gw-ai-quiz-submit-btn" class="btn btn-primary btn-small gw-ai-btn-block">
                            <span class="icon-wand"></span> Gerar Agora
                        </button>

                    </div>
                </div>

                <!-- Generate Button -->
                <button type="button" class="btn btn-primary" id="gw-ai-generate-btn">
                    <span class="icon-magic"></span> Gerar Descrição
                </button>
            </div>


        </div>
    </div>
</div>
