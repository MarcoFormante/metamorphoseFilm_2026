import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['deleteBtn', 'imageID'];

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.onDeleteClick = this.onDeleteClick.bind(this);
        this.images = this.element.querySelectorAll("img");
        this.draggedImage = null;
        this.droppedImage = null;
    }

    connect(){
      
        let draggedImage = null
        let droppedImage = null
        
        this.images.forEach(img => {
            img.addEventListener("dragstart",this.onDragStart)

            img.addEventListener("dragover",this.onDragOver)

            img.addEventListener("drop",this.onDrop)
        });

        const deleteButtons = this.element.querySelectorAll("button.delete-button");

        deleteButtons.forEach(btn => {
            btn.addEventListener('click',this.onDeleteClick);
        })
    }

    disconnect() {
         this.images.forEach(img => {
            img.removeEventListener("dragstart",this.onDragStart)

            img.removeEventListener("dragover",this.onDragOver)

            img.removeEventListener("drop",this.onDrop)
        });
    }

    onDragStart(e){
        if (this.draggedImage !== e.target && !this.draggedImage) {
            this.draggedImage = e.target
        }
    }

    onDragOver(e){
        e.preventDefault()
        if (e.target !== this.draggedImage ) {
            this.droppedImage = e.target
        }
    }

    onDrop(){
        const data = [
            {
                id: this.draggedImage.dataset.id,
                position: this.droppedImage.dataset.position
            },
            {
                id: this.droppedImage.dataset.id,
                position: this.draggedImage.dataset.position
            },
        ]
        
        const inputs = this.element.querySelectorAll("input.form-control");
        
        inputs[0].value = data[0].id
        inputs[1].value = data[0].position
        inputs[2].value = data[1].id
        inputs[3].value = data[1].position
        
        this.element.querySelector("button#item_position_submit").click()
        this.draggedImage = null
        this.droppedImage = null
    }


    onDeleteClick(e){
            e.preventDefault()
            const id = e.target.dataset.id
            const galleryName = e.target.dataset.name
            this.imageIDTarget.value = id
            this.deleteBtnTarget.click()
        }
}
