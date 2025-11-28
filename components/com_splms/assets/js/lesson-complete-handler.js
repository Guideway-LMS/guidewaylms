jQuery(function ($) {
    "use strict";
    console.log("JS DO SPLMS CARREGOU!");

    window.SPLMS_autoComplete = function (item_id, item_type = "lesson") {
        console.log("SPLMS_autoComplete chamado com:", {item_id, item_type});
        
        const btn = $("#splms-completed-item");
        if (btn.hasClass("btn-success")) {
            console.log("Já está concluído");
            return;
        }

        const form = document.getElementById("splms-completed-item-form");
        if (!form) {
            console.error("Formulário não encontrado!");
            return;
        }
        
        const user_id = form.querySelector("input[name='user_id']")?.value;
        if (!user_id) {
            console.error("user_id não encontrado!");
            return;
        }

        console.log("Enviando:", {user_id, item_id, item_type});

        $.ajax({
            type: "POST",
            url: "index.php?option=com_splms&task=lesson.completeditem",
            data: { user_id, item_id, item_type },
            success: function (response) {
                console.log("Resposta:", response);
                const data = $.parseJSON(response);
                console.log("Parseada:", data);
                if (data.status) {
                    console.log("Sucesso!");
                    btn.text(data.content).removeClass("btn-primary").addClass("btn-success").prop("disabled", true);
                    
                    // INTEGRAÇÃO COM TRABALHO DA IRIS
                    console.log("Mostrando notificação...");
                    if (typeof mostrarAlertaConclusao === 'function') {
                        mostrarAlertaConclusao();
                    } else {
                        console.warn("Função mostrarAlertaConclusao não encontrada");
                    }
                    
                    console.log("Atualizando barra...");
                    SPLMS_updateProgressBar();
                } else {
                    console.error("Erro:", data.content);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro AJAX:", {xhr, status, error});
            }
        });
    };

    $(document).on("click", "#splms-completed-item", function (event) {
        event.preventDefault();
        const form = $("#splms-completed-item-form");
        const user_id = form.find("input[name='user_id']").val();
        const item_id = form.find("input[name='item_id']").val();
        const item_type = form.find("input[name='item_type']").val();
        
        console.log("Botão clicado:", {user_id, item_id, item_type});

        $.ajax({
            type: "POST",
            url: "index.php?option=com_splms&task=lesson.completeditem",
            data: { user_id, item_id, item_type },
            beforeSend: function () { $("#splms-completed-item").prop("disabled", true).text("..."); },
            success: function (response) {
                const data = $.parseJSON(response);
                if (data.status) {
                    $("#splms-completed-item").text(data.content).removeClass("btn-primary").addClass("btn-success");
                    
                    // INTEGRAÇÃO COM TRABALHO DA IRIS
                    if (typeof mostrarAlertaConclusao === 'function') {
                        mostrarAlertaConclusao();
                    }
                    
                    console.log("Atualizando barra...");
                    SPLMS_updateProgressBar();
                } else {
                    alert(data.content);
                }
            }
        });
    });

    function SPLMS_updateProgressBar() {
        let courseId = new URLSearchParams(window.location.search).get('id');
        if (!courseId) {
            const m = window.location.pathname.match(/\/courses\/(\d+)/);
            if (m) courseId = m[1];
        }
        if (courseId && typeof loadCourseProgress === 'function') {
            console.log("Recarregando progresso:", courseId);
            loadCourseProgress(courseId);
        }
    }

    function SPLMS_hideButtonWhenVideo() {
        if ($("#splmsVideoPlayer").length > 0 || $("iframe[src*='youtube']").length > 0) {
            console.log("Escondendo botão");
            $("#splms-completed-item").hide();
        }
    }

    function SPLMS_bindFWDEVPlayer() {
        const w = setInterval(function () {
            if (window.splmsVideoPlayer1 && typeof window.splmsVideoPlayer1.addListener === "function") {
                clearInterval(w);
                console.log("FWDEVPlayer conectado!");
                window.splmsVideoPlayer1.addListener("playComplete", function () {
                    console.log("Vídeo terminou!");
                    const form = document.getElementById("splms-completed-item-form");
                    if (!form) return;
                    const item_id = form.querySelector("input[name='item_id']").value;
                    const item_type = form.querySelector("input[name='item_type']").value;
                    console.log("Chamando autoComplete...");
                    window.SPLMS_autoComplete(item_id, item_type);
                });
            }
        }, 200);
    }

    $(document).ready(function () {
        console.log("Inicializando...");
        SPLMS_hideButtonWhenVideo();
        SPLMS_bindFWDEVPlayer();
    });
});
