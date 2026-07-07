import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.addRealYTBVideo = this.addRealYTBVideo.bind(this);
    }

    connect() {
        this.videoCover = this.element.querySelector('.video-blocker');
        
        if (this.videoCover) {
            this.videoCover.addEventListener('click', this.addRealYTBVideo);
        }
    }

    disconnect() {
        if (this.videoCover) {
            this.videoCover.removeEventListener('click', this.addRealYTBVideo);
        }
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