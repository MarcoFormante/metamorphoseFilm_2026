import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect(){
        const projects = this.element.querySelectorAll("a");
        let draggedProject = null
        let droppedProject = null
       
       
        
        
        projects.forEach(p => {
            p.addEventListener("dragstart",(e)=>{
                if (draggedProject !== e.target && !draggedProject) {
                    draggedProject = e.target
                }
            })

            p.addEventListener("dragover",(e)=>{
                e.preventDefault()
                if (e.target !== draggedProject ) {
                    droppedProject = e.target
                }
            })

            p.addEventListener("drop",(e)=>{
                const data = [
                    {
                        id:draggedProject.dataset.id,
                        position:droppedProject.dataset.position
                    },
                    {
                        id:droppedProject.dataset.id,
                        position:draggedProject.dataset.position
                    },
                ]
                
                const inputs = this.element.querySelectorAll("input.form-control");
                
                inputs[0].value = data[0].id
                inputs[1].value = data[0].position
                inputs[2].value = data[1].id
                inputs[3].value = data[1].position

                this.element.querySelector("button").click()

                draggedProject = null
                droppedProject = null
            })
        });
    }
}


