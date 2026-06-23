import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    initialize() {
        this.cards = this.element.querySelectorAll('button')
        this.turnCard = this.turnCard.bind(this)
    }

    connect() {
        this.cards.forEach(card => {
            card.addEventListener("click",this.turnCard)
        });
    }

    disconnect() {
      
    }

    turnCard({currentTarget}){
        if (!currentTarget.classList.contains("services-card-btn-on")) {
            currentTarget.ariaExpanded = true
            currentTarget.ariaPressed = true
            currentTarget.querySelector(".card-front").ariaHidden = true;
            currentTarget.querySelector(".card-back").ariaHidden = false;
            
        }else{
            currentTarget.ariaExpanded = false
            currentTarget.ariaPressed = false
             currentTarget.querySelector(".card-front").ariaHidden = false;
            currentTarget.querySelector(".card-back").ariaHidden = true;
        }
        currentTarget.classList.toggle("services-card-btn-on")
        
    }
}
