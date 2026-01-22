// Função principal: mostrar notificação de aula concluída
function mostrarAlertaConclusao() {
    // Evita criar várias caixas se a função for chamada mais de uma vez
    if (document.querySelector(".conclusao-alerta")) {
        return;
    }

    const caixa = document.createElement("div");
    caixa.classList.add("conclusao-alerta");

    caixa.innerHTML = `
        <div class="conclusao-alerta__conteudo">
            <h3>Parabéns! 🎉</h3>
            <p>Você concluiu esta aula.</p>
        </div>
    `;

    document.body.appendChild(caixa);

    // Mostra com animação
    setTimeout(() => {
        caixa.classList.add("conclusao-alerta--visivel");
    }, 50);

    // Some depois de alguns segundos
    setTimeout(() => {
        caixa.classList.remove("conclusao-alerta--visivel");
        setTimeout(() => caixa.remove(), 500);
    }, 3500);
}
