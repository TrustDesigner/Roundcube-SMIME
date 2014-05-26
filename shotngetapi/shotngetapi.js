// This class contain constant for listening result
function CListenerConst(){
	this.RESULT_NO_ERROR = "0";
	this.RESULT_RAND_ERROR = "1";
	this.RESULT_TIMEOUT = "2";
	this.RESULT_SERVER_ERROR = "3";
	this.RESULT_PLUGIN_ERROR = "4";
	this.RESULT_SERVER_ERROR_WITH_RETRY = "10";
	this.RESULT_CERTPHONE_ERROR = "11";
}

/*
* this class is used to check certphone response.
* Call startListening to start the response listening.
* IMPORTANT: you have to re-implement the onFinishedListening(rand, error) method
* to manage response. this method is calling at the end of Listening
*/
function CListener(pRand, randPath, page, time, freq) {
    /** ******************************************************************************************** */
    var xhr;
    
    this.rand = pRand;
    this.randPath = randPath;
    
    this.page = page;
    this.time = time;
    this.freq = freq;
        
    /** ******************************************************************************************** */
    if (window.XMLHttpRequest) {
	xhr =  new window.XMLHttpRequest();
    }
    else {
	try {  
	    xhr = new ActiveXObject('Msxml2.XMLHTTP');
        }
	catch (e) {
	    try {   
		xhr = new ActiveXObject('Microsoft.XMLHTTP');
            }
	    catch (e2) {
		try {  
		    xhr = new XMLHttpRequest();
		}
		catch (e3) { 
		    xhr = false;
		}
	    }
	}
    }
    
    /** ******************************************************************************************** */
    if ( typeof CListener.initialized == "undefined" ) {
	/** ******************************************************************** */
	CListener.prototype.startListening = function() {
	    if(this.page != '' && this.page != null){
		if(xhr) {
		    xhr.rand = this.rand;
		    xhr.randPath = this.randPath;
		    xhr.page = this.page;
		    xhr.time = this.time;
		    xhr.freq = this.freq;
		    
		    xhr.onreadystatechange = this.onReadyStateChange;
		    
		    this.sendTest(this);
		}
		else {
		    this.onFinishedListening(rand, 'xhr null');
		}
	    }
	    else {
		this.onFinishedListening(rand, 'null page value');
	    }
	}
	
	CListener.prototype.onReadyStateChange = function() {
	    if(xhr.readyState  == 4){
		if(xhr.status  == 200) {
		    var response = xhr.responseText;
		    
		    var listenerConst = new CListenerConst();
		    
		    switch(response){
		    case listenerConst.RESULT_SERVER_ERROR: //pas encore répondu
			if(time != null){
			    time -= freq;
			    
			    if(time > 0){
				CListener.prototype.sendTest(xhr);
			    }
			    else {
				onFinishedListening(xhr.rand, listenerConst.RESULT_TIMEOUT);
			    }
			}
			else {
			    CListener.prototype.sendTest(xhr);
			}
			break;
			
		    case listenerConst.RESULT_NO_ERROR: //répondu
			onFinishedListening(xhr.rand, listenerConst.RESULT_NO_ERROR);
			break;
			
		    case listenerConst.RESULT_TIMEOUT: //timeout
			onFinishedListening(xhr.rand, response);
			break;
			
		    default:
			onFinishedListening(xhr.rand, response);
			break;
		    }
		}
		else {
		    onFinishedListening(xhr.rand, 'xhr status: ' + xhr.status);
		}
	    }
	}
	
	CListener.prototype.onFinishedListening = function(rand, error) {
	    
	}
	
	/** ******************************************************************** */
	CListener.prototype.sendTest = function(o) {
	    xhr.open( "GET", page + "?rand=" + o.rand + "&randPath=" + randPath + "&freq=" + freq + "&stamp=" + new Date().getTime(),  true); 
	    xhr.send(null);
	}
	
	/** ******************************************************************** */
	CListener.prototype.setPage = function(page) {this.page = page;}
	CListener.prototype.getPage = function() {return this.page;}
	
	CListener.prototype.setTime = function(time) {this.time = time;}
	CListener.prototype.getTime = function() {return this.time;}
	
	CListener.prototype.setFreq = function(freq) {this.freq = freq;}
	CListener.prototype.getFreq = function() {return this.freq;}
	
	CListener.initialized = true;
    }
}

function QRListener(page, callback) {
    /** ******************************************************************************************** */
    var qrxhr;
    this.page = page;
    this.callback = callback;
    /** ******************************************************************************************** */
    if (window.XMLHttpRequest) {
	qrxhr =  new window.XMLHttpRequest();
    }
    else {
	try {  
	    qrxhr = new ActiveXObject('Msxml2.XMLHTTP');
        }
	catch (e) {
	    try {   
		qrxhr = new ActiveXObject('Microsoft.XMLHTTP');
            }
	    catch (e2) {
		try {  
		    qrxhr = new XMLHttpRequest();
		}
		catch (e3) { 
		    qrxhr = false;
		}
	    }
	}
    }
    
    /** ******************************************************************************************** */
    if ( typeof this.initialized == "undefined" ) {
	/** ******************************************************************** */
	QRListener.prototype.startListening = function() {
	    if(this.page != '' && this.page != null){
		if(qrxhr) {
		    qrxhr.page = this.page;
		    qrxhr.onreadystatechange = this.onReadyStateChange;
		    qrxhr.callback = this.callback;
		    QRListener.prototype.sendTest(this);
		}
		else {
		    this.onFinishedListening(null);
		}
	    }
	    else {
		this.onFinishedListening(null);
	    }
	}
	
	QRListener.prototype.onReadyStateChange = function() {
	    if(qrxhr.readyState  == 4){
		if(qrxhr.status  == 200) {
		    onQRFinishedListening(qrxhr.responseText, qrxhr.callback);
		}
		else {
		    onQRFinishedListening(null, qrxhr.callback);
		}
	    }
	}

	/** ******************************************************************** */
	QRListener.prototype.sendTest = function(o) {
	    qrxhr.open( "GET", page, true);
	    qrxhr.send({action:'get_code'});
	}
	this.initialized = true;
    }
}

function get_shotnget_code(page, callback, text) {
    var div = document.getElementById("shotnget_login_div");
    if (!(typeof(div) != 'undefined' && div != null)) {
	var shotnget_div = document.createElement("div");
	var backgound_img = document.createElement("img");
	
	shotnget_div.id = "shotnget_login_div";
	shotnget_div.style.margin = "0px";
	shotnget_div.style.position = "fixed";
	shotnget_div.style.top = "0px";
	shotnget_div.style.left = "0px";
	shotnget_div.style.display = "none";
	shotnget_div.style.height = "100%";
	shotnget_div.style.width = "100%";

	backgound_img.id = "background_img_shotnget_login";
	backgound_img.style.backgroundColor = "black";
	backgound_img.style.opacity = "0.65";
	backgound_img.style.position = "fixed";
	backgound_img.style.margin = "0px";
	backgound_img.style.top = "0px";
	backgound_img.style.left = "0px";
	backgound_img.style.display = "block";
	backgound_img.style.width = "100%";
	backgound_img.style.height = "100%";
	backgound_img.style.zIndex = "-1";

	if (text != 'undefined' && text != null) {
	    var shotnget_text = document.createElement("div");
	    shotnget_text.style.fontSize = "15px";
	    shotnget_text.style.color = "white";
	    shotnget_text.innerHTML = text;
	    shotnget_text.style.top = "50%";
	    shotnget_text.style.textAlign = "center";
	    shotnget_text.style.marginTop = "121.5px";
	    shotnget_text.style.position = "relative"
	    shotnget_div.appendChild(shotnget_text);
	}

	document.getElementsByTagName('body')[0].appendChild(shotnget_div);
	document.getElementById("shotnget_login_div").appendChild(backgound_img);

	document.getElementById("shotnget_login_div").addEventListener('click', function() {
	    document.getElementById("shotnget_login_div").style.display = 'none';
	});
    }
    var qrlistener = new QRListener(page, callback);
    qrlistener.startListening();
}

function onQRFinishedListening(html, callback) {
    // Add QRCode inside client page
    var div = document.getElementById("shotnget_login_div");
    div.style.display = "block";

    var code = document.getElementById("shotnget_code");
    var code_div = document.getElementById("shotnget_login_div");
    if (typeof(code) != 'undefined' && code != null) {
	code_div.removeChild(code);
    }
    code_div.innerHTML += html;
    code_div.style.zIndex = 99999;
    code = document.getElementById("shotnget_code");
    code.style.display = "block";
    var body = document.getElementsByTagName('body')[0];
    code.style.top = "50%";
    code.style.left = "50%";
    code.style.marginTop = "-96.5px";
    code.style.marginLeft = "-82px";
    code.style.position = "absolute";
    code.style.borderRadius = "5px";
    code.style.zIndex = 100000;
    if (callback != 'undefined' && callback != null)
	callback(html);
}