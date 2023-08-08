import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import RegularEvent from "@typo3/core/event/regular-event.js";
 class AddUserLocation{
   constructor() {
     this.selector = ".dashboard-item";
     this.initialize()
   }
   initialize() {
     new RegularEvent("widgetContentRendered", (function (e) {
       e.preventDefault();
       if(this.getAttribute('data-widget-key') !== 'weatherWidget'){
         return false;
       }
       let button = this.querySelector('#weatherWidgetSetLocation');
       self = this;
       button.addEventListener('click', function (){
         let location = self.querySelector('#weatherWidgetLocation').value;
         new AjaxRequest(TYPO3.settings.ajaxUrls.add_userlocation).withQueryArguments(
           {
             location: location,
             t: Date.now()
           }).get();
       })
     })).delegateTo(document, this.selector)
   }
 }
 export default new AddUserLocation();
