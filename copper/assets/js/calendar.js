cu.calendar = {
    dragged: false,
    useGeneralAvailability:false,
    month:{
        updateUser: function(month, year){
            var id = $F('SwapUserID');
            var params = { "month":month, "year":year, "userid":id, module:"calendar", action:"monthview" }
            document.location.href = cu.url.build("index.php",params);
        },
        updateClient: function(month, year, user){
            var id= $F('swapClientId');
            var params = { "month":month, "year":year, userid: user, module:"calendar", action:"monthview", clientid: id }
            document.location.href = cu.url.build("index.php",params);
        },
        updateProject: function(month, year, user){
            var id = $F('swapProjectId');
            var params = { "month":month, "year":year, userid: user, module:"calendar", action:"monthview", projectid:id }
            document.location.href = cu.url.build("index.php",params);
        }
    },
    week:{
        updateUser: function(){
            var id = $F('SwapUserID');
            var params = { "userid":id, module:"calendar", action:"week" }
            document.location.href = cu.url.build("index.php",params);
        },
        updateClient: function(user){
            var id= $F('swapClientId');
            var params =  { "userid":user, module:"calendar", action:"week", clientid: id }
            document.location.href = cu.url.build("index.php",params);
        },
        updateProject: function(user){
            var id = $F('swapProjectId');
            var params = { "userid":user, module:"calendar", action:"week", projectid: id }
            document.location.href = cu.url.build("index.php",params);
        }
    },
    loadTest:1,
    availShowing: false,
    showAvailability: function(user) {
	// console.log("Showing is " + this.availShowing);
        if(this.availShowing){
            $('availabilityform').getElementsBySelector('.availability').each(function(item){item.setStyle({visibility:"hidden"})});
        } else {
            $('availabilityform').getElementsBySelector('.availability').each(function(item){item.setStyle({visibility:"visible"})});
        }
        this.availShowing = !this.availShowing;
        this.showGeneralAvailability(user);
    },
    handleDrop: function(dropped, target, event){
        tooltip.hide();
        var ul = target.getElementsBySelector("ul");
        if(ul.length<=0){
            var ul = document.createElement("ul");
            target.appendChild(ul);
        } else {
            ul = ul[0];
        }
        dropped.parentNode.removeChild(dropped);
        ul.appendChild(dropped);
        var id = target.id;
        var droppedId = dropped.id;
        var url = cu.url.build("index.php",{module:"calendar", action:"changedate", newdate:id, item:droppedId});
        cu.ajaxGet(url, function(){console.log(arguments)});
        cu.calendar.dragging = false;
        cu.calendar.dragged = true;
        return true;
    },
    init: function(){
//        console.log("init called");

        $$('.draggable').each(function(item){ new Draggable(item, {revert: true, ghosting:true})});
        $$('.draggable a').each(function(item){ item.observe('click',tooltip.trigger.bind(tooltip));});
        $$('.droppable').each(function(item){
            Droppables.add(item, {
                accept: "draggable",
                hoverclass:"droppable-hover",
                onDrop:calendar.handleDrop,
                onDrag:function(){
                    cu.calendar.dragging = true;
                }
            });
        });
        window.document.observe('click',function(e){
            if(!Event.element(e).hasClassName("trigger")){
                tooltip.hide();
            }
        })

				$('availabilityform').observe('submit', function(e) {
					e.stop();
					calendar.saveForm();
				});
    },
    showEventDetails: function(obj){
            if (tooltipOpen && currentEvent==obj) {
                    hideEventDetails();
            } else {
                    if (isDragging == false){
                            tooltipRegisterEvents();
                            var xPos = obj.cumulativeOffset()[0] + 12;
                            var yPos = obj.cumulativeOffset()[1] + 12;
                            $('calendar-tooltip').setStyle({left:xPos+"px",top:yPos+"px"});
                            $('calendar-tooltip').show();
                            tooltipTrap = true;
                            tooltipOpen = true;
                            currentEvent = obj;
                    }
            }
},
    saveForm: function(){
        if(this.useGeneralAvailability){
            this.saveGeneralForm();                
        } else {
            this.saveCalendarForm();
        }            
    },
    saveGeneralForm: function(){
        var form = $('mainForm');
        var params = form.serialize(true);
        params.caller="ajax";
        cu.ajaxPost("index.php?module=calendar&action=resourcesetsave", params, function(response){                
            calendar.saveCalendarForm();
        });
    },
    saveCalendarForm: function(){
        var form = $('availabilityform');
        var params = form.serialize(true);
        cu.ajaxPost("index.php?module=calendar&action=resourceupdatesave", params, function(response){
            var obj = response.responseText.evalJSON();
            if(obj.success == 1){
                // Effect.toggle($('availability'),"blind",{duration:0.5});
                // $('availabilityform').getElementsBySelector('.edit').each(function(item){item.setStyle({visibility:"hidden"})});
		            $('availabilityform').getElementsBySelector('.availability').each(function(item){item.setStyle({visibility:"hidden"})});
								this.availShowing = false;
            }
        }.bind(this)); // bind so we can set the avail showing parameter.
    },

    showGeneralAvailability: function(user){
        if($('availability').down("input") == null){
            var url = "index.php?module=calendar&action=resourceset&userID="+user;
            cu.ajaxGet(url, function(response){
                var target = $('availability');
                target.setStyle({display:"none"});
                target.update(response.responseText);
                Effect.toggle(target,"blind",{duration:0.5});
            });
        }else {
            Effect.toggle($('availability'),"blind",{duration:0.5});
        }
    }
}

cu.tooltip = {
    _currentTarget: null,
    _isOpen: false,
    _getTooltipNode: function(){ return $('calendar-tooltip'); },
    _getDetailNode: function(id){ return $(id+'_detail'); },
    _move: function(){},
    TIME_TEMPLATE:"From: ${from},<br/> To: ${to}",
    _updateDetails: function(tooltipNode, detailNode){
        var info = this.serializeInfo(detailNode);
        $('tooltip-title').innerHTML = info.title;
        $('tooltip-time').innerHTML = cu.substitute(this.TIME_TEMPLATE,info);
        $('tooltip-description').innerHTML = info.description;
        //$('tooltip-url').href = unescape(info.link);
        this._activeLink = info.link;
    },
    serializeInfo: function(elem){
        var obj = {};
        var nodes = elem.getElementsBySelector("span");
        nodes.each(function(item){
            obj[item.className] = item.innerHTML;
        })
        return obj;
    },
    trigger: function(e){
        //check if this event was caused by the drag'n'drop. If so ignore.
        if(cu.calendar.dragged){
            cu.calendar.dragged = false;
            return false;
        }
        var elem = Event.element(e);
        var li = elem.up('li');
        var id = li.getAttribute('id');
        if(this._isOpen && (this._currentTarget == id)){
            this.hide();
        } else {                
            this.hide();
            this._currentTarget = id;
            var xPos = li.cumulativeOffset()[0] + 12;
            var yPos = li.cumulativeOffset()[1] + 12;
            var tooltip = this._getTooltipNode();
            var details = this._getDetailNode(id);
            this._updateDetails(tooltip, details);
            tooltip.setStyle({left:xPos+"px",top:yPos+"px"});
            this.show();
        }
        return false;
    },
    noShow: function(){
        this._preventShow = true;
    },
    show: function(){
        this._getTooltipNode().show();
        this._isOpen = true;
    },
    hide: function(){
        this._getTooltipNode().hide();
        this._isOpen = false;
    },
    edit: function(){
        var url = this._activeLink.replace(/\&amp;/g, '&');
        document.location.href = url;
        return false;
    }
}


