/**
 * Admin Course Architect JS
 */

document.addEventListener('DOMContentLoaded', function () {
    try {
        const generateBtn = document.getElementById('splms-ai-generate-btn');
        const downloadDocBtn = document.getElementById('splms-ai-download-doc');

        const loadingDiv = document.getElementById('splms-ai-loading');
        const resultsDiv = document.getElementById('splms-ai-results');
        const previewContainer = document.getElementById('splms-ai-preview-content');

        // Manual Trigger Logic for AI Architect Modal
        const aiTriggerBtn = document.getElementById('splms-ai-trigger-btn');
        const aiModalEl = document.getElementById('splmsAiArchitectModal');

        if (aiTriggerBtn) {
            aiTriggerBtn.addEventListener('click', function (e) {
                if (!aiModalEl) return;

                // Try Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    try {
                        const modal = new bootstrap.Modal(aiModalEl);
                        modal.show();
                        return;
                    } catch (err) { console.warn(err); }
                }

                // Try jQuery (Bootstrap 3/4)
                if (typeof jQuery !== 'undefined' && jQuery(aiModalEl).modal) {
                    jQuery(aiModalEl).modal('show');
                    return;
                }

                // Fallback: Force CSS display
                aiModalEl.style.display = 'block';
                aiModalEl.classList.add('show');
                aiModalEl.classList.add('in');
                aiModalEl.style.opacity = '1';

                // Backdrop
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show in';
                    document.body.appendChild(backdrop);
                }

                // Close logic
                const closeBtns = aiModalEl.querySelectorAll('.btn-close, .close, [data-dismiss="modal"]');
                closeBtns.forEach(btn => {
                    btn.onclick = function () {
                        aiModalEl.style.display = 'none';
                        aiModalEl.classList.remove('show');
                        aiModalEl.classList.remove('in');
                        if (backdrop) backdrop.remove();
                    };
                });
            });
        }

        let currentStructure = null;
        let cachedHtmlContent = '';

        if (generateBtn) {
            generateBtn.addEventListener('click', function () {
                const topic = document.getElementById('ai_topic').value;
                const audience = document.getElementById('ai_audience').value;
                const objectives = document.getElementById('ai_objectives').value;
                const language = document.getElementById('ai_language').value;

                if (!topic) {
                    alert('Por favor, informe pelo menos o Tópico.');
                    return;
                }

                // UI State
                loadingDiv.classList.remove('d-none');
                resultsDiv.classList.add('d-none');
                generateBtn.disabled = true;

                // Ajax Call to Joomla Controller
                const data = new FormData();
                data.append('option', 'com_splms');
                data.append('task', 'course.generateAiStructure');
                data.append('topic', topic);
                data.append('audience', audience);
                data.append('objectives', objectives);
                data.append('language', language);

                fetch('index.php', {
                    method: 'POST',
                    body: data
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            currentStructure = res.data;
                            renderPreview(currentStructure);
                            prepareDownloadContent(currentStructure, topic, audience, objectives);



                            resultsDiv.classList.remove('d-none');
                        } else {
                            alert('Erro na IA: ' + (res.message || 'Desconhecido'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro de comunicação.');
                    })
                    .finally(() => {
                        loadingDiv.classList.add('d-none');
                        generateBtn.disabled = false;
                    });
            });
        }

        function prepareDownloadContent(structure, topic, audience, objectives) {
            cachedHtmlContent = `
                <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                <head>
                    <meta charset="utf-8">
                    <title>${topic}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        h1 { color: #2e3e4e; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
                        h2 { color: #27ae60; margin-top: 30px; }
                        h3 { color: #333; margin-top: 20px; text-decoration: underline; }
                        .meta { color: #666; font-style: italic; margin-bottom: 20px; }
                        .lesson { margin-left: 20px; margin-bottom: 15px; border-left: 3px solid #eee; padding-left: 10px; }
                        .duration { font-weight: bold; font-size: 0.9em; color: #555; }
                    </style>
                </head>
                <body>
                    <h1>Proposta de Curso: ${topic}</h1>
                    
                    <div class="meta">
                        <p><strong>Público-Alvo:</strong> ${audience}</p>
                        <p><strong>Objetivos:</strong> ${objectives}</p>
                    </div>
                    <hr>
            `;

            if (structure.sections) {
                structure.sections.forEach((section, i) => {
                    cachedHtmlContent += `
                        <h2>Módulo ${i + 1}: ${section.title}</h2>
                        <p>${section.description || ''}</p>
                    `;
                    if (section.lessons) {
                        section.lessons.forEach((lesson, j) => {
                            cachedHtmlContent += `
                                <div class="lesson">
                                    <h3>Aula ${j + 1}: ${lesson.title}</h3>
                                    <p class="duration">Duração Estimada: ${lesson.duration}</p>
                                    <p>${lesson.description}</p>
                                </div>
                            `;
                        });
                    }
                });
            }
            cachedHtmlContent += '</body></html>';
        }

        if (downloadDocBtn) {
            downloadDocBtn.addEventListener('click', function () {
                if (!currentStructure || !cachedHtmlContent) return;

                const topic = document.getElementById('ai_topic').value;
                const filename = `Proposta_Curso_${topic.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.doc`;

                const blob = new Blob(['\ufeff', cachedHtmlContent], { type: 'application/msword' });

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        function renderPreview(data) {
            if (!data.sections) return;

            let html = '<div class="accordion" id="aiAccordion">';

            data.sections.forEach((section, index) => {
                html += `
                    <div class="accordion-item card mb-2" style="border: 1px solid #dee2e6;">
                        <h2 class="accordion-header card-header" id="heading${index}" style="background-color: #f8f9fa;">
                            <button class="accordion-button btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" 
                                    data-toggle="collapse" data-target="#collapse${index}" aria-expanded="false" 
                                    style="color: #212529; text-decoration: none; font-weight: bold;">
                                ${section.title}
                            </button>
                        </h2>
                        <div id="collapse${index}" class="accordion-collapse collapse" data-bs-parent="#aiAccordion" data-parent="#aiAccordion">
                            <div class="accordion-body card-body" style="background-color: #fff; color: #212529;">
                                <p style="color: #495057; border-bottom: 1px solid #eee; padding-bottom: 10px;">${section.description}</p>
                                <ul class="list-group list-group-flush">
                `;

                if (section.lessons) {
                    section.lessons.forEach(lesson => {
                        html += `
                            <li class="list-group-item" style="border: none; border-bottom: 1px solid #f1f1f1;">
                                <div style="font-weight: 500; color: #212529;">
                                    <i class="fa fa-play-circle me-2" style="color: #0d6efd;"></i> ${lesson.title} 
                                    <small class="float-end float-right text-muted" style="font-weight: normal;">(${lesson.duration || 'N/A'})</small>
                                </div>
                                <div class="small ps-4" style="color: #6c757d; margin-top: 5px;">${lesson.description}</div>
                            </li>
                        `;
                    });
                }

                html += `
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            previewContainer.innerHTML = html;
        }
    } catch (e) {
        console.error("SplmsAI Error: ", e);
    }
});

