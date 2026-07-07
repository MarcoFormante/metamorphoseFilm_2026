import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['deleteBtn', 'imageID'];

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.onDeleteClick = this.onDeleteClick.bind(this);
        
        this.draggedImage = null;
        this.droppedImage = null;
    }

    connect() {
        this.images = this.element.querySelectorAll("img");
        this.deleteButtons = this.element.querySelectorAll("button.delete-button");

        this.images.forEach(img => {
            img.addEventListener("dragstart", this.onDragStart);
            img.addEventListener("dragover", this.onDragOver);
            img.addEventListener("drop", this.onDrop);
        });

        this.deleteButtons.forEach(btn => {
            btn.addEventListener('click', this.onDeleteClick);
        });
    }

    disconnect() {
        // Pulizia completa di TUTTI i listener
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
        const img = e.target.closest("img");
        if (img && this.draggedImage !== img) {
            this.draggedImage = img;
        }
    }

    onDragOver(e) {
        e.preventDefault();
        const img = e.target.closest("img");
        if (img && img !== this.draggedImage) {
            this.droppedImage = img;
        }
    }

    onDrop(e) {
        e.preventDefault();
        if (!this.draggedImage || !this.droppedImage) return;

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
        const btn = e.currentTarget; 
        
        this.imageIDTarget.value = btn.dataset.id;
        this.deleteBtnTarget.click();
    }
}