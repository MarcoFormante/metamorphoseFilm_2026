import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.handleKeyDown = this.handleKeyDown.bind(this);
        this.handleNextPrev = this.handleNextPrev.bind(this);
        this.exit = this.exit.bind(this);
        this.onImageClick = this.onImageClick.bind(this);
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
        this.descText = this.element.querySelector('.img-desc');
        this.descContainer = this.element.querySelector('.image-desc-container');
    }

    connect() {
        this.images = this.element.querySelectorAll('.gallery-img');
        this.imageContainer = this.element.querySelector(".show-gallery");
        this.newImage = this.element.querySelector(".show-gallery img");
        this.buttons = this.element.querySelectorAll("button");
        this.exitBtn = this.element.querySelector(".show-gallery-exit");
        
        this.index = 0;
        this.count = this.images.length;

        this.images.forEach((img, i) => {
            img.dataset.galleryIndex = i; // 
            img.addEventListener("click", this.onImageClick);
        });

        this.buttons.forEach((btn) => {
            btn.addEventListener("click", this.handleNextPrev);
        });

        if (this.exitBtn) this.exitBtn.addEventListener("click", this.exit);
        window.addEventListener("keydown", this.handleKeyDown);
    }

    disconnect() {
        this.images.forEach((img) => {
            img.removeEventListener("click", this.onImageClick);
        });

        this.buttons.forEach((btn) => {
            btn.removeEventListener("click", this.handleNextPrev);
        });

        if (this.exitBtn) this.exitBtn.removeEventListener("click", this.exit);
        window.removeEventListener('keydown', this.handleKeyDown);
    }

    onImageClick(e) {
        const img = e.currentTarget;
      
        const idx = parseInt(img.dataset.galleryIndex, 10);
        this.showImage(img.src, idx);
    }

    showImage(src, index) {
        this.newImage.src = src;
       
        this.index = index;
        this.updateIndices();
        this.imageContainer.classList.add("show-gallery-on");
    }

    updateIndices() {
        this.nextIndex = (this.index + 1) > this.count - 1 ? 0 : this.index + 1;
        this.prevIndex = (this.index - 1) < 0 ? this.count - 1 : this.index - 1;
        const desc = this.images[this.index].dataset.description 
        this.descText.innerText = desc
        if (!desc) {
            this.descContainer.classList.add('image-desc-container-no-desc')
        }else{
             this.descContainer.classList.remove('image-desc-container-no-desc')
        }
    }

    exit() {
        if (this.imageContainer && this.imageContainer.classList.contains("show-gallery-on")) {
            this.imageContainer.classList.remove("show-gallery-on");
            this.newImage.src = "";
        }
    }

    handleKeyDown(e) {
     if (!this.imageContainer.classList.contains("show-gallery-on")) return;

        if (e.code === "Escape") {
            this.exit();
        } else if (e.code === "ArrowRight") {
            this.next(); 
        } else if (e.code === "ArrowLeft") {
            this.prev(); 
        }
    }

    handleNextPrev({ currentTarget }) {
        if (currentTarget.classList.contains("show-gallery-btn-right")) {
            this.next();
        } else if (currentTarget.classList.contains("show-gallery-btn-left")) {
            this.prev();
        }
    }

    next(){
        this.index = this.nextIndex;
        if (this.images[this.index]) {
            this.newImage.src = this.images[this.index].src;
            this.updateIndices();
        }
    }

    prev(){
        this.index = this.prevIndex;
        if (this.images[this.index]) {
            this.newImage.src = this.images[this.index].src;
            this.updateIndices();
        }
    }
}