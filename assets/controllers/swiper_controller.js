import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper';
import Keyboard from 'swiper/modules/keyboard';
import Mousewheel from 'swiper/modules/mousewheel';
import 'swiper/css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
   
    
    connect() {
        this.swiper = new Swiper(this.element, {
            modules: [Keyboard, Mousewheel],
            mousewheel: true,
            slidesPerView: 1,
            direction: 'vertical',
            keyboard: {
                enabled: true,
            },
            speed: 1000,
            on: {
                init: function () {
                    playActiveVideo(this);
                },
                slideChange: function () {
                    playActiveVideo(this);
                }
            },
            
        });
    }

    disconnect() {
        if (this.swiper) {
            this.swiper.destroy(true, true);
        }
    }

   
}



 function playActiveVideo(swiperInstance) {
    
   const videos = swiperInstance.el.querySelectorAll('video')
    videos.forEach(video => {
        video.pause();
        video.removeAttribute('src')
        video.load()
    });
    
    const activeSlide = swiperInstance.slides[swiperInstance.activeIndex];
    if (!activeSlide) {
        return;
    }
    
    const activeVideo = activeSlide.querySelector('video');
    if (!activeVideo) {
        return;
    }

    if (activeVideo.dataset.src) {
        activeVideo.src = activeVideo.dataset.src;
    }
    activeVideo.play().catch(() => {
        // Autoplay blocked or not allowed by browser
    });
}