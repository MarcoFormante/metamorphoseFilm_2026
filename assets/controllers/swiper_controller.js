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
            speed: 1000
        });
    }

    disconnect() {
        if (this.swiper) {
            this.swiper.destroy(true, true);
        }
    }
}