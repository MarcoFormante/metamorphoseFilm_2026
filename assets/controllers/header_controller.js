import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['linksContainer', 'hamburger'];
    
    initialize() {
        this.toggleMenu = this.toggleMenu.bind(this);
    }

    connect() {
        this.hamburgerTarget.addEventListener('click', this.toggleMenu);
    }

    disconnect() {
        this.hamburgerTarget.removeEventListener('click', this.toggleMenu);
    }

    toggleMenu() {
        const isOpen = this.linksContainerTarget.classList.toggle('header-links-container-open');
        
        this.hamburgerTarget.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        this.hamburgerTarget.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    }
}