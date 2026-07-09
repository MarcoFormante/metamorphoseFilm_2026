import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.onDeleteClick = this.onDeleteClick.bind(this);
        
        this.draggedVideo = null;
        this.droppedVideo = null;
    }

    connect() {
        this.videos = this.element.querySelectorAll(".video-blocker");
        this.deleteBtns = this.element.querySelectorAll(".delete-btn");
        this.deleteForm = this.element.querySelector('form[name=delete_service_video]');

        this.videos.forEach(video => {
            video.addEventListener("dragstart", this.onDragStart);
            video.addEventListener("dragover", this.onDragOver);
            video.addEventListener("drop", this.onDrop);
        });

        this.deleteBtns.forEach(btn => {
            btn.addEventListener('click', this.onDeleteClick);
        });
    }

    disconnect() {
        this.videos.forEach(video => {
            video.removeEventListener("dragstart", this.onDragStart);
            video.removeEventListener("dragover", this.onDragOver);
            video.removeEventListener("drop", this.onDrop);
        });

        this.deleteBtns.forEach(btn => {
            btn.removeEventListener('click', this.onDeleteClick);
        });
    }

    onDragStart(e) {
        if (this.draggedVideo !== e.currentTarget && !this.draggedVideo) {
            this.draggedVideo = e.currentTarget;
        }
    }

    onDragOver(e) {
        e.preventDefault();
        if (e.currentTarget !== this.draggedVideo) {
            this.droppedVideo = e.currentTarget;
        }
    }

    onDrop(e) {
        e.preventDefault();
        if (!this.draggedVideo || !this.droppedVideo) return;

        const data = [
            {
                id: this.draggedVideo.dataset.id,
                position: this.droppedVideo.dataset.position
            },
            {
                id: this.droppedVideo.dataset.id,
                position: this.draggedVideo.dataset.position
            },
        ];
        
        const inputs = this.element.querySelectorAll("input");
        
        if (inputs.length >= 4) {
            inputs[0].value = data[0].id;
            inputs[1].value = data[0].position;
            inputs[2].value = data[1].id;
            inputs[3].value = data[1].position;
            
            const submitBtn = this.element.querySelector("button#item_position_submit");
            if (submitBtn) submitBtn.click();
        }
        
        this.draggedVideo = null;
        this.droppedVideo = null;
    }

    onDeleteClick(e) {
        e.preventDefault();
        if (!this.deleteForm) return;

        const id = e.currentTarget.dataset.id;
        this.deleteForm.action = `/admin/services/${id}/delete`;
        
        const submitDelete = this.element.querySelector('.deleteform-delete-btn');
        if (submitDelete) submitDelete.click();
    }
}