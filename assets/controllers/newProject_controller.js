import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const files = this.element.querySelectorAll("input[type=file]");
        files.forEach(file => {
            file.addEventListener('change',(e)=>{
                //if video input changes
                if (e.target.classList.contains("inpt-bg-video") ) {

                    const video = document.createElement("video");    
                    if (!this.element.querySelector(".video-bg")) {
                        e.target.parentNode.appendChild(video);
                        video.width = 300
                        video.height = 200
                        video.controls = true
                        video.classList.add("video-bg")
                        video.src = URL.createObjectURL(e.target.files[0])
                    }else{
                        if ( e.target.files[0] !== undefined) {
                            this.element.querySelector(".video-bg").src = URL.createObjectURL(e.target.files[0])
                        }
                    }
                }
                 //if image input changes
                if(e.target.name.includes("image")){
                    const className = e.target.name
                    
                    if (!e.target.parentNode.querySelector("img")) {
                        const image = document.createElement("img"); 
                        image.classList.add(className)
                        e.target.parentNode.appendChild(image);
                        image.width = 300
                        image.height = 200
                        image.src = URL.createObjectURL(e.target.files[0])
                    }else{
                        if (e.target.files[0] !== undefined) {
                            e.target.parentNode.querySelector("img").src = URL.createObjectURL(e.target.files[0])
                        }
                    }
                }
                
            })
       })

       
        this.element.querySelector(".add_staff").addEventListener("click",() => createStaffNodes(this.element))
        const lastImages = this.element.querySelectorAll("input[data-last].inpt-img");
        if (lastImages.length) {
            lastImages.forEach(li => {
                const lastImageValue = li.dataset.last
                const parentNode = li.parentNode
                const image = document.createElement("img"); 
                const className = li.name
                image.classList.add(className)
                parentNode.appendChild(image);
                image.width = 300
                image.height = 200
                image.src = "/uploads/images/projects/" + lastImageValue
            })           
        }

        const lastVideo = this.element.querySelector("input[data-last].inpt-bg-video");
         if (lastVideo) {
            const lastVideoValue = lastVideo.dataset.last
            const video = document.createElement("video");  
            lastVideo.parentNode.appendChild(video);
            video.width = 300
            video.height = 200
            video.controls = true
            video.classList.add("video-bg")
            video.src = "/uploads/videos/" + lastVideoValue
        }
    }
}



function createStaffNodes(element){
    const newStaffContainer = element.querySelector(".new_staff_container");
    const childsLength = element.querySelectorAll(".new_staff_item").length;
    const container = element.querySelector(".new_staff_item");
    const cloneNode = container.cloneNode(true)

    cloneNode.querySelectorAll("label")[0].htmlFor += childsLength 
    cloneNode.querySelectorAll("label")[1].htmlFor += childsLength 
    cloneNode.querySelectorAll("input")[0].id += childsLength 
    cloneNode.querySelectorAll("input")[1].id += childsLength 
    cloneNode.querySelectorAll("input")[0].value = "" 
    cloneNode.querySelectorAll("input")[1].value = "" 

    newStaffContainer.appendChild(cloneNode);
}