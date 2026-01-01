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

    // Chamada real para o endpoint callAI (admin)
    async callAI(acao, texto) {
        // Obtém o token CSRF do Joomla
        const token = Joomla.getOptions('csrf.token') || document.querySelector('input[name^="csrf"]')?.name || '';

        const formData = new URLSearchParams();
        formData.append('texto', texto);
        formData.append('acao', acao);
        if (token) formData.append(token, '1');

        // URL relativa - no admin, vai para controller admin
        // Removido format=json para evitar erro "Controlador inválido"
        const response = await fetch('index.php?option=com_splms&task=lesson.callAI', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }

        return data;
    }
};

jQuery(function ($) {
    $('.gw-ai-btn').on('click', async function () {
        const acao = $(this).data('action');
        const texto = GuidewayFrontend.getTextoEditor();

        if (!texto) return;

        GuidewayFrontend.setLoading(true);

        try {
            const response = await GuidewayFrontend.callAI(acao, texto);

            if (response.success) {
                GuidewayFrontend.setTextoEditor(response.data);
            } else {
                alert(response.message || 'Erro ao processar texto');
            }

        } catch (e) {
            console.error('GuidewayAI Error:', e);
            alert('Erro ao comunicar com o servidor: ' + e.message);
        } finally {
            GuidewayFrontend.setLoading(false);
        }
    });
});
