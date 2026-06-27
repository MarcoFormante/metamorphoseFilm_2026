import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['banner', 'icon','btnAcc','btnRef'];
    
    initialize() {
        this.setCookie = this.setCookie.bind(this)
        this.getCookie = this.getCookie.bind(this)
        this.toggleBanner = this.toggleBanner.bind(this)
        this.clickBtn = this.clickBtn.bind(this)
        this.showBanner = this.showBanner.bind(this)
    }

    connect() {
        const isConsented = this.getCookie('cookie-consent') === 'true';
        const isRefused = this.getCookie('cookie-consent') === 'false';
        const isNotDecided = this.getCookie('cookie-consent') === ''
        
        this.iconTarget.addEventListener('click',this.toggleBanner)
        this.btnAccTarget.addEventListener('click',()=>this.clickBtn('accept'))
        this.btnRefTarget.addEventListener('click',()=>this.clickBtn('refuse'))
        if (isNotDecided) {
            this.showBanner()
        }
    }

    

    disconnect() {
        this.iconTarget.removeEventListener('click',this.toggleBanner)
        this.btnAccTarget.removeEventListener('click',()=>this.clickBtn('accept'))
        this.btnRefTarget.removeEventListener('click',()=>this.clickBtn('refuse'))
    }

    setCookie(cname, cvalue) {
        document.cookie = cname + "=" + cvalue + ";"  + ";path=/";
        document.location.reload()
    }

    getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
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

    showBanner(){
        this.bannerTarget.classList.remove("cookie-banner-hidden")
    }

    toggleBanner(){
        this.bannerTarget.classList.toggle("cookie-banner-hidden")
    }

    clickBtn(type){
        if (type === 'accept') {
            this.setCookie("cookie-consent","true");
        }else{
            this.setCookie("cookie-consent","false");
        }
    }
}
