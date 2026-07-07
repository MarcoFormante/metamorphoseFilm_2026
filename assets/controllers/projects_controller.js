import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        
        this.draggedProject = null;
        this.droppedProject = null;
    }

    connect() {
        this.projects = this.element.querySelectorAll("a");

        this.projects.forEach(p => {
            p.addEventListener("dragstart", this.onDragStart);
            p.addEventListener("dragover", this.onDragOver);
            p.addEventListener("drop", this.onDrop);
        });
    }

    disconnect() {
        // Rimozione speculare e pulizia totale della memoria
        this.projects.forEach(p => {
            p.removeEventListener("dragstart", this.onDragStart);
            p.removeEventListener("dragover", this.onDragOver);
            p.removeEventListener("drop", this.onDrop);
        });
    }

    onDragStart(e) {
        const link = e.target.closest("a");
        if (link && this.draggedProject !== link) {
            this.draggedProject = link;
        }
    }

    onDragOver(e) {
        e.preventDefault();
        const link = e.target.closest("a");
        if (link && link !== this.draggedProject) {
            this.droppedProject = link;
        }
    }

    onDrop(e) {
        e.preventDefault();
        if (!this.draggedProject || !this.droppedProject) return;

        const data = [
            {
                id: this.draggedProject.dataset.id,
                position: this.droppedProject.dataset.position
            },
            {
                id: this.droppedProject.dataset.id,
                position: this.draggedProject.dataset.position
            },
        ];
        
        const inputs = this.element.querySelectorAll("input.form-control");
        
        if (inputs.length >= 4) {
            inputs[0].value = data[0].id;
            inputs[1].value = data[0].position;
            inputs[2].value = data[1].id;
            inputs[3].value = data[1].position;

            const submitBtn = this.element.querySelector("button");
            if (submitBtn) submitBtn.click();
        }

        this.draggedProject = null;
        this.droppedProject = null;
    }
}