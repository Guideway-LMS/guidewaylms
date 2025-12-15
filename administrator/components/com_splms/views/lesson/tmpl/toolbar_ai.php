<div id="guideway-ai-toolbar">
    <p><span class="icon-stars"></span>Organize a descrição com IA</p>

    <div class="btn-group">
        <button type="button" class="btn btn-small btn-info gw-ai-btn" data-action="revisar">
            <span class="icon-pencil"></span> Revisar Gramática
        </button>

        <button type="button" class="btn btn-small btn-warning gw-ai-btn" data-action="resumir">
            <span class="icon-list"></span> Criar Resumo
        </button>

        <button type="button" class="btn btn-small btn-success gw-ai-btn" data-action="reescrever">
            <span class="icon-lightbulb"></span> Turbinar Copy
        </button>
    </div>

    <span id="gw-ai-loading">
        <span class="icon-loop spinner"></span> Processando...
    </span>
</div>

<script>
var GuidewayFrontend = {

    getTextoEditor() {
        if (!tinymce?.activeEditor) {
            alert('TinyMCE não carregado');
            return '';
        }
        return tinymce.activeEditor.getContent();
    },

    setTextoEditor(novoTexto) {
        if (tinymce?.activeEditor) {
            tinymce.activeEditor.setContent(novoTexto);
            tinymce.activeEditor.focus();
        }
    },

};

jQuery(function($){
    $('.gw-ai-btn').on('click', function(){
        // diferenciar cada botão:
        const acao = $(this).data('action');

        if (acao === 'revisar') {
            GuidewayFrontend.setTextoEditor("<p><strong>Texto substituído com sucesso!</strong><br>Este conteúdo foi inserido via setContent().</p>");
        }

        if (acao === 'resumir') {
            GuidewayFrontend.setTextoEditor("<h3>Resumo Gerado:</h3><p>Lorem ipsum dolor sit amet...</p>");
        }

        if (acao === 'reescrever') {
            GuidewayFrontend.setTextoEditor("<p>Nova versão reescrita automaticamente para teste.</p>");
        }
    });
});
</script>
