import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    initialize() {
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDrop = this.onDrop.bind(this);
        this.onDeleteClick = this.onDeleteClick.bind(this);
        this.videos = this.element.querySelectorAll(".video-blocker");
        this.draggedVideo = null;
        this.droppedVideo = null;
        this.deleteBtns = this.element.querySelectorAll(".delete-btn");
        this.deleteForm = this.element.querySelector('form[name=delete_service_video]');
        
        
    }

    connect(){
      
        this.videos.forEach(video => {
            video.addEventListener("dragstart",this.onDragStart)

            video.addEventListener("dragover",this.onDragOver)

            video.addEventListener("drop",this.onDrop)
        });


        this.deleteBtns.forEach(btn => {
            btn.addEventListener('click',this.onDeleteClick);
        })
    }

    disconnect() {
         this.videos.forEach(video => {
            video.removeEventListener("dragstart",this.onDragStart)

            video.removeEventListener("dragover",this.onDragOver)

            video.removeEventListener("drop",this.onDrop)
        });
    }

    onDragStart(e){
        
        if (this.draggedVideo !== e.currentTarget && !this.draggedVideo) {
            this.draggedVideo = e.currentTarget
        }
    }

    onDragOver(e){
        e.preventDefault()
        if (e.target !== this.draggedVideo ) {
            this.droppedVideo = e.currentTarget
        }
    }

    onDrop(){
        const data = [
            {
                id: this.draggedVideo.dataset.id,
                position: this.droppedVideo.dataset.position
            },
            {
                id: this.droppedVideo.dataset.id,
                position: this.draggedVideo.dataset.position
            },
        ]
        
        const inputs = this.element.querySelectorAll("input");
        console.log(inputs);
        
        inputs[0].value = data[0].id
        inputs[1].value = data[0].position
        inputs[2].value = data[1].id
        inputs[3].value = data[1].position
        
        this.element.querySelector("button#item_position_submit").click()
        this.draggedVideo = null
        this.droppedVideo = null
    }


    onDeleteClick(e){
            e.preventDefault()
            const id = e.currentTarget.dataset.id
            this.deleteForm.action = `/admin/services/${id}/delete`
            this.element.querySelector('.deleteform-delete-btn').click()
        }
}
