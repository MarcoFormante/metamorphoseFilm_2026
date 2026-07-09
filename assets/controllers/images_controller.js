import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['deleteBtn', 'imageID'];

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.onDeleteClick = this.onDeleteClick.bind(this);
        this.onClickImage = this.onClickImage.bind(this)
        this.draggedImage = null;
        this.droppedImage = null;
        this.modBtn = this.element.querySelector('.admin-gallery-mod-btn');
        this.toggleMod = this.toggleMod.bind(this)
        this.addDescBtn = this.element.querySelector('.admin-gallery-add-desc-btn')
        this.modSelection = false

        this.selectedImages = []
    }

    connect() {
        this.images = this.element.querySelectorAll("img");
        this.deleteButtons = this.element.querySelectorAll("button.delete-button");

        this.images.forEach(img => {
            img.parentNode.addEventListener('click',this.onClickImage)
            img.addEventListener("dragstart", this.onDragStart);
            img.addEventListener("dragover", this.onDragOver);
            img.addEventListener("drop", this.onDrop);
        });

        this.deleteButtons.forEach(btn => {
            btn.addEventListener('click', this.onDeleteClick);
        });

        this.modBtn.addEventListener('click',this.toggleMod)
    }

    disconnect() {
        this.images.forEach(img => {
            img.removeEventListener("dragstart", this.onDragStart);
            img.removeEventListener("dragover", this.onDragOver);
            img.removeEventListener("drop", this.onDrop);
        });

        this.deleteButtons.forEach(btn => {
            btn.removeEventListener('click', this.onDeleteClick);
        });
    }

    onDragStart(e) {
        if (this.modSelection === false) {
            const img = e.target.closest("img");
            if (img && this.draggedImage !== img) {
                this.draggedImage = img;
            }   
        }
        
    }

    onDragOver(e) {
        e.preventDefault();
        if (this.modSelection === false ) {
            const img = e.target.closest("img");
            if (img && img !== this.draggedImage) {
                this.droppedImage = img;
            }
        }
    }

    onDrop(e) {
        e.preventDefault();
        if (this.modSelection === true || !this.draggedImage || !this.droppedImage) return;
        
        console.log("asdasd");
        
        const data = [
            {
                id: this.draggedImage.dataset.id,
                position: this.droppedImage.dataset.position
            },
            {
                id: this.droppedImage.dataset.id,
                position: this.draggedImage.dataset.position
            },
        ];
        
        const inputs = this.element.querySelectorAll("input.form-control");
        
        if (inputs.length >= 4) {
            inputs[0].value = data[0].id;
            inputs[1].value = data[0].position;
            inputs[2].value = data[1].id;
            inputs[3].value = data[1].position;
            
            const submitBtn = this.element.querySelector("button#item_position_submit");
            if (submitBtn) submitBtn.click();
        }
        
        this.draggedImage = null;
        this.droppedImage = null;
    }

    onDeleteClick(e) {
        e.preventDefault();
        if (!this.modSelection) {
            const btn = e.currentTarget; 
            this.imageIDTarget.value = btn.dataset.id;
            this.deleteBtnTarget.click();
        }
    }
    
    onClickImage(e){
        if (this.modSelection) {
            e.currentTarget.classList.toggle('image-selected');
            const selectedID = e.currentTarget.querySelector('img').dataset.id
            console.log(e.currentTarget.classList.contains('image-selected'));
            
            if (e.currentTarget.classList.contains('image-selected')) {
                this.selectedImages.push(selectedID)
            }else{
                const filteredImages = this.selectedImages.filter(id => id !== selectedID )
                this.selectedImages = filteredImages                
            }
        }
        
        if (this.selectedImages.length) {
            this.addDescBtn.classList.remove('hidden')
            this.addDescBtn.href = '/admin/gallery/add-desc?ids=' + this.selectedImages + '&count=' + this.selectedImages.length 
        }else{
            this.addDescBtn.classList.add('hidden')
             this.addDescBtn.href = '/admin/gallery/add-desc?'
        }


        
    }

    toggleMod(){
        this.modSelection = !this.modSelection
        this.modBtn.dataset.selected = this.modSelection

        this.modBtn.innerText = this.modSelection ? 'Mode Normal' : 'Mode Selection'
        
        this.deleteButtons.forEach(btn => {
            btn.classList.toggle('hidden')
        })

        
    }

}