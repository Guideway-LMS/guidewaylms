var GuidewayFrontend = {

    getTextoEditor() {
        if (!window.tinymce || !tinymce.activeEditor) {
            alert('Editor não carregado');
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

    setLoading(loading) {
        const buttons = document.querySelectorAll('.gw-ai-btn');
        const spinner = document.getElementById('gw-ai-loading');

        buttons.forEach(btn => btn.disabled = loading);
        spinner.style.display = loading ? 'inline-block' : 'none';
    },

    // MOCK — simula resposta da IA
    mockRequest(acao, texto) {
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({
                    success: true,
                    data: `<p><strong>[${acao.toUpperCase()}]</strong><br>${texto}</p>`
                });
            }, 1500);
        });
    }
};

jQuery(function ($) {
    $('.gw-ai-btn').on('click', async function () {
        const acao = $(this).data('action');
        const texto = GuidewayFrontend.getTextoEditor();

        if (!texto) return;

        GuidewayFrontend.setLoading(true);

        try {
            // depois trocar isso pelo fetch real
            const response = await GuidewayFrontend.mockRequest(acao, texto);

            if (response.success) {
                GuidewayFrontend.setTextoEditor(response.data);
            } else {
                alert(response.message || 'Erro ao processar texto');
            }

        } catch (e) {
            alert('Erro inesperado ao comunicar com o servidor');
        } finally {
            GuidewayFrontend.setLoading(false);
        }
    });
});
