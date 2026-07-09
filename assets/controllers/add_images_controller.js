import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['container', 'fileInput'];

    initialize() {
        this.onFileChange = this.onFileChange.bind(this);
    }

    connect() {
        this.fileInputTarget.addEventListener("change", this.onFileChange);
    }

    disconnect() {
        this.fileInputTarget.removeEventListener("change", this.onFileChange);
    }

    onFileChange(e) {
        const files = e.target.files;
        this.containerTarget.innerHTML = "";

        if (files.length) {
            for (let index = 0; index < files.length; index++) {
                this.containerTarget.innerHTML += `<img src='${URL.createObjectURL(files[index])}' width='200' height='130' alt=''/>`;
            }
        }
    }
}