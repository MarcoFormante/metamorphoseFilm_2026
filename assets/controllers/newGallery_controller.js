import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['fileInput', 'image'];

    initialize() {
        this.onFileChange = this.onFileChange.bind(this);
        this.lastImagePath = this.imageTarget.src;
    }

    connect() {
        this.fileInputTarget.addEventListener("change", this.onFileChange);
    }

    disconnect() {
        this.fileInputTarget.removeEventListener("change", this.onFileChange);
    }

    onFileChange(e) {
        const file = e.target.files[0];
        
        if (file) {
            this.imageTarget.src = URL.createObjectURL(file);
            this.imageTarget.classList.remove('newGallery-img-hidden');
        } else {
            this.imageTarget.src = this.lastImagePath;
            this.imageTarget.classList.add('newGallery-img-hidden');
        }
    }
}