import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.onFileChange = this.onFileChange.bind(this);
        this.addStaffRow = this.addStaffRow.bind(this);
    }

    connect() {
        this.fileInputs = this.element.querySelectorAll("input[type=file]");
        this.addStaffBtn = this.element.querySelector(".add_staff");

        this.fileInputs.forEach(file => {
            file.addEventListener('change', this.onFileChange);
        });

        if (this.addStaffBtn) {
            this.addStaffBtn.addEventListener("click", this.addStaffRow);
        }

        this.renderInitialPreviews();
    }

    disconnect() {
        this.fileInputs.forEach(file => {
            file.removeEventListener('change', this.onFileChange);
        });

        if (this.addStaffBtn) {
            this.addStaffBtn.removeEventListener("click", this.addStaffRow);
        }
    }

    onFileChange(e) {
        const input = e.currentTarget;
        const file = input.files[0];
        const parent = input.parentNode;

        if (input.classList.contains("inpt-bg-video")) {
            let video = this.element.querySelector(".video-bg");
            if (!video) {
                video = document.createElement("video");
                video.width = 300;
                video.height = 200;
                video.controls = true;
                video.classList.add("video-bg");
                parent.appendChild(video);
            }
            if (file){
                video.src = URL.createObjectURL(file);
            } else{
                video.src = "/uploads/videos/" + e.currentTarget.dataset.last;
            }
        }

        if (input.name.includes("image")) {
            let image = parent.querySelector("img");
            if (!image) {
                image = document.createElement("img");
                image.width = 300;
                image.height = 200;
                parent.appendChild(image);
            }
            if (file){
                image.src = URL.createObjectURL(file);
            }else{
                image.src = "/uploads/images/projects/" + e.currentTarget.dataset.last;
            }
        }
    }

    renderInitialPreviews() {
        const lastImages = this.element.querySelectorAll("input[data-last].inpt-img");
        lastImages.forEach(li => {
            const image = document.createElement("img");
            image.width = 300;
            image.height = 200;
            image.src = "/uploads/images/projects/" + li.dataset.last;
            li.parentNode.appendChild(image);
        });

        const lastVideo = this.element.querySelector("input[data-last].inpt-bg-video");
        if (lastVideo) {
            const video = document.createElement("video");
            video.width = 300;
            video.height = 200;
            video.controls = true;
            video.classList.add("video-bg");
            video.src = "/uploads/videos/" + lastVideo.dataset.last;
            lastVideo.parentNode.appendChild(video);
        }

        const lastImageCover = this.element.querySelector("input[data-last].inpt-img-cover")
        const image = document.createElement("img");
        image.width = 300;
        image.height = 200;
        image.src = "/uploads/images/projects/" + lastImageCover.dataset.last;
        lastImageCover.parentNode.appendChild(image);
        
    }

    addStaffRow() {
        const newStaffContainer = this.element.querySelector(".new_staff_container");
        const container = this.element.querySelector(".new_staff_item");
        
        if (!newStaffContainer || !container) return;

        const childsLength = this.element.querySelectorAll(".new_staff_item").length;
        const cloneNode = container.cloneNode(true);

        const labels = cloneNode.querySelectorAll("label");
        const inputs = cloneNode.querySelectorAll("input");

        if (labels.length >= 2) {
            labels[0].htmlFor += childsLength;
            labels[1].htmlFor += childsLength;
        }
        if (inputs.length >= 2) {
            inputs[0].id += childsLength;
            inputs[1].id += childsLength;
            inputs[0].value = "";
            inputs[1].value = "";
        }

        newStaffContainer.appendChild(cloneNode);
    }
}