
if(typeof(console)=="undefined"){
    window.console = {};
    console.log = function(){};
    console.warn = function(){};
    console.error = function(){};
}

var cu = {
        /*used for debug purposes
         *hook a logger on to a function call so that
         *you can see the passed arguments and return if any.
         **/
        watch:function(){
            var obj,method;
            if(arguments.length==2){
                obj = arguments[0];
                method = arguments[1];
            } else if(arguments.length ==1){
                obj = window;
                method = arguments[0];
            } else {
                console.error("Bad number of arguments");
                return false;
            }
            if((obj!=null)&&Object.isString(method)){
                var _ref = obj[method];
                if(!_ref){
                    console.error("Ref not found",arguments);
                    return false;
                }
                obj[method]=function(){
                    console.log(method,"IN",arguments);
                    var _ret = _ref.apply(obj, arguments);
                    console.log(method,"OUT",_ret);
                    return _ret;
                }
                return true;
            } else {
                console.log("Bad arguments",arguments);
                return false;
            }
        },
    browser: {
        isIE: function(){
            //crazy short hack for checking if we're in IE
            return Prototype.Browser.IE;
        }
    },
    date: {
        century: "20",
        F1: "dd mmm yy",
        F2: "dd-mm-yy",
        toDate: function(dateStr, format){
            if(format == this.F1){
                var parts = dateStr.split(" ");
                var day = parts[0];
                var year = parts[2];
                var fullYear = this.century + year;
                return new Date(day+" "+parts[1]+" "+fullYear);
            } else {
                console.error("Bad format for date");
            }
        },
        asFormatted: function(date, format){
            if(format == this.F2){
                var day = cu.string.pad(date.getDate(),2,"0",false);
                var month = cu.string.pad((date.getMonth()+1),2,"0",false);
                var year = date.getFullYear();
                return day+"-"+month+"-"+year;
            } else {
                console.error("Bad format for date");
            }
        }
    },
    flash: {
        currentVersion: -1,
        greaterThan: function(VersionString){
            var reqMajorVer, reqMinorVer, reqRevision;
            var parts = VersionString.split(".");
            switch(parts.length.toString()){
                case '1':
                    reqMajorVer = parts[0];
                    reqMinorVer = 0;
                    reqRevision = 0;
                    break;
                case '2':
                    reqMajorVer = parts[0];
                    reqMinorVer = parts[1];
                    reqRevision = 0;
                    break;
                case '3':
                    reqMajorVer = parts[0];
                    reqMinorVer = parts[1];
                    reqRevision = parts[2];
                    break;
                default:
                    reqMajorVer = parts[0];
                    reqMinorVer = 0;
                    reqRevision = 0;
            }

            //taken from adobe detection kit
            var versionStr = cu.flash.currentVersion;
            if (versionStr == -1 ) {
                return false;
            } else if (versionStr != 0) {
                if(adobe.isIE && adobe.isWin && !adobe.isOpera) {
                    // Given "WIN 2,0,0,11"
                    tempArray         = versionStr.split(" ");     // ["WIN", "2,0,0,11"]
                    tempString        = tempArray[1];            // "2,0,0,11"
                    versionArray      = tempString.split(",");    // ['2', '0', '0', '11']
                } else {
                    versionArray      = versionStr.split(".");
                }
                var versionMajor      = versionArray[0];
                var versionMinor      = versionArray[1];
                var versionRevision   = versionArray[2];
                // is the major.revision >= requested major.revision AND the minor version >= requested minor
                if (versionMajor > parseFloat(reqMajorVer)) {
                    return true;
                } else if (versionMajor == parseFloat(reqMajorVer)) {
                    if (versionMinor > parseFloat(reqMinorVer))
                        return true;
                    else if (versionMinor == parseFloat(reqMinorVer)) {
                        if (versionRevision >= parseFloat(reqRevision))
                            return true;
                    }
                }
                return false;
            }        
        return null;
        }
    },
    cookie: {
        getCookieObj: function(){return this.getObj()},
        getObj: function(){
            var cookieStr = document.cookie;
            var values = cookieStr.split(";");
            var cookieObj={};
            values.each(function(item){
                var p = item.split("=");
                cookieObj[cu.string.trim(p[0])]=p[1];
            })
            return cookieObj;
        },
        getValue: function(id){
            return this.getCookieObj()[id];
        },
        create:function(name,value,days) {
            if (days) {
                var date = new Date();
                date.setTime(date.getTime()+(days*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            } else {
                var expires = "";
            }
            document.cookie = name+"="+value+expires+"; path=/";
        }

    },
    string: {
        trim:function(str) {
            return str.replace(/^\s+|\s+$/g,"");
        },
        rep: function(str, num){
            if(num <= 0 || !str){ return ""; }
            var buf = [];
            for(;;){
                if(num & 1){ buf.push(str); }
                if(!(num >>= 1)){ break; }
                str += str;
            }
            return buf.join("");
        },
        pad: function(text, size, ch, end){
            ch=(!ch)?0:ch;
            var out = String(text),
                pad = cu.string.rep(ch, Math.ceil((size - out.length) / ch.length));
            return end ? out + pad : pad + out;
        }
    },
    currency: {
        _character: "$",
        _decimalPlaces: 2,
        format: function(value){
            var number = cu.numberFormat(value, this._decimalPlaces);
            return this._character + String(number);
        }
    },
    /*Mirror the std php function for number format*/
    numberFormat: function(value, decimalPlace){
        //if null default to 2?
        decimalPlace = (decimalPlace)?decimalPlace:2;
        var neg = "";
        value = parseFloat(value);
        if(isNaN(value)){
            var returnVal = 0;
            return returnVal.toFixed(decimalPlace)
        }
        if(value<0){
            value = value * -1;
            neg = "-";
        }
        var parts = (value.toFixed(decimalPlace)).split(".");
        var formatted = (this.splitOnInterval(parts[0], 3)).join(',');
        return ( neg + formatted + "." + parts[1]);
    },
    /*split a string at a regular interval (from the right)*/
    splitOnInterval: function(str, interval){        
        var l = str.length;
        var sa = str.split("");
        var ex = l%interval;
        t = [];
        if(ex>0){ t.push(sa.splice(0,ex).join("")); }
        while(sa.length>0){ t.push(sa.splice(0,3).join(""));}
        return t;
    },

    _windowLoaded: false,    
    /*adds an event to be triggered once the page is loaded*/
    addOnLoad: function(/*function*/ func,/*?object?*/ scope){
        scope = (scope)?scope:window;
        /*note if the window is already loaded just call the function*/
        if(this._windowLoaded){
            func.apply(scope,[]);
        } else {
            document.observe("dom:loaded", func.bind(scope));
        }
    },
    /*takes a JSON structure and converts it to dom nodes*/
    /*
     *requires type, properties,
     *optional content
     *
     *type = string representing tag type
     *properties = object representing tag attribues (note class needs quotes)
     *content = array can be objects for further processing or string.
     *
     *eg.
     *var structure = {type:"div",properties:{id:"test"}, content:["div content"]};
     *var structure = {
     *    type:"div",
     *    properties:{id:"t1", "class":"blah"},
     *    content:["Something:",{type:"span",properties:{},content:["test"]}]};
     **/
    renderDom: function(/*object*/ structure){
        var nodeType = structure.type;
        var properties = structure.properties;
        var node = new Element(nodeType, properties);
        if(typeof(structure.content)!="undefined"){
            if(Object.isString(structure.content)){
                node.innerHTML=structure.content
            } else if(Object.isArray(structure.content)){
                structure.content.each(function(item){
                    if(Object.isString(item)){
                        node.appendChild(document.createTextNode(item));
                    } else {
                        node.appendChild(cu.renderDom(item));
                    }
                })
            }
        }
        return node;
    },
    _globalTicker:0,
    /* Generate a universal GUID based on time and a global counter.*/
    getUID: function(){
        return "cu_"+(new Date()).getTime()+"_"+this._globalTicker++
    },
    /*take a template, replace placeholders with values in an object*/
    substitute: function(/*String*/ template, /*Object*/ valueMap){
       return template.replace(/\$\{([^\s\:\}]+)(?:\:([^\s\:\}]+))?\}/g, function(match, key) {
             return (valueMap[key])?valueMap[key]:"";
         });
    },
    /*take a template, replace placeholders with values in an object*/
    inpageSubstitute: function(/*String*/ template, /*Object*/ valueMap){
      return template.replace(/\$\[([^\s\:\]]+)(?:\:([^\s\:\]]+))?\]/g, function(match, key) {
        return (valueMap[key]) ? valueMap[key] : "";
      });
    },
    /* internal function that returns the global object which is almost always "window" */
    _getGlobal: function(){ return window; },
    /*
     * Another way of expressing the forEach prototype method. Note the forcing of scope.
     * usage:
     *        var obj = {
     *                    log: function(item){console.log(item);},
     *                    a: function(item){this.log(item);}
     *                    }
     *        cu.forEach([1,2,3], obj.a, obj);
     *    Note: the use of "this" means you need to force the scope for the method to work.
     */
    forEach: function(/*Array*/array, /*function*/func, /*object*/scope){
        if(!scope){ scope = this._getGlobal();}
        array.each(func.bind(scope));
    },
    /*
     * Another way of expressing the prototype filtering method "findAll"
     * usage:  var filtered = cu.filter([1,2,3], function(item){return item>=2});
     */
    filter: function(/*Array*/arr, /*function*/func){ return arr.findAll(func);},
    /*
     * Mix the supplied objects parameters and methods into the src object.
     * usage:
     *    var a = {x:1, y:2};
     *    cu.mixin(a,{
     *        something: function(){},
     *        else: "else"
     *    })
     * a will have it's initial x,y as well as a.something and a.else
     **/
    mixin: function(/*object*/src, /*object*/params){
        for(var item in params){
            src[item] = params[item];
        }
    },
    /*
     * Shorthand for prototypes ajax request.
     * This method:
     * - makes a 'get' call to the url specified
     * - calls the func or errFunc depending on the success or failure.
     */
    ajaxGet: function(/*string*/ url, /*function*/ func, /*function*/ errFunc){
        if(!errFunc){ errFunc = function(){console.error("---Request Failed---",url)}}
        return new Ajax.Request(url, {
            method: 'get',
            onSuccess: func,
            onError: errFunc
        });
    },
    /*
     *This method:
     * - makes an ajax call to the url specified,
     * - inserts the reponse code into the id speicified
     * - call initilize modal links
     * - call the followup callback if it exists.
     **/
    ajaxUpdate: function(/*string*/ url, /*string*/ id, /*function(callback)*/ followup){
            if(url.indexOf('?')>0){
            url += "&bust="+cu.getUID();
        } else {
            url += "?bust="+cu.getUID();
        }
        this.ajaxGet(url, function(transport) {
            Element.update(id, transport.responseText);
            modalDialog.initializeLinks();
            if(followup){followup.apply(window,[]);}
        });
    },
    /*
     * This method:
     * - makes an ajax post to the url speicifed using the parameters specified.
     * the func and error func are callbacks that will be enacted on success or on failur.
     */
    ajaxPost: function(/*string*/ url, /*object*/ params, /*function*/ func, /*function*/ errFunc){
        if(!errFunc){ errFunc = function(){console.error("---Post Request Failed---",url)}}
        return new Ajax.Request(url, {
            method:'post',
            parameters: params,
            onSuccess: func,
            onError: errFunc
        });
    },
    url: {
        /*
         *Takes a base path such as "index.php" and convers a prameters object into url parameters
         *for a get request.
         */
        build: function(/*String*/basePath, /*Object*/params){
            var arr = [];
            for(var item in params){
                arr.push(item+"="+params[item]);
            }
            return basePath+"?"+arr.join("&");
        },
                digestSearch: function(){
                    var search = document.location.search;
                    search = search.substr(1,search.length);
                    var items = search.split("&");
                    var obj = {};
                    cu.forEach(items,function(item){
                        var parts = item.split("=");
                        if(parts.length==2){
                            obj[parts[0]]=parts[1];
                        }else if(parts.length==1){
                            obj[parts[0]]=true;
                        }
                    });
                    return obj;
                }
    },
        Deferred: function(){
            this._callbacks = [];
            this._fired = false;
            this._success = null;
        }
}

cu.Deferred.prototype = {
    addCallback: function(func, scope){
        scope = (scope)?scope:this;
        //if we've already seen true then immediately call;
        if((this._fired)&&(this._success)){
            func.apply(scope,[]);
        }else {
            this._callbacks.push({f:func, s:scope});
        }
    },
    callback: function(func){
        if(this._fired){
            console.error("already fired");
            return;
        }
        this._fired = true;
        this._success = func;
        if(func){
            cu.forEach(this._callbacks, function(item){
                item.f.apply(item.s,[]);
            });
        } else {
            console.log("callback trigger returned false");
        }
    }
}

if(typeof(adobe)!="undefined"){
    cu.flash.currentVersion = adobe.GetSwfVer();
}
Event.observe(window,'load',function(){
    cu._windowLoaded=true;
});
