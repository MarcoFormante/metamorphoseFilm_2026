import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.videoCover = this.element.querySelector('.video-blocker')
        this.addRealYTBVideo = this.addRealYTBVideo.bind(this)
    }

    connect() {
        if (this.videoCover) {
            this.videoCover.addEventListener('click',this.addRealYTBVideo)
        }
    }

    

    disconnect() {
        this.videoCover.removeEventListener('click',this.addRealYTBVideo)
    }

    addRealYTBVideo() {
        const videoId = this.videoCover.getAttribute('data-video-id');
    
        if (videoId) {
            this.videoCover.innerHTML = `
            <iframe 
                width="100%" 
                height="100%" 
                src="https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen
                style="position: absolute; top:0; left:0; width:100%; height:100%;">
            </iframe>`;
        }
    }

}
