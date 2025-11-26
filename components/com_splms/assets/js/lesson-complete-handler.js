jQuery(function ($) {
    "use strict";
    console.log("JS DO SPLMS CARREGOU!");

    /**
     * -------------------------------------------------------
     * FUNÇÃO PRINCIPAL DE CONCLUSÃO DE LIÇÃO (AJAX)
     * -------------------------------------------------------
     */
    window.SPLMS_autoComplete = function (item_id, item_type = "lesson") {

        const btn = $("#splms-completed-item");

        // Evita marcar novamente
        if (btn.hasClass("btn-success")) return;

        $.ajax({
            type: "POST",
            url: "index.php?option=com_splms&task=lesson.completeditem",
            data: { item_id, item_type },
            success: function (response) {
                const data = $.parseJSON(response);

                if (data.status) {
                    btn
                        .text(data.content)
                        .removeClass("btn-primary")
                        .addClass("btn-success")
                        .prop("disabled", true);
                } else {
                    console.warn("Não foi possível concluir: " + data.content);
                }
            },
            error: function () {
                console.error("Erro ao enviar a solicitação.");
            }
        });
    };


    /**
     * -------------------------------------------------------
     * EVENTO DO BOTÃO MANUAL — usado para lições de TEXTO
     * -------------------------------------------------------
     */
    $(document).on("click", "#splms-completed-item", function (event) {
        event.preventDefault();

        const $this = $(this);
        const form = $("#splms-completed-item-form");

        const item_id = form.find("input[name='item_id']").val();
        const item_type = form.find("input[name='item_type']").val();

        $.ajax({
            type: "POST",
            url: "index.php?option=com_splms&task=lesson.completeditem",
            data: { item_id, item_type },
            beforeSend: function () {
                $this.prop("disabled", true).text("...");
            },
            success: function (response) {
                const data = $.parseJSON(response);

                if (data.status) {
                    $this
                        .text(data.content)
                        .removeClass("btn-primary")
                        .addClass("btn-success");
                } else {
                    alert(data.content);
                    $this.prop("disabled", false);
                }
            },
            error: function () {
                alert("Erro ao enviar a solicitação.");
                $this.prop("disabled", false);
            }
        });
    });


    /**
     * -------------------------------------------------------
     * ESCONDER BOTÃO QUANDO HÁ VÍDEO (FWDEV / YT / Vimeo)
     * -------------------------------------------------------
     */
    function SPLMS_hideButtonWhenVideo() {
        const hasVideo =
            $("#splmsVideoPlayer").length > 0 ||       // FWDEVPlayer
            $("iframe[src*='youtube']").length > 0 ||  // YouTube embed
            $("iframe[src*='vimeo']").length > 0;      // Vimeo embed

        if (hasVideo) {
            $("#splms-completed-item").hide();
        }
    }


    /**
     * -------------------------------------------------------
     * INTEGRAR FWDEVPlayer — detectar vídeo finalizado
     * -------------------------------------------------------
     */
    function SPLMS_bindFWDEVPlayer() {

        const wait = setInterval(function () {

            // A instância criada pelo SPLMS normalmente se chama "splmsVideoPlayer1"
            if (window.splmsVideoPlayer1 && typeof window.splmsVideoPlayer1.addListener === "function") {

                clearInterval(wait);

                window.splmsVideoPlayer1.addListener("playComplete", function () {

                    const form = document.getElementById("splms-completed-item-form");
                    if (!form) return;

                    const item_id = form.querySelector("input[name='item_id']").value;
                    const item_type = form.querySelector("input[name='item_type']").value;

                    window.SPLMS_autoComplete(item_id, item_type);
                });
            }

        }, 200);
    }


    /**
     * -------------------------------------------------------
     * INICIALIZAÇÃO
     * -------------------------------------------------------
     */
    $(document).ready(function () {
        SPLMS_hideButtonWhenVideo();
        SPLMS_bindFWDEVPlayer();
    });

});
