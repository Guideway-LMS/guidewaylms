/**
 * Admin Course Cover Creator JS
 * Handles Image Search via Pexels API in Administrator
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inject Button if it doesn't exist (Backup method if PHP injection fails to place it exactly)
    // For now we assume PHP placed the button #splms-cover-search-trigger, or we attach logic to it.

    const searchBtn = document.getElementById('splms-cover-search-btn');
    const searchInput = document.getElementById('splms-cover-search-input');
    const resultsContainer = document.getElementById('splms-cover-results');
    const loadingSpinner = document.getElementById('splms-cover-loading');

    // Pexels API Key
    const PEXELS_API_KEY = 'eaGCBMkBl0ISSVtFWBORscJXQFXL1DE0OnQkITG8QNhs4PSeJEwb6yPm';

    // Manual Trigger Logic with Debugging
    const triggerBtn = document.getElementById('splms-cover-trigger-btn');
    const modalEl = document.getElementById('splmsAdminCoverModal');

    if (triggerBtn) {
        console.log('SplmsCover: Trigger Button Found');
        triggerBtn.addEventListener('click', function (e) {
            console.log('SplmsCover: Trigger Clicked');

            if (!modalEl) {
                console.error('SplmsCover: Modal Element NOT Found');
                alert('Erro: Modal não encontrado no DOM.');
                return;
            }

            // Try Bootstrap 5
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                console.log('SplmsCover: Attempting Bootstrap 5');
                try {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                    return;
                } catch (err) { console.error(err); }
            }

            // Try jQuery (Bootstrap 3/4)
            if (typeof jQuery !== 'undefined' && jQuery(modalEl).modal) {
                console.log('SplmsCover: Attempting jQuery/CMS Modal');
                jQuery(modalEl).modal('show');
                return;
            }

            // Disclaimer for fallback
            console.log('SplmsCover: Fallback to CSS');

            // Fallback: Force CSS display
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            modalEl.classList.add('in'); // BS3 support
            modalEl.style.opacity = '1';

            // Add Backdrop manually if needed (simple version)
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show in';
                document.body.appendChild(backdrop);
            }

            // Close logic
            const closeBtns = modalEl.querySelectorAll('.btn-close, .close, [data-dismiss="modal"]');
            closeBtns.forEach(btn => {
                btn.onclick = function () {
                    modalEl.style.display = 'none';
                    modalEl.classList.remove('show');
                    modalEl.classList.remove('in');
                    if (backdrop) backdrop.remove();
                };
            });
        });
    } else {
        console.warn('SplmsCover: Trigger Button NOT Found');
    }

    if (searchBtn && searchInput) {
        // Trigger search on button click
        searchBtn.addEventListener('click', function () {
            performSearch();
        });

        // Trigger search on Enter key
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    function performSearch() {
        const query = searchInput.value.trim();
        if (!query) return;

        // UI Reset
        resultsContainer.innerHTML = '';
        loadingSpinner.classList.remove('d-none');

        const url = `https://api.pexels.com/v1/search?query=${encodeURIComponent(query)}&per_page=12&orientation=landscape`;

        fetch(url, {
            headers: {
                Authorization: PEXELS_API_KEY
            }
        })
            .then(response => {
                if (!response.ok) throw new Error('API Error');
                return response.json();
            })
            .then(data => {
                renderResults(data.photos);
            })
            .catch(error => {
                console.error('Error fetching images:', error);
                resultsContainer.innerHTML = '<div class="alert alert-danger">Erro ao buscar imagens. Verifique a chave da API.</div>';
            })
            .finally(() => {
                loadingSpinner.classList.add('d-none');
            });
    }

    function renderResults(photos) {
        if (!photos || photos.length === 0) {
            resultsContainer.innerHTML = '<div class="alert alert-info">Nenhuma imagem encontrada.</div>';
            return;
        }

        const html = photos.map(photo => {
            // Use 'large' (1920px) which is standard web HD. 
            // If still too big, we can try 'medium' but it might be pixelated.
            const imageUrl = photo.src.large;
            const fileName = `course-cover-${photo.id}.jpg`;

            return `
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="splms-cover-item card h-100" style="border:1px solid #ddd; transition: all 0.2s;">
                        <img src="${photo.src.medium}" class="card-img-top" alt="${photo.alt}" style="height: 150px; object-fit: cover; width:100%;">
                        <div class="card-body p-2 text-center bg-white">
                            <small class="text-muted d-block text-truncate mb-2">Por: ${photo.photographer}</small>
                            <button type="button" class="btn btn-sm btn-success w-100" onclick="forceDownload('${imageUrl}', '${fileName}', this)">
                                <span class="icon-download"></span> Baixar (JPG)
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        resultsContainer.innerHTML = `<div class="row">${html}</div>`;
    }

    // Export function to global scope so onclick works
    window.forceDownload = function (url, filename, btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Baixando...';
        btn.disabled = true;

        fetch(url)
            .then(response => response.blob())
            .then(blob => {
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                a.download = filename; // Explicitly set .jpg
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(blobUrl);
                document.body.removeChild(a);

                btn.innerHTML = '<span class="icon-check"></span> Salvo!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2000);
            })
            .catch(err => {
                console.error('Download failed:', err);
                btn.innerHTML = 'Erro ao baixar';
                btn.classList.add('btn-danger');
                alert('Não foi possível baixar automaticamente. Tente clicar com botão direito na imagem de prévia e "Salvar imagem como".');
            });
    };
});
