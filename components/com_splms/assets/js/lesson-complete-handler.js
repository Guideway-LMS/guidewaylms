jQuery(function ($) {
    "use strict";
    console.log("SPLMS: JS carregado.");

    // ----------------------------
    // Config / constantes
    // ----------------------------
    const MAX_FIND_FORM_ATTEMPTS = 20;
    const FIND_FORM_INTERVAL_MS = 150;

    // pega config enviada pelo PHP via addScriptOptions
    const cfg = (typeof Joomla !== "undefined" && typeof Joomla.getOptions === "function")
        ? Joomla.getOptions("splmsConfig") || {}
        : {};
    const SPLMS_PERCENTUAL_MINIMO = parseInt(cfg.percentualMinimoConclusao || 90, 10);

    console.log("SPLMS-LOG: Percentual mínimo configurado:", SPLMS_PERCENTUAL_MINIMO + "%");

    // ----------------------------
    // Estado
    // ----------------------------
    let SPLMS_ITEM_ID = null;
    let SPLMS_ITEM_TYPE = null;
    let SPLMS_USER_ID = null; // não usamos no POST (controller usa usuário logado), mas mantemos para info
    let SPLMS_COURSE_ID = null;
    let formFound = false;

    // ----------------------------
    // Utils para obter valores
    // ----------------------------
    function readValuesFromForm(form) {
        try {
            const itemIdEl = form.querySelector("input[name='item_id']");
            const itemTypeEl = form.querySelector("input[name='item_type']");
            const userIdEl = form.querySelector("input[name='user_id']");
            const courseIdEl = form.querySelector("input[name='course_id']");

            const itemId = itemIdEl ? itemIdEl.value : null;
            const itemType = itemTypeEl ? itemTypeEl.value : null;
            const userId = userIdEl ? userIdEl.value : null;
            const courseId = courseIdEl ? courseIdEl.value : null;

            return { itemId, itemType, userId, courseId };
        } catch (e) {
            console.warn("SPLMS-LOG: erro ao ler valores do form:", e);
            return { itemId: null, itemType: null, userId: null, courseId: null };
        }
    }


    window.readValuesFromForm = readValuesFromForm;

    function findHiddenInputsAnywhere() {
        const itemIdEl = document.querySelector("input[name='item_id']");
        const itemTypeEl = document.querySelector("input[name='item_type']");
        const userIdEl = document.querySelector("input[name='user_id']");
        const courseIdEl = document.querySelector("input[name='course_id']");
        return {
            itemId: itemIdEl ? itemIdEl.value : null,
            itemType: itemTypeEl ? itemTypeEl.value : null,
            userId: userIdEl ? userIdEl.value : null,
            courseId: courseIdEl ? courseIdEl.value : null
        };
    }

    function readValuesFromButton(btn) {
        if (!btn) return { itemId: null, itemType: null, userId: null };
        return {
            itemId: btn.getAttribute("data-item_id") || btn.getAttribute("data-item-id") || null,
            itemType: btn.getAttribute("data-item_type") || btn.getAttribute("data-item-type") || null,
            userId: btn.getAttribute("data-user_id") || btn.getAttribute("data-user-id") || null
        };
    }

    // ----------------------------
    // Localizar form (com tentativas)
    // ----------------------------
    // ERICK 21-12
    function bootstrapLessonContext() {
        if (window.SPLMS_CONTEXT?.itemId) {
            SPLMS_ITEM_ID = window.SPLMS_CONTEXT.itemId;
            SPLMS_ITEM_TYPE = window.SPLMS_CONTEXT.itemType;
            SPLMS_USER_ID = window.SPLMS_CONTEXT.userId;
            SPLMS_COURSE_ID = window.SPLMS_CONTEXT.courseId;

            console.log("SPLMS-LOG: Contexto carregado via PHP", window.SPLMS_CONTEXT);
            return true;
        }
        return false;
    }

    function locateFormAndValues(callback) {
        let attempts = 0;

        function attempt() {
            attempts++;
            const form = document.getElementById("splms-completed-item-form");
            if (form) {
                formFound = true;
                console.log("SPLMS-LOG: Formulário #splms-completed-item-form encontrado (tentativa: " + attempts + ").");
                const vals = readValuesFromForm(form);
                SPLMS_ITEM_ID = SPLMS_ITEM_ID || vals.itemId;
                SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || vals.itemType;
                SPLMS_USER_ID = SPLMS_USER_ID || vals.userId;
                SPLMS_COURSE_ID = SPLMS_COURSE_ID || vals.courseId;
                console.log("SPLMS-LOG: Valores do form:", { SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, SPLMS_USER_ID, SPLMS_COURSE_ID });
                callback(true);
                return;
            }

            // fallback: procurar inputs escondidos em qualquer lugar
            const foundAnywhere = findHiddenInputsAnywhere();
            if (foundAnywhere.itemId || foundAnywhere.itemType) {
                console.log("SPLMS-LOG: Inputs escondidos encontrados fora do form:", foundAnywhere);
                SPLMS_ITEM_ID = foundAnywhere.itemId;
                SPLMS_ITEM_TYPE = foundAnywhere.itemType;
                SPLMS_USER_ID = foundAnywhere.userId;
                SPLMS_COURSE_ID = foundAnywhere.courseId
                callback(true);
                return;
            }

            // fallback: tentar ler do botão (talvez template tenha data-attrs)
            const btn = document.getElementById("splms-completed-item");
            if (btn) {
                const fromBtn = readValuesFromButton(btn);
                if (fromBtn.itemId || fromBtn.itemType) {
                    console.log("SPLMS-LOG: Valores adquiridos do botão:", fromBtn);
                    SPLMS_ITEM_ID = fromBtn.itemId;
                    SPLMS_ITEM_TYPE = fromBtn.itemType;
                    SPLMS_USER_ID = fromBtn.userId;
                    SPLMS_COURSE_ID = fromBtn.courseId
                    callback(true);
                    return;
                }
            }

            if (attempts >= MAX_FIND_FORM_ATTEMPTS) {
                console.error("❌ SPLMS-ERRO: Formulário/valores NÃO encontrados após " + attempts + " tentativas.");
                callback(false);
                return;
            }

            // tentar de novo
            setTimeout(attempt, FIND_FORM_INTERVAL_MS);
        }

        attempt();
    }

    // ----------------------------
    // Função AJAX que o controller espera (item_id + item_type)
    // ----------------------------
    function doCompleteAjax(itemId, itemType, onSuccess, onError) {
        if (!itemId || !itemType) {
            console.error("SPLMS-LOG: Não é possível enviar AJAX — itemId ou itemType faltando.", { itemId, itemType });
            if (onError) onError(new Error("itemId/itemType missing"));
            return;
        }

        console.log("SPLMS-LOG: Enviando AJAX para completar item:", { itemId, itemType, courseId: SPLMS_COURSE_ID });

        $.ajax({
            type: "POST",
            url: "index.php?option=com_splms&task=lesson.completeditem",
            data: {
                item_id: itemId,
                item_type: itemType,
                // não enviamos user_id — controller usa usuário logado via Factory::getUser()
                courseId: SPLMS_COURSE_ID
            },
            success: function (raw) {
                console.log("SPLMS-LOG: Resposta do servidor:", raw);
                let res = null;
                try {
                    res = $.parseJSON(raw);
                } catch (e) {
                    console.error("SPLMS-LOG: Falha ao parsear JSON de resposta:", e, raw);
                    if (onError) onError(e);
                    return;
                }

                if (res && (res.status === true || res.success === true)) {
                    console.log("SPLMS-LOG: Aula concluída com sucesso (resposta):", res);
                    if (onSuccess) onSuccess(res);
                } else {
                    console.warn("SPLMS-LOG: Servidor retornou falha ao concluir:", res);
                    if (onError) onError(res);
                }
            },
            error: function (xhr, status, err) {
                console.error("SPLMS-LOG: Erro AJAX:", { xhr, status, err });
                if (onError) onError(err || status);
            }
        });
    }

    // ----------------------------
    // Atualiza UI após conclusão
    // ----------------------------
    function markButtonCompleted(text) {
        const btn = $("#splms-completed-item");
        if (!btn || btn.length === 0) return;
        const contentText = text || (cfg.text_completed || 'Concluído');
        btn.text(contentText).removeClass("btn-primary").addClass("btn-success").prop("disabled", true).show();
        // remove form to avoid re-submissions (keeping inputs elsewhere unchanged)
        const form = document.getElementById("splms-completed-item-form");
        if (form) form.style.display = "none";
    }

    // ----------------------------
    // Handler de clique (manual)
    // ----------------------------
    function attachManualClickHandler() {
        // usa delegated listener para garantir presença
        $(document).off("click", "#splms-completed-item.splms-attached").on("click", "#splms-completed-item", function (e) {
            e.preventDefault();
            const btn = $(this);
            // evitar duplo-click
            if (btn.hasClass("btn-success") || btn.prop("disabled")) {
                console.log("SPLMS-LOG: Botão já concluído / desabilitado — ignorando clique.");
                return;
            }

            // garante que valores estejam atualizados
            if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
                // tentar ler direto antes de falhar
                const form = document.getElementById("splms-completed-item-form");
                if (form) {
                    const vals = readValuesFromForm(form);
                    SPLMS_ITEM_ID = SPLMS_ITEM_ID || vals.itemId;
                    SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || vals.itemType;
                    SPLMS_USER_ID = SPLMS_USER_ID || vals.userId;
                    SPLMS_COURSE_ID = SPLMS_COURSE_ID || vals.courseId
                }
            }

            if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
                console.error("SPLMS-LOG: Clique manual: não há item_id ou item_type para enviar.");
                alert("Não foi possível marcar a aula como concluída (dados faltando).");
                return;
            }

            btn.prop("disabled", true).text("...");
            //antigo que funciona
            // doCompleteAjax(SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, function (res) {
            //     markButtonCompleted(res && res.content ? res.content : undefined);
            //     // optional: mostrar notificação custom
            //     if (typeof mostrarAlertaConclusao === "function") {
            //         try { mostrarAlertaConclusao(); } catch (e) { console.warn(e); }
            //     }
            //     // atualizar barra
            //     if (typeof loadCourseProgress === "function") {
            //         // tenta buscar course id
            //         let courseId = new URLSearchParams(window.location.search).get("id");
            //         if (!courseId) {
            //             const m = window.location.pathname.match(/\/courses\/(\d+)/);
            //             if (m) courseId = m[1];
            //         }
            //         if (courseId) loadCourseProgress(courseId);
            //     }
            // }, function (err) {
            //     console.error("SPLMS-LOG: Falha ao marcar aula manualmente:", err);
            //     const btn2 = $("#splms-completed-item");
            //     if (btn2 && btn2.length) btn2.prop("disabled", false).text(cfg.text_complete || 'Marcar Concluída');
            // });
            doCompleteAjax(SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, function (res) {
                markButtonCompleted(res && res.content ? res.content : undefined);
                // optional: mostrar notificação custom
                if (typeof mostrarAlertaConclusao === "function") {
                    try { mostrarAlertaConclusao(); } catch (e) { console.warn(e); }
                }
                // atualizar barra
                if (typeof loadCourseProgress === "function") {
                    // tenta buscar course id
                    let courseId = new URLSearchParams(window.location.search).get("id");
                    if (!courseId) {
                        const m = window.location.pathname.match(/\/courses\/(\d+)/);
                        if (m) courseId = m[1];
                    }
                    if (courseId) loadCourseProgress(courseId);
                }
            }, function (err) {
                console.error("SPLMS-LOG: Falha ao marcar aula manualmente:", err);

                let errorMsg = "Erro ao concluir aula. Verifique o console.";
                if (err && err.content) { errorMsg = err.content; }
                else if (typeof err === "string") { errorMsg = err; }
                alert("Falha no Servidor:\\n" + errorMsg);

                const btn2 = $("#splms-completed-item");
                if (btn2 && btn2.length) btn2.prop("disabled", false).text(cfg.text_complete || 'Marcar Concluída');
            });

        }).addClass("splms-attached");
    }

    // ----------------------------
    // Esconde botão quando houver vídeo
    // ----------------------------
    function hideButtonWhenVideo() {
        const hasFWDevPlayer = $("#splmsVideoPlayer").length > 0;
        const hasIframeYoutube = $("iframe[src*='youtube']").length > 0;

        if (hasFWDevPlayer || hasIframeYoutube) {
            console.log("SPLMS: Vídeo detectado — escondendo botão manual.");
            $("#splms-completed-item").hide();
        } else {
            // mostra caso esteja escondido
            $("#splms-completed-item").show();
        }
    }
    // ----------------------------
    // Integração com FWDEVPlayer - ERICK - 16/03
    // ----------------------------
    function bindFWDEVPlayer() {

    // 🔥 espera player existir
    if (!window.splmsVideoPlayer1 || !window.splmsVideoPlayer1.addListener) {
        console.warn("SPLMS-DEBUG: player ainda não pronto...");
        setTimeout(bindFWDEVPlayer, 300);
        return;
    }

    console.log("SPLMS-DEBUG: player OK");

    let alreadyCompleted = false;

    let lastPercent = 0;
    let accumulatedPercent = 0;

    window.splmsVideoPlayer1.addListener("update", function (e) {
        //ANTIGO
        // if (!e || typeof e.percent === "undefined") return;

        // const currentPercent = Number(e.percent) * 100;
        // const delta = currentPercent - lastPercent;

        // console.log("SPLMS-DEBUG:", {
        //     currentPercent: currentPercent.toFixed(2),
        //     lastPercent: lastPercent.toFixed(2),
        //     delta: delta.toFixed(2)
        // });

        // // 🚨 DETECTAR SEEK (pulo grande)
        // if (delta > 5 || delta < 0) {

        //     console.warn("SPLMS-DEBUG: SEEK DETECTADO 🚫");

        //     // não acumula progresso
        // } else {

        //     // 🎯 acumula só progresso real
        //     accumulatedPercent += delta;
        // }

        // lastPercent = currentPercent;

        // // evita passar de 100
        // if (accumulatedPercent > 100) {
        //     accumulatedPercent = 100;
        // }

        // console.log("SPLMS-DEBUG: progresso real:", accumulatedPercent.toFixed(2) + "%");
        //ERICK NOVO CODIGO ANTI ARRASTADA - ERICK - 04/04
        if (!e || typeof e.currentTime === "undefined") return;

        const currentTime = Number(e.currentTime); // tempo atual do vídeo em segundos
        let deltaTime = currentTime - lastTime;

        // ignora micro flutuações negativas ou lag
        if (deltaTime < 0) deltaTime = 0;

        // acumula somente tempo real assistido
        accumulatedTime += deltaTime;

        // limita ao tempo total do vídeo
        if (accumulatedTime > videoDuration) accumulatedTime = videoDuration;

        lastTime = currentTime;

        const accumulatedPercent = (accumulatedTime / videoDuration) * 100;

        console.log("SPLMS-DEBUG: progresso real:", accumulatedPercent.toFixed(2) + "%");
        // ✅ conclusão real
        if (!alreadyCompleted && accumulatedPercent >= SPLMS_PERCENTUAL_MINIMO) {

            alreadyCompleted = true;

            console.log("SPLMS-DEBUG: CONCLUINDO AULA ✅");

            doCompleteAjax(SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, function (res) {

                console.log("SPLMS-DEBUG: resposta AJAX", res);

                markButtonCompleted(res && res.content ? res.content : undefined);
                markLessonAsCompletedInList(SPLMS_ITEM_ID);

                if (typeof mostrarAlertaConclusao === "function") {
                    mostrarAlertaConclusao();
                }

            });
        }

    });

    // ⏸ reset ao pausar (evita bug de delta gigante)
    window.splmsVideoPlayer1.addListener("pause", function () {
        console.log("SPLMS-DEBUG: pause");
        lastPercent = 0;
    });

}
    // // ----------------------------
    // // Integração com FWDEVPlayer OLD FUNCIONANDO
    // // ----------------------------
    // function bindFWDEVPlayer() {
    //     console.log("SPLMS-LOG: Aguardando FWDEVPlayer para bind (se houver)...");
    //     let attempts = 0;
    //     const maxAttempts = 50;

    //     const t = setInterval(function () {
    //         attempts++;
    //         if (attempts >= maxAttempts) {
    //             clearInterval(t);
    //             console.warn("SPLMS-LOG: FWDEVPlayer não encontrado dentro do tempo limite.");
    //             return;
    //         }

    //         if (window.splmsVideoPlayer1 && typeof window.splmsVideoPlayer1.addListener === "function") {
    //             clearInterval(t);
    //             console.log("SPLMS-LOG: FWDEVPlayer detectado — vinculando listeners.");

    //             let alreadyCompleted = false;

    //             try {
    //                 window.splmsVideoPlayer1.addListener("update", function (e) {
    //                     if (!e || typeof e.percent === "undefined") return;
    //                     const percent = Number(e.percent) * 100;
    //                     // debug leve
    //                     // console.log("SPLMS-LOG: progresso vídeo:", percent.toFixed(1) + "%");
    //                     if (!alreadyCompleted && percent >= SPLMS_PERCENTUAL_MINIMO) {
    //                         alreadyCompleted = true;
    //                         console.log("SPLMS-LOG: Percentual atingido (" + percent.toFixed(1) + "%) — executando conclusão automática.");
    //                         // tenta garantir valores antes de enviar
    //                         if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
    //                             const formNow = document.getElementById("splms-completed-item-form");
    //                             if (formNow) {
    //                                 const vals = readValuesFromForm(formNow);
    //                                 SPLMS_ITEM_ID = SPLMS_ITEM_ID || vals.itemId;
    //                                 SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || vals.itemType;
    //                                 SPLMS_USER_ID = SPLMS_USER_ID || vals.userId;
    //                             } else {
    //                                 // tentar inputs fora do form
    //                                 const found = findHiddenInputsAnywhere();
    //                                 SPLMS_ITEM_ID = SPLMS_ITEM_ID || found.itemId;
    //                                 SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || found.itemType;
    //                                 SPLMS_USER_ID = SPLMS_USER_ID || found.userId;
    //                             }
    //                         }

    //                         if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
    //                             console.error("SPLMS-LOG: Impossível concluir automaticamente — item_id/item_type ausentes.");
    //                             return;
    //                         }

    //                         doCompleteAjax(SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, function (res) {
    //                             markButtonCompleted(res && res.content ? res.content : undefined);
    //                             //nova linha Erick 21-12 para aula concluida visual na lista
    //                             markLessonAsCompletedInList(SPLMS_ITEM_ID);
    //                             //fim
    //                             if (typeof mostrarAlertaConclusao === "function") {
    //                                 try { mostrarAlertaConclusao(); } catch (e) { console.warn(e); }
    //                             }
    //                         }, function (err) {
    //                             console.warn("SPLMS-LOG: Falha na conclusão automática:", err);
    //                         });
    //                     }
    //                 });
    //             } catch (e) {
    //                 console.warn("SPLMS-LOG: Erro ao adicionar listener 'update':", e);
    //             }
    //             /* completa quando acaba o video
    //             try {
    //                 window.splmsVideoPlayer1.addListener("playComplete", function () {
    //                     console.log("SPLMS-LOG: Evento playComplete recebido.");
    //                     if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
    //                         const f = document.getElementById("splms-completed-item-form");
    //                         if (f) {
    //                             const v = readValuesFromForm(f);
    //                             SPLMS_ITEM_ID = SPLMS_ITEM_ID || v.itemId;
    //                             SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || v.itemType;
    //                         } else {
    //                             const found = findHiddenInputsAnywhere();
    //                             SPLMS_ITEM_ID = SPLMS_ITEM_ID || found.itemId;
    //                             SPLMS_ITEM_TYPE = SPLMS_ITEM_TYPE || found.itemType;
    //                         }
    //                     }

    //                     if (!SPLMS_ITEM_ID || !SPLMS_ITEM_TYPE) {
    //                         console.error("SPLMS-LOG: playComplete: item_id/item_type ausentes — não marcou.");
    //                         return;
    //                     }

    //                     doCompleteAjax(SPLMS_ITEM_ID, SPLMS_ITEM_TYPE, function (res) {
    //                         markButtonCompleted(res && res.content ? res.content : undefined);
    //                         if (typeof mostrarAlertaConclusao === "function") {
    //                             try { mostrarAlertaConclusao(); } catch (e) { console.warn(e); }
    //                         }
    //                     }, function (err) {
    //                         console.warn("SPLMS-LOG: Falha no playComplete AJAX:", err);
    //                     });
    //                 });
    //             } catch (e) {
    //                 console.warn("SPLMS-LOG: Erro ao adicionar listener 'playComplete':", e);
    //             }*/
    //         }
    //     }, 300);
    // }
    //Erick 21-12 funcao visual de positivo quando aula esta concluida -- EDIT 31-01 ERICK OBSOLETO 
    function markLessonAsCompletedInList(lessonId) {
        console.log('[SPLMS-LOG] Tentando marcar aula concluída na lista:', lessonId);

        const lessonItem = document.querySelector(
            '.lesson[data-lesson-id="' + lessonId + '"]'
        );

        if (!lessonItem) {
            console.warn('[SPLMS-LOG] Aula não encontrada na lista:', lessonId);
            return;
        }

        lessonItem.classList.add('lesson-completed');

        const titleEl = lessonItem.querySelector('.lesson-title');

        if (!titleEl) {
            console.warn('[SPLMS-LOG] .lesson-title não encontrado para:', lessonId);
            return;
        }

        if (titleEl.querySelector('.lesson-completed-icon')) {
            console.log('[SPLMS-LOG] Aula já estava marcada como concluída.');
            return;
        }

        const icon = document.createElement('span');
        icon.className = 'lesson-completed-icon';
        icon.textContent = ' ✅';

        titleEl.appendChild(icon);

        console.log('[SPLMS-LOG] Aula marcada como concluída com sucesso:', lessonId);
    }
    // ERICK 31-01 NOVA FUNCAO MARCADORA DE AULAS CONLUIDAS OU PENDENTE
    function applyLessonState(lessonId, state) {
        const lessonItem = document.querySelector(
            '.lesson[data-lesson-id="' + lessonId + '"]'
        );

        if (!lessonItem) return;

        // limpa estados anteriores
        lessonItem.classList.remove(
            'lesson-completed',
            'lesson-pending'
        );

        const titleEl = lessonItem.querySelector('.lesson-title');
        if (!titleEl) return;

        titleEl.querySelectorAll(
            '.lesson-completed-icon, .lesson-pending-icon'
        ).forEach(el => el.remove());

        if (state === 1) {
            lessonItem.classList.add('lesson-completed');
            titleEl.insertAdjacentHTML(
                'beforeend',
                '<span class="lesson-completed-icon"> ✅</span>'
            );
        }

        if (state === 2) {
            lessonItem.classList.add('lesson-pending');
            titleEl.insertAdjacentHTML(
                'beforeend',
                '<span class="lesson-pending-icon"> ⏳</span>'
            );
        }
    }
    //fim
    // ----------------------------
    // Inicialização geral
    // ----------------------------
    $(document).ready(function () {
        console.log("SPLMS: Inicialização (document ready).");

        // 1️⃣ tenta carregar contexto via PHP
        const contextLoaded = bootstrapLessonContext();

        // 🔴 EARLY EXIT CORRETO
        if (window.SPLMS_CONTEXT?.isCompleted === true) {
            console.log("SPLMS: Aula já concluída — fluxo de conclusão ignorado.");
            return;
        }

        if (!contextLoaded) {
            console.warn("SPLMS-LOG: Contexto PHP não encontrado, tentando via DOM/form.");
        }

        // 2️⃣ fallback antigo (form / inputs / botão)
        locateFormAndValues(function (ok) {
            if (!ok && !contextLoaded) {
                console.error("❌ SPLMS-ERRO CRÍTICO: Nenhuma fonte de contexto disponível.");
            }

            attachManualClickHandler();
           // hideButtonWhenVideo();
            bindFWDEVPlayer();
        });
    }); ''

});
