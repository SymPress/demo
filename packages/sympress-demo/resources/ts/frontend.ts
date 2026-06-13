import '../scss/frontend.scss';

const enhanceNotes = (): void => {
    document.querySelectorAll<HTMLElement>('.sympress-demo-notes').forEach((element) => {
        const cards = element.querySelectorAll<HTMLElement>('.sympress-demo-notes__item');

        element.dataset.enhanced = 'true';
        element.style.setProperty('--sympress-demo-note-count', String(cards.length));
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceNotes, { once: true });
} else {
    enhanceNotes();
}
