/*
 *Revised modal dialog.
 *
 *Requires cu.renderDom and prototype.
 *
 *Renders a single instance of the dialog on first show and shares
 *that instance for all future calls.
 *
 *Can be triggered via javascript show / hide or links in the page
 *if the initializeLinks is called. 
 */

var modalDialog = {
    MODAL_ID:"modal_dialog",
    BACKING_ID:"leightbox-overlay",

    yPos : 0,
    xPos : 0,

    _yesCallback:function(){},
    _noCallback:function(){},

    _isShowing:false,
    _isDialogRendered:false,
    _isBackingRendered:false,
    _isIe:false,
    init: function(){
        if(!this._isDialogRendered){
            this.renderDialogMarkup();
        }
        if(!this._isBackingRendered){
            this.renderBackingMarkup();
        }
    },
    _getDialog: function(){ return $(this.MODAL_ID); },
    _getBacking: function(){ return $(this.BACKING_ID); },
    _getBackingIframe: function(){ return $('modalDialogIframe'); },
    _getUpdatedBacking: function(){
        var backing = this._getBacking();
        return backing;
    },
    _setTitle: function(title){
        $("confirmTitle").innerHTML = title;
    },
    _setBody: function(bodyText){        
        $("confirmBody").innerHTML = bodyText;
    },
    renderDialogMarkup: function(){

        var body = $$("body")[0];
        body.appendChild(cu.renderDom({type:"iframe",properties:{id:"modalDialogIframe",style:"display:none;position:absolute;visibility:hidden"}}));
    //    var dialog = cu.renderDom(structure);
    //    body.appendChild(dialog);
        this._isDialogRendered = true;
    },
    renderBackingMarkup: function(){
        $$('body')[0].appendChild(cu.renderDom({type:"div",properties:{id:this.BACKING_ID}}));
        this._isBackingRendered = true;
    },
    show: function(title, question, yesCallback, noCallback){
        try{
            this.init();
            this._setBody(question);
            this._setTitle(title);
            this._yesCallback = yesCallback;
            this._noCallback = noCallback;
            var dialog = this._getDialog();
            var height = dialog.getHeight();
            var width = dialog.getWidth();
            var marginVal = ((0-Math.ceil(height/2))+'px 0 0 '+(0-Math.ceil(width/2))+'px');

						//position the box
            dialog.setStyle({
                margin: marginVal,
                display:"block"
            });
            
            // hide the rest of the other stuff
            $(document.body).addClassName('modalised')

            var iframe = this._getBackingIframe();
            Element.clonePosition(iframe, dialog);
            iframe.style.display="block";
            this._getUpdatedBacking().style.display="block";
        }catch(e){
           //squash
        }
    },
    
    /*hide the dialog and the backing*/
    hide: function(){
        this._getDialog().setStyle({display:"none"});
        this._getBacking().setStyle({display:"none"});
        this._getBackingIframe().setStyle({display:"none"});
        $(document.body).removeClassName('modalised')        
    },
    
    /*trigger one of the callbacks after a user clicks the links*/
    action: function(outcome){
        this.hide();
        if(outcome){
            this._yesCallback();
        } else {
            this._noCallback();
        }
        this._yesCallback=function(){};
        this._noCallback=function(){};
    },
    
    /*use a link to define what the dialog should do*/
    showFromLink: function(e){
        var func = null;
        var el = Event.element(e);
        var title = (el.hasAttribute('msgTitle'))?el.getAttribute('msgTitle'):"";
        var body = (el.hasAttribute('msgBody'))?el.getAttribute('msgBody'):"";
        if(el.hasAttribute("usesOnclick")){
            func = el.onclick;
        } else if (el.hasAttribute('href')) {
            var href = el.getAttribute('href') + '&confirm=1';
            func = function(){document.location.href = href};
        }
        this.show(title, body, func, function(){});
    },
    /*convert page links to triggers for the modal dialog*/
    initializeLinks:function(){
        $$(".lbOn").each(function(item){
            if(!item.hasAttribute("marked")){
                item.observe('click',modalDialog.showFromLink.bind(modalDialog));
                if(item.hasAttribute('onclick')){
                    item.setAttribute("usesOnclick",true);
                } else {
                    item.onclick=function(){return false;}
                }
                item.setAttribute("marked", true)
            }
        });
    }
}

// onload is now in toys.