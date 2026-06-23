import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['container', 'fileInput'];

    initialize() {
        this.onFileChange.bind(this)
    }

    connect() {
        this.fileInputTarget.addEventListener("change",(e)=>this.onFileChange(e,this.containerTarget))
    }


    disconnect() {
       
    }

    onFileChange(e,container){
        const files = e.target.files
        if (files.length) {
            for (let index = 0; index < files.length; index++) {
                container.innerHTML += `<img src='${URL.createObjectURL(files[index])}' width='200' heigth='130' alt=''/>`
            }
        }else{
                container.innerHTML = "";
        }
    }
}
