var GuidewayNotifications = {

    containerId: 'gw-notification-container',
    cssPath: 'components/com_splms/assets/css/notifications.css',

    init: function () {
        this.injectCSS();
        this.createContainer();
    },

    injectCSS: function () {
        if (!document.querySelector(`link[href="${this.cssPath}"]`)) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = this.cssPath;
            document.head.appendChild(link);
        }
    },

    createContainer: function () {
        if (!document.getElementById(this.containerId)) {
            var container = document.createElement('div');
            container.id = this.containerId;
            document.body.appendChild(container);
        }
    },

    /**
     * Show a notification
     * @param {string} type - 'success', 'error', 'warning', 'info'
     * @param {string} message - The message to display
     * @param {number} duration - Duration in ms (default 5000), 0 for persistent
     */
    show: function (type, message, duration = 5000) {
        // Ensure initialized
        this.init();

        const container = document.getElementById(this.containerId);
        const notification = document.createElement('div');

        // Icon mapping
        const titles = {
            'success': 'Success',
            'error': 'Error',
            'warning': 'Warning',
            'info': 'Info'
        };

        notification.className = `gw-notification gw-${type}`;
        notification.innerHTML = `
            <div class="gw-notification-icon"><i class="gw-icon-${type}"></i></div>
            <div class="gw-notification-content">
                <span class="gw-notification-title">${titles[type] || 'Notification'}</span>
                <span class="gw-notification-message">${this.escapeHtml(message)}</span>
            </div>
            <button class="gw-notification-close" onclick="this.parentElement.remove()">✕</button>
        `;

        container.appendChild(notification);

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(notification);
            }, duration);
        }
    },

    dismiss: function (element) {
        if (element) {
            element.classList.add('gw-hiding');
            element.addEventListener('animationend', function () {
                if (element.parentElement) {
                    element.remove();
                }
            });
        }
    },

    escapeHtml: function (text) {
        if (!text) return text;
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};

// Inicializa ao carregar
document.addEventListener('DOMContentLoaded', function () {
    GuidewayNotifications.init();
});
