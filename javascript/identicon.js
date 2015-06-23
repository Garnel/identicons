/*
identicon.js v1.0
maogm12@gmail.com
*/
function Identicon(options){
    /*default options*/
    this.renderTo = '';
    this.width = 8;
    this.height = 8;
    this.text = '';
    this.canvas = null;
    if (options) {
        if (options.hasOwnProperty('renderTo'))
            this.renderTo = options.renderTo;
        if (options.hasOwnProperty('width'))
            this.width = options.width;
        if (options.hasOwnProperty('height'))
            this.height = options.height;
        if (options.hasOwnProperty('text'))
            this.text = options.text;
    }
    this.genHash();
    this.render();
};

Identicon.prototype.genHash = function () {
    var hashStr = this.murmurHash(this.text, 22).toString(2);
    // pad 0
    this.hash = hashStr.length < 32 ? new Array(32 - hashStr.length + 1).join('0') + hashStr : hashStr;
}

/**
 * JS Implementation of MurmurHash3 (r136) (as of May 20, 2011)
 * 
 * @author <a href="mailto:gary.court@gmail.com">Gary Court</a>
 * @see http://github.com/garycourt/murmurhash-js
 * @author <a href="mailto:aappleby@gmail.com">Austin Appleby</a>
 * @see http://sites.google.com/site/murmurhash/
 * 
 * @param {string} key ASCII only
 * @param {number} seed Positive integer only
 * @return {number} 32-bit positive integer hash 
 */
Identicon.prototype.murmurHash = function (key, seed) {
	var remainder, bytes, h1, h1b, c1, c1b, c2, c2b, k1, i;
	
	remainder = key.length & 3; // key.length % 4
	bytes = key.length - remainder;
	h1 = seed;
	c1 = 0xcc9e2d51;
	c2 = 0x1b873593;
	i = 0;
	
	while (i < bytes) {
	  	k1 = 
	  	  ((key.charCodeAt(i) & 0xff)) |
	  	  ((key.charCodeAt(++i) & 0xff) << 8) |
	  	  ((key.charCodeAt(++i) & 0xff) << 16) |
	  	  ((key.charCodeAt(++i) & 0xff) << 24);
		++i;
		
		k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff;
		k1 = (k1 << 15) | (k1 >>> 17);
		k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff;

		h1 ^= k1;
        h1 = (h1 << 13) | (h1 >>> 19);
		h1b = ((((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16))) & 0xffffffff;
		h1 = (((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16));
	}
	
	k1 = 0;
	
	switch (remainder) {
		case 3: k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
		case 2: k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
		case 1: k1 ^= (key.charCodeAt(i) & 0xff);
		
		k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
		k1 = (k1 << 15) | (k1 >>> 17);
		k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
		h1 ^= k1;
	}
	
	h1 ^= key.length;

	h1 ^= h1 >>> 16;
	h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
	h1 ^= h1 >>> 13;
	h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
	h1 ^= h1 >>> 16;

	return h1 >>> 0;
}

Identicon.prototype.genColor = function(h, s, l){
    //Generate a random nice color.
    var h, s, l, choices = [0, 120, 240];
    if (arguments[0]) { //(0.02 - 0.31) + n*(1/3) n = 0, 1, 2
        h = arguments[0]*360;
        h = h%120*104.4/120+7.2 + Math.floor(h/120)*120;
    } else {
        //random
        //Void solid red, green, blue
        h = (Math.random()*104.4+7.2) + choices[Math.floor(Math.random()*3)];
    }
    
    if (arguments[1]) {
        s = arguments[1];
        s = (s*0.5+0.3)*100;
    } else {
        //random, Void too dark or too bright
        s = (Math.random()*0.5 + 0.3)*100;
    }
    
    if (arguments[2]) {
        l = arguments[2];
        l = (l*0.5+0.3)*100;
    } else {
        //random, void too dark or too light
        l = (Math.random()*0.5 + 0.3)*100;
    }

    return 'hsl(' + h + ',' + s + '%,' + l + '%)';
};

Identicon.prototype.render = function(repaint){
    if (typeof repaint === 'undefined')
        repaint = false;

    var container = document.getElementById(this.renderTo);
    if (container === null)  //no such a div
        return;

    //force repain or the canvas is not inited yet
    if (repaint === true || this.canvas === null) {
        this.canvas = document.createElement('canvas');
        container.innerHTML = ''; //clear content in container
        container.appendChild(this.canvas);
    }

    this.canvas.width = this.width;
    this.canvas.height = this.height;

    //render on canvas
    var icon_ctx = this.canvas.getContext('2d');
    icon_ctx.canvas.width = this.width;
    icon_ctx.canvas.height = this.height;
    icon_ctx.clearRect(0, 0, this.width, this.height);

    //get the color
    var h = parseInt(this.hash.slice(0, 10), 2)/1023,
        s = parseInt(this.hash.slice(11, 21), 2)/1023,
        l = parseInt(this.hash.slice(22, 32), 2)/1023;
    var color = this.genColor(h, s, l);
    icon_ctx.fillStyle=color;

    //render cubes
    var px_len = Math.floor(Math.min(this.width, this.height)/8),
        icon_edge = px_len*8;
    var top = Math.floor((this.height - icon_edge)/2),
        left = Math.floor((this.width - icon_edge)/2);

    for (var idx in this.hash) { //size = 32
        if (this.hash[idx] == '1') {
            var xl = left+idx%4*px_len,
                xr = left+(7-idx%4)*px_len,
                y = top+Math.floor(idx/4)*px_len;
            icon_ctx.fillRect(xl, y, px_len, px_len); 
            icon_ctx.fillRect(xr, y, px_len, px_len); 
        }
    }
};

Identicon.prototype.setText = function(text){
    if (typeof text === 'undefined')
        return;
    this.text = text;
    this.genHash();
    this.render(false);
};

Identicon.prototype.resize = function(width, height){
    if (typeof width === 'undefined' || typeof height === 'undefined')
        return;

    this.width = width;
    this.height = height;
    
    this.render(false);
};
