import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.galleries = this.element.querySelectorAll(".admin-gallery-container");
        this.draggedGallery = null;
        this.droppedGallery = null;
    }

    connect() {
        
        this.galleries.forEach(img => {
            img.addEventListener("dragstart",this.onDragStart)

            img.addEventListener("dragover",this.onDragOver)

            img.addEventListener("drop",this.onDrop)
        });

    }

    disconnect() {
       this.galleries.forEach(img => {
            img.removeEventListener("dragstart",this.onDragStart)

            img.removeEventListener("dragover",this.onDragOver)

            img.removeEventListener("drop",this.onDrop)
        });
    }


     onDragStart(e){
        if (this.draggedGallery !== e.target && !this.draggedGallery) {
            this.draggedGallery = e.target
        }
    }

    onDragOver(e){
        e.preventDefault()
        if (e.target !== this.draggedGallery ) {
            this.droppedGallery = e.target
        }
    }

    onDrop(){
        const data = [
            {
                id: this.draggedGallery.dataset.id,
                position: this.droppedGallery.dataset.position
            },
            {
                id: this.droppedGallery.dataset.id,
                position: this.draggedGallery.dataset.position
            },
        ]
        
        const inputs = this.element.querySelectorAll("input.form-control");
        
        inputs[0].value = data[0].id
        inputs[1].value = data[0].position
        inputs[2].value = data[1].id
        inputs[3].value = data[1].position
        
        this.element.querySelector("button#item_position_submit").click()
        this.draggedGallery = null
        this.droppedGallery = null
    }
}
