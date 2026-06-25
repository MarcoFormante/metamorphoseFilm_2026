import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['fileInput', 'image'];

    initialize() {
       this.onFileChange = this.onFileChange.bind(this)
       this.lastImagePath =  this.imageTarget.src
       this.image = this.imageTarget 
    }

    connect() {
        this.fileInputTarget.addEventListener("change",this.onFileChange)
    }

   
    disconnect() {
       this.fileInputTarget.removeEventListener("change",this.onFileChange)
    }

    onFileChange(e){
        const file = this.image ? e.target.files[0] : null
        if (file) {
           this.image.src = URL.createObjectURL(file);
           this.image.classList.remove('newGallery-img-hidden')
        }else{
            this.image.src  = this.lastImagePath
        }
    }
}
