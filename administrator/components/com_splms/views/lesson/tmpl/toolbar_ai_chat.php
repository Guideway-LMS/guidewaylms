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

            <!-- Actions Row -->
            <div class="gw-ai-actions" style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                
                <!-- File Input -->
                <div class="gw-ai-upload-wrapper" style="width: 100%;">
                    <input type="file" id="gw-ai-file" name="gw_ai_file" accept=".pdf" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                    <span class="muted small" style="margin-left: 5px;">(PDF, Máx. 5MB)</span>
                </div>

                <!-- Generate Button -->
                <button type="button" class="btn btn-primary" id="gw-ai-generate-btn">
                    <span class="icon-magic"></span> Gerar Descrição
                </button>
            </div>


        </div>
    </div>
</div>
