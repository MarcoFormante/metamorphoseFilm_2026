import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['banner', 'icon', 'btnAcc', 'btnRef'];
    
    initialize() {
        this.toggleBanner = this.toggleBanner.bind(this);
        this.onAcceptClick = this.onAcceptClick.bind(this);
        this.onRefuseClick = this.onRefuseClick.bind(this);
    }

    connect() {
        const cookieStatus = this.getCookie('cookie-consent');
        const isNotDecided = cookieStatus === '';
        
        this.iconTarget.addEventListener('click', this.toggleBanner);
        this.btnAccTarget.addEventListener('click', this.onAcceptClick);
        this.btnRefTarget.addEventListener('click', this.onRefuseClick);

        if (isNotDecided) {
            this.showBanner();
        }
    }

    disconnect() {
        this.iconTarget.removeEventListener('click', this.toggleBanner);
        this.btnAccTarget.removeEventListener('click', this.onAcceptClick);
        this.btnRefTarget.removeEventListener('click', this.onRefuseClick);
    }

    onAcceptClick() {
        this.clickBtn('accept');
    }

    onRefuseClick() {
        this.clickBtn('refuse');
    }

    setCookie(cname, cvalue) {
        const d = new Date();
        d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        document.location.reload();
    }

    getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    showBanner() {
        this.bannerTarget.classList.remove("cookie-banner-hidden");
    }

    toggleBanner() {
        this.bannerTarget.classList.toggle("cookie-banner-hidden");
    }

    clickBtn(type) {
        if (type === 'accept') {
            this.setCookie("cookie-consent", "true");
        } else {
            this.setCookie("cookie-consent", "false");
        }
    }
}