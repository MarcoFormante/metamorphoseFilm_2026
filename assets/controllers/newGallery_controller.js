import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['fileInput', 'image'];

    initialize() {
       this.onFileChange.bind(this)
       this.lastImagePath = this.imageTarget.src
    }

    connect() {
        this.fileInputTarget.addEventListener("change",(e)=>this.onFileChange(e,this.imageTarget,this.lastImagePath))
    }

   
    disconnect() {
       this.fileInputTarget.removeEventListener("change",(e)=>this.onFileChange(e,this.imageTarget,this.lastImagePath))
    }

    onFileChange(e,img,lastPath){
        const file = e.target.files[0]
        if (file) {
           img.src = URL.createObjectURL(file);
        }else{
            img.src  = lastPath
        }
    }
}
