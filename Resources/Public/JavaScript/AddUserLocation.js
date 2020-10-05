define(["require", "TYPO3/CMS/Core/Ajax/AjaxRequest", "TYPO3/CMS/Core/Event/RegularEvent"], (function (e, AjaxRequest, RegularEvent) {
  "use strict";
  return new class {
    constructor() {
      this.selector = ".dashboard-item",
      this.initialize()
    }

    initialize() {
      new RegularEvent("widgetContentRendered", (function (e) {
        e.preventDefault();
        // Generate a random number between 1 and 32
        let button = this.querySelector('#weatherWidgetSetLocation');
        self = this;
        button.addEventListener('click', function (e){
         // e.preventDefault();
          let location = self.querySelector('#weatherWidgetLocation').value;
          new AjaxRequest(TYPO3.settings.ajaxUrls.add_userlocation).withQueryArguments({location: location}).get();
        })
      })).delegateTo(document, this.selector)
    }
  }
}));
