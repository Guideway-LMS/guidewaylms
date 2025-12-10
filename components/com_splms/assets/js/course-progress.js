// // /*
// // /**
// //  * Sistema de Barra de Progresso do Curso
// //  * GUIDEWAY CUSTOM - 2025-11-12 - Joshua - Barra de progresso
// //  * JavaScript Vanilla (sem jQuery) - Compatível com Joomla 5
// //  */

// // /**
// //  * Busca o progresso do curso e atualiza a barra
// //  * @param {number} courseId - ID do curso
// //  */
// // function loadCourseProgress(courseId) {
// //     console.log('🔍 Buscando progresso do curso:', courseId);
    
// //     fetch('/guidewaylms/index.php?option=com_splms&task=courses.getCourseProgress&course_id=' + courseId)
// //         .then(response => response.json())
// //         .then(data => {
// //             console.log('✅ Dados recebidos:', data);
            
// //             if (data.success && data.data) {
// //                 updateProgressBar(data.data);
// //             } else {
// //                 console.error('❌ Erro:', data.message);
// //             }
// //         })
// //         .catch(error => {
// //             console.error('❌ Erro ao buscar progresso:', error);
// //         });
// // }

// // /**
// //  * Atualiza a barra de progresso na tela
// //  * @param {object} data - Dados do progresso
// //  */
// // function updateProgressBar(data) {
// //     const { total_aulas, aulas_completas, porcentagem } = data;
    
// //     console.log('📊 Atualizando barra:', porcentagem + '%');
    
// //     // Atualizar a largura da barra
// //     const barra = document.getElementById('course-progress-bar');
// //     if (barra) {
// //         barra.style.width = porcentagem + '%';
// //     }
    
// //     // Atualizar o texto
// //     const texto = document.getElementById('course-progress-text');
// //     if (texto) {
// //         texto.textContent = aulas_completas + ' de ' + total_aulas + ' aulas (' + porcentagem + '%)';
// //     }
    
// //     // Atualizar emoji/status
// //     updateProgressStatus(porcentagem);
// // }

// // /**
// //  * Atualiza emoji e mensagem baseado no progresso
// //  * @param {number} porcentagem - Porcentagem de conclusão
// //  */
// // function updateProgressStatus(porcentagem) {
// //     const emoji = document.getElementById('progress-emoji');
// //     const mensagem = document.getElementById('progress-message');
    
// //     if (!emoji || !mensagem) return;
    
// //     if (porcentagem === 0) {
// //         emoji.textContent = '📝';
// //         mensagem.textContent = 'Comece sua jornada!';
// //     } else if (porcentagem < 30) {
// //         emoji.textContent = '🌱';
// //         mensagem.textContent = 'Ótimo começo!';
// //     } else if (porcentagem < 70) {
// //         emoji.textContent = '🚀';
// //         mensagem.textContent = 'Você está indo muito bem!';
// //     } else if (porcentagem < 100) {
// //         emoji.textContent = '🔥';
// //         mensagem.textContent = 'Quase lá!';
// //     } else {
// //         emoji.textContent = '🎉';
// //         mensagem.textContent = 'Parabéns! Curso completo!';
// //     }
// // }

// // /**
// //  * Inicialização quando a página carregar
// //  */
// // document.addEventListener('DOMContentLoaded', function() {
// //     console.log('🚀 Sistema de Progresso do Curso carregado');
    
// //     // Tentar pegar o course_id da URL
// //     // Tentar pegar o ID do curso da URL
// // let courseId = null;

// // // Método 1: Da query string (?id=1)
// // const urlParams = new URLSearchParams(window.location.search);
// // courseId = urlParams.get('id');

// // // Método 2: Do caminho da URL (/courses/1-nome-do-curso)
// // if (!courseId) {
// //     const pathMatch = window.location.pathname.match(/\/courses\/(\d+)/);
// //     if (pathMatch) {
// //         courseId = pathMatch[1];
// //     }
// // }
// // if (courseId) {
// //         console.log('📚 Curso detectado:', courseId);
// //         loadCourseProgress(courseId);
// //     } else {
// //         console.log('⚠️ ID do curso não encontrado na URL');
// //     }
// // }); 

// /**
//  * Busca o progresso do curso e atualiza a barra
//  * @param {number} courseId - ID do curso
//  */
// function loadCourseProgress(courseId) {
//     console.log('🔍 Buscando progresso do curso:', courseId);
    
//     fetch('/guidewaylms/index.php?option=com_splms&task=courses.getCourseProgress&course_id=' + courseId)
//         .then(response => response.json())
//         .then(data => {
//             console.log('✅ Dados recebidos:', data);
            
//             if (data.success) {
//                 updateProgressBar(data.data); // agora data.data é só a porcentagem
//             } else {
//                 console.error('❌ Erro:', data.message);
//             }
//         })
//         .catch(error => {
//             console.error('❌ Erro ao buscar progresso:', error);
//         });
// }

// /**
//  * Atualiza a barra de progresso na tela
//  * @param {number} porcentagem - Porcentagem de conclusão
//  */
// function updateProgressBar(porcentagem) {
//     console.log('📌 Tipo de porcentagem recebida:', typeof porcentagem, porcentagem);

//     porcentagem = Number(porcentagem) || 0;

//     console.log('📊 Atualizando barra:', porcentagem + '%');

//     // Atualizar a largura da barra
//     const barra = document.getElementById('course-progress-bar');
//     if (barra) {
//         barra.style.width = porcentagem + '%';
//     }

//     // Atualizar o texto
//     const texto = document.getElementById('course-progress-text');
//     if (texto) {
//         texto.textContent = `${porcentagem}% concluído`;
//     }

//     // Atualizar emoji/status
//     updateProgressStatus(porcentagem);
// }

// /**
//  * Atualiza emoji e mensagem baseado no progresso
//  * @param {number} porcentagem
//  */
// function updateProgressStatus(porcentagem) {
//     const emoji = document.getElementById('progress-emoji');
//     const mensagem = document.getElementById('progress-message');

//     if (!emoji || !mensagem) return;

//     if (porcentagem === 0) {
//         emoji.textContent = '📝';
//         mensagem.textContent = 'Comece sua jornada!';
//     } else if (porcentagem < 30) {
//         emoji.textContent = '🌱';
//         mensagem.textContent = 'Ótimo começo!';
//     } else if (porcentagem < 70) {
//         emoji.textContent = '🚀';
//         mensagem.textContent = 'Você está indo muito bem!';
//     } else if (porcentagem < 100) {
//         emoji.textContent = '🔥';
//         mensagem.textContent = 'Quase lá!';
//     } else {
//         emoji.textContent = '🎉';
//         mensagem.textContent = 'Parabéns! Curso completo!';
//     }
// }

// /**
//  * Inicialização quando a página carregar
//  */

// document.addEventListener('DOMContentLoaded', function() {
//     console.log('🚀 Sistema de Progresso do Curso carregado');

//     let courseId = null;

//     // 1) Declarar e ler parâmetros da URL
//     const urlParams = new URLSearchParams(window.location.search);
//     courseId = urlParams.get('course_id');

//     // 2) Se vier com alias, ex: "1:curso-intro"
//     if (courseId) {
//         courseId = courseId.split(':')[0];
//     }

//     // 3) Se não encontrou, tenta via formulário hidden
//     if (!courseId) {
//         const formCourse = document.querySelector("#splms-completed-item-form input[name='course_id']");
//         if (formCourse) {
//             courseId = formCourse.value;
//         }
//     }

//     // 4) Fallback final via regex na URL
//     if (!courseId) {
//         const pathMatch = window.location.pathname.match(/\/courses\/(\d+)/);
//         if (pathMatch) {
//             courseId = pathMatch[1];
//         }
//     }

//     // 5) Se achou, agora SÓ AGORA carrega progresso
//     if (courseId) {
//         console.log('📚 Curso detectado:', courseId);
//         loadCourseProgress(courseId);
//     } else {
//         console.warn('⚠️ Nenhum course_id encontrado na URL, formulário ou path.');
//     }
// });

//novo
/**
 * Retorna o ID do curso atual de forma confiável
 * @returns {string|null}
 */
function getCourseId() {
    // 1️⃣ Do input hidden no form (lição não concluída)
    let courseId = document.querySelector("#splms-completed-item-form input[name='course_id']")?.value;

    // 2️⃣ Do data-attribute no wrapper (lição concluída)
    if (!courseId) {
        courseId = document.querySelector(".splms-lesson-completed-lesson-wrapper")?.dataset.courseId;
    }

    // 3️⃣ Da URL query string
    if (!courseId) {
        const urlParams = new URLSearchParams(window.location.search);
        courseId = urlParams.get('course_id') || urlParams.get('id');
        if (courseId) {
            courseId = courseId.split(':')[0];
        }
    }

    // 4️⃣ Último fallback: regex no pathname
    if (!courseId) {
        const pathMatch = window.location.pathname.match(/\/courses\/(\d+)/);
        if (pathMatch) {
            courseId = pathMatch[1];
        }
    }

    return courseId || null;
}

/**
 * Busca o progresso do curso e atualiza a barra
 * @param {number} courseId - ID do curso
 */
function loadCourseProgress(courseId) {
    console.log('🔍 Buscando progresso do curso:', courseId);

    fetch(`/guidewaylms/index.php?option=com_splms&task=courses.getCourseProgress&course_id=${courseId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateProgressBar(data.data); // data.data é a porcentagem
            } else {
                console.error('❌ Erro ao carregar progresso:', data.message);
            }
        })
        .catch(err => console.error('❌ Erro ao buscar progresso:', err));
}

/**
 * Atualiza a barra de progresso na tela
 * @param {number} porcentagem - Porcentagem de conclusão
 */
function updateProgressBar(porcentagem) {
    porcentagem = Number(porcentagem) || 0;

    const barra = document.getElementById('course-progress-bar');
    const texto = document.getElementById('course-progress-text');

    if (barra) barra.style.width = porcentagem + '%';
    if (texto) texto.textContent = `${porcentagem}% concluído`;

    updateProgressStatus(porcentagem);
}

/**
 * Atualiza emoji e mensagem baseado no progresso
 * @param {number} porcentagem
 */
function updateProgressStatus(porcentagem) {
    const emoji = document.getElementById('progress-emoji');
    const mensagem = document.getElementById('progress-message');
    if (!emoji || !mensagem) return;

    if (porcentagem === 0) {
        emoji.textContent = '📝';
        mensagem.textContent = 'Comece sua jornada!';
    } else if (porcentagem < 30) {
        emoji.textContent = '🌱';
        mensagem.textContent = 'Ótimo começo!';
    } else if (porcentagem < 70) {
        emoji.textContent = '🚀';
        mensagem.textContent = 'Você está indo muito bem!';
    } else if (porcentagem < 100) {
        emoji.textContent = '🔥';
        mensagem.textContent = 'Quase lá!';
    } else {
        emoji.textContent = '🎉';
        mensagem.textContent = 'Parabéns! Curso completo!';
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sistema de Progresso do Curso carregado');

    const courseId = getCourseId();
    if (courseId) {
        console.log('📚 Curso detectado:', courseId);
        loadCourseProgress(courseId);
    } else {
        console.warn('⚠️ Nenhum course_id encontrado na página');
    }
});
