import '../scss/admin.scss';

const enhanceAdminDashboard = (): void => {
    const root = document.querySelector<HTMLElement>('.sympress-demo-admin');

    if (root === null) {
        return;
    }

    const availableComponents = root.querySelectorAll<HTMLElement>(
        '[data-component-status="available"]',
    );

    root.dataset.ready = 'true';
    root.style.setProperty(
        '--sympress-demo-available-components',
        String(availableComponents.length),
    );
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceAdminDashboard, { once: true });
} else {
    enhanceAdminDashboard();
}
