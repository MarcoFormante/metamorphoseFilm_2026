import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.images = this.element.querySelectorAll('.gallery-img')
        this.index = 0;
        this.imageContainer = this.element.querySelector(".show-gallery")
        this.newImage = this.element.querySelector(".show-gallery img")
        this.showImage = this.showImage.bind(this)
        this.handleKeyDown = this.handleKeyDown.bind(this)
        this.buttons =  this.element.querySelectorAll("button")
        this.handleNextPrev = this.handleNextPrev.bind(this)
        this.exit = this.exit.bind(this)
        this.count = this.images.length
        this.exitBtn = this.element.querySelector(".show-gallery-exit")
    }

    connect() {
       this.images.forEach((img,i) => {
            img.addEventListener("click",()=>this.showImage(img,i))
       });

       this.buttons.forEach((btn) => {
            btn.addEventListener("click",this.handleNextPrev)
       })

        this.exitBtn.addEventListener("click",this.exit)

        window.addEventListener("keydown",this.handleKeyDown)
    }

   
    disconnect() {
        window.removeEventListener('keydown',this.handleKeyDown)
    }


    showImage(img,index){
        const src = img.src
        this.newImage.src = src
        this.index = index
        this.nextIndex = (index + 1) > this.count - 1 ? 0 : index + 1 
        this.prevIndex = index - 1 < 0 ? this.count - 1 : index - 1
        this.imageContainer.classList.add("show-gallery-on")
    }

    exit(){
        if (this.imageContainer.classList.contains("show-gallery-on")) {
            this.imageContainer.classList.remove("show-gallery-on")
            this.newImage.src = ""
        }
    }

    handleKeyDown(e) {
        if (e.code === "Escape" && this.imageContainer.classList.contains("show-gallery-on")) {
            this.exit();
        }
    }

    handleNextPrev({target}){
        if (target.classList.contains("show-gallery-btn-right")) {
            this.newImage.src = this.images[this.nextIndex].src
            this.index = this.nextIndex
            this.nextIndex = (this.index + 1) > this.count - 1 ? 0 : this.index + 1 
            this.prevIndex = this.index - 1 < 0 ? this.count - 1 : this.index - 1
            
        }
        else if (target.classList.contains("show-gallery-btn-left")) {
            this.newImage.src = this.images[this.prevIndex].src
            this.index = this.prevIndex
            this.nextIndex = (this.index + 1) > this.count - 1 ? 0 : this.index + 1 
            this.prevIndex = this.index - 1 < 0 ? this.count - 2 : this.index - 1
            console.log(this.index);
        }
    }

    
}
