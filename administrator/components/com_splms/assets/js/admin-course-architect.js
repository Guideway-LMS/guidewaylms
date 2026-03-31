/**
 * Admin Course Architect JS
 */

document.addEventListener('DOMContentLoaded', function () {
    try {
        const generateBtn = document.getElementById('splms-ai-generate-btn');
        const downloadDocBtn = document.getElementById('splms-ai-download-doc');
        const downloadPdfBtn = document.getElementById('splms-ai-download-pdf');
        const applyBtn = document.getElementById('splms-ai-apply-btn');

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
                        closeModal();
                    };
                });
            });
        }

        function closeModal() {
             if (!aiModalEl) return;
             
             // Bootstrap 5 instance
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                 const modal = bootstrap.Modal.getInstance(aiModalEl);
                 if (modal) { modal.hide(); return; }
            }
            
            // jQuery
            if (typeof jQuery !== 'undefined' && jQuery(aiModalEl).modal) {
                jQuery(aiModalEl).modal('hide');
                return;
            }

            aiModalEl.style.display = 'none';
            aiModalEl.classList.remove('show');
            aiModalEl.classList.remove('in');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }

        let currentStructure = null;
        
        // Removed global cachedHtmlContent in favor of on-demand generation

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
                if (applyBtn) applyBtn.disabled = true;
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
                            
                            resultsDiv.classList.remove('d-none');
                            if (applyBtn) applyBtn.disabled = false;
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



        // Helper to generate HTML for the body content (used in Editor and DOCX)
        function generateCurriculumHtml(structure) {
            if (!structure || !structure.sections) return '';
            
            let html = '';
            
            structure.sections.forEach((section, i) => {
                html += `<h2>Módulo ${i + 1}: ${section.title}</h2>`;
                if (section.description) {
                    html += `<p>${section.description}</p>`;
                }
                
                if (section.lessons) {
                    html += '<div class="lesson-list">';
                    section.lessons.forEach((lesson, j) => {
                        html += `
                            <div class="lesson" style="margin-left: 20px; margin-bottom: 15px; border-left: 3px solid #eee; padding-left: 10px;">
                                <h3>Aula ${j + 1}: ${lesson.title}</h3>
                                <p class="duration"><strong>Duração:</strong> ${lesson.duration || 'N/A'}</p>
                                <p>${lesson.description}</p>
                            </div>
                        `;
                    });
                     html += '</div>';
                }
                html += '<hr>';
            });
            
            return html;
        }

        if (downloadDocBtn) {
            downloadDocBtn.addEventListener('click', function () {
                if (!currentStructure) return;

                const topic = document.getElementById('ai_topic').value;
                const audience = document.getElementById('ai_audience').value;
                const objectives = document.getElementById('ai_objectives').value;
                
                const curriculumHtml = generateCurriculumHtml(currentStructure);
                const filename = `Proposta_Curso_${topic.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.doc`;

                const fullDoc = `
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
                    </style>
                </head>
                <body>
                    <h1>Proposta de Curso: ${topic}</h1>
                    <div class="meta">
                        <p><strong>Público-Alvo:</strong> ${audience}</p>
                        <p><strong>Objetivos:</strong> ${objectives}</p>
                    </div>
                    <hr>
                    ${curriculumHtml}
                </body></html>`;

                const blob = new Blob(['\ufeff', fullDoc], { type: 'application/msword' });

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        if (downloadPdfBtn) {
            downloadPdfBtn.addEventListener('click', function () {
                if (!currentStructure) return;

                const topic = document.getElementById('ai_topic').value;
                const audience = document.getElementById('ai_audience').value;
                const objectives = document.getElementById('ai_objectives').value;

                const originalText = downloadPdfBtn.innerHTML;
                downloadPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Gerando...';
                downloadPdfBtn.disabled = true;

                const data = new FormData();
                data.append('option', 'com_splms');
                data.append('task', 'course.downloadPdf');
                data.append('structure', JSON.stringify(currentStructure));
                data.append('topic', topic);
                data.append('audience', audience);
                data.append('objectives', objectives);

                fetch('index.php', {
                    method: 'POST',
                    body: data
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao gerar PDF (HTTP ' + response.status + ')');
                    }
                    return response.blob();
                })
                .then(blob => {
                    const filename = `Proposta_Curso_${topic.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`;
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao gerar PDF: ' + err.message);
                })
                .finally(() => {
                    downloadPdfBtn.innerHTML = originalText;
                    downloadPdfBtn.disabled = false;
                });
            });
        }
        
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                if (!currentStructure) return;
                
                const topic = document.getElementById('ai_topic').value;
                const audience = document.getElementById('ai_audience').value;
                const objectives = document.getElementById('ai_objectives').value;
                const language = document.getElementById('ai_language').value;
                
                // 1. Title
                // Try standard Joomla form field IDs
                const titleField = document.getElementById('jform_title');
                if (titleField) {
                     titleField.value = topic; 
                     // Or "Curso de " + topic? Keep it simple.
                }

                // 2. Short Description
                const shortDescField = document.getElementById('jform_short_description');
                if (shortDescField) {
                     shortDescField.value = `Público-Alvo: ${audience}\n\nObjetivos:\n${objectives}`;
                }
                
                // 3. Description (Editor)
                const curriculumHtml = `<h3>Sobre o Curso</h3>
                <p>Este curso foi planejado para ${audience}.</p>
                <p><strong>Objetivos:</strong> ${objectives}</p>
                <hr>
                <h3>Conteúdo Programático</h3>
                ${generateCurriculumHtml(currentStructure)}`;
                
                if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                    tinymce.activeEditor.setContent(curriculumHtml);
                } else {
                     // Fallback
                     const descArea = document.getElementById('jform_description');
                     if (descArea) descArea.value = curriculumHtml;
                }
                
                // 4. Language
                /* Naive mapping, can be improved */
                const langField = document.getElementById('jform_language');
                if (langField) {
                    // This often uses chosen/select2, so simply setting value might not update UI visually immediately
                    // but it sets the form value.
                    // Map common values:
                    let langCode = '*'; // All
                    if (language === 'Portuguese') langCode = 'pt-BR';
                    else if (language === 'English') langCode = 'en-GB';
                    else if (language === 'Spanish') langCode = 'es-ES';
                    
                    langField.value = langCode;
                    // Trigger change for chosen/select2
                    langField.dispatchEvent(new Event('change'));
                    if (typeof jQuery !== 'undefined') jQuery(langField).trigger('change').trigger('chosen:updated');
                }
                
                // Close and Notify
                closeModal();
                
                // Use Joomla native alert if possible, or simple alert
                if (window.Joomla && Joomla.renderMessages) {
                    Joomla.renderMessages({'message': ['Conteúdo da IA aplicado ao formulário! Revise e clique em Salvar.']});
                    window.scrollTo(0,0);
                } else {
                    alert('Conteúdo aplicado com sucesso! Revise e salve o formulário.');
                }
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
