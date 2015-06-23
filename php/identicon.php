<?php
    /*
    identicon.js v1.0
    maogm12@gmail.com
    */
class Identicon {
    private $text;
    private $hash;
    private $color;

    public function __construct($_text)
    {
        $this->text = $_text;
        
        // murmurhash3
        $hashVal = $this->murmurhash3($this->text);
        if ($hashVal < 0) {
            $hashVal += 0xffffffff;
        }
        $this->hash = str_pad(decbin($hashVal), 32, '0', STR_PAD_LEFT);

        //Get color
        $h = bindec(substr($this->hash, 0, 10))/1023;
        $s = bindec(substr($this->hash, 11, 10))/1023;
        $l = bindec(substr($this->hash, 22, 10))/1023;
        $this->color = $this->gen_color($h, $s, $l);
    }

    
    public function image($width = 8, $height = 8) {
        //create a white empty image using gd lib
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);
        
        //get the hash-specified color
        $im_color = imagecolorallocate($im, $this->color['r'], 
                                            $this->color['g'], 
                                            $this->color['b']);
        
        //get image-related lengthes
        $px_len = floor(min($width, $height)/8);
        $icon_edge = $px_len*8;
        $top = floor(($height - $icon_edge)/2);
        $left = floor(($width - $icon_edge)/2);

        // 01234567 for foreground
        for ($i = 0; $i < strlen($this->hash); $i++) { //should be 32 bits
            if ($this->hash[$i] === "1") {
                $xl = $left + $i%4*$px_len;
                $xr = $left + (7-$i%4)*$px_len;
                $y = $top + floor($i/4)*$px_len;
                imagefilledrectangle($im, $xl, $y, $xl+$px_len-1, $y+$px_len-1, $im_color);
                imagefilledrectangle($im, $xr, $y, $xr+$px_len-1, $y+$px_len-1, $im_color);
            }
        }
        
        ob_start();
        ImagePNG($im);      //display the image
        ImageDestroy($im);  //destroy the image
        return ob_get_clean();
    }
    
    //Helper function for HSLtoRGB
    private function HuetoRGB($v1, $v2, $vH) {
        if ( $vH < 0 ) {
            $vH += 1;
        }
        if ( $vH > 1 ) {
            $vH -= 1;
        }
        if ( ( 6 * $vH ) < 1 ) {
            return ( $v1 + ( $v2 - $v1 ) * 6 * $vH );
        }
        if ( ( 2 * $vH ) < 1 ) {
            return ( $v2 );
        }
        if ( ( 3 * $vH ) < 2 ) {
            return ( $v1 + ( $v2 - $v1 ) * ( ( 2 / 3 ) - $vH ) * 6 );
        }
        return ( $v1 );
    }
    
    //convert hsl color to rgb
    private function HSLtoRGB($H, $S, $L) {
        if ( $S == 0 ) {
            $R = $L * 255;
            $G = $L * 255;
            $B = $L * 255;
        } else {
            if ( $L < 0.5 ) {
                $var_2 = $L * ( 1 + $S );
            } else {
                $var_2 = ( $L + $S ) - ( $S * $L );
            }

            $var_1 = 2 * $L - $var_2;

            $R = 255 * $this->HuetoRGB( $var_1, $var_2, $H + ( 1 / 3 ) );
            $G = 255 * $this->HuetoRGB( $var_1, $var_2, $H );
            $B = 255 * $this->HuetoRGB( $var_1, $var_2, $H - ( 1 / 3 ) );
        }

        return array('r' => $R, 
                     'g' => $G,
                     'b' => $B);
    }

    //gen rgb color by h,s,l (both 0-1 float)
    private function gen_color($h = null, $s = null, $l = null) {
        //Generate a random nice color.'''
        if (is_null($h)) {
            //Void solid red, green, blue
            $h = rand(20, 310)/1000 + rand(0,2)/3;
        } else {
            $h = ($h-floor($h*3)/3)*0.29*3 + 0.02 + floor($h*3)/3;
        }

        if (is_null($s)) {
            //Void too dark or too bright
            $s = rand(300, 800)/100;
        } else {
            $s = $s*0.5+0.3;
        }
        
        if (is_null($l)) {
            //Void too dark or too light
            $l = rand(300, 800)/100;
        } else {
            $l = $l*0.5+0.3;
        }

        return $this->HSLtoRGB($h, $s, $l);
    }
    
    // murmurhash for php from https://github.com/lastguest/murmurhash-php
    private function murmurhash3($key, $seed = 0) {
    	$klen = strlen($key);
    	$h1   = $seed;
    	for ($i = 0, $bytes = $klen - ($remainder = $klen&3); $i < $bytes;) {
    		$k1 = ((ord($key[$i]) & 0xff))
    			| ((ord($key[++$i]) & 0xff) << 8)
    			| ((ord($key[++$i]) & 0xff) << 16)
    			| ((ord($key[++$i]) & 0xff) << 24);
    		++$i;
    		$k1  = (((($k1 & 0xffff) * 0xcc9e2d51) + (((($k1 >> 16) * 0xcc9e2d51) & 0xffff) << 16))) & 0xffffffff;
    		$k1  = $k1 << 15 | $k1 >> 17;
    		$k1  = (((($k1 & 0xffff) * 0x1b873593) + (((($k1 >> 16) * 0x1b873593) & 0xffff) << 16))) & 0xffffffff;
    		$h1 ^= $k1;
    		$h1  = $h1 << 13 | $h1 >> 19;
    		$h1b = (((($h1 & 0xffff) * 5) + (((($h1 >> 16) * 5) & 0xffff) << 16))) & 0xffffffff;
    		$h1  = ((($h1b & 0xffff) + 0x6b64) + (((($h1b >> 16) + 0xe654) & 0xffff) << 16));
    	}
    	$k1 = 0;
    	switch ($remainder) {
    		case 3: $k1 ^= (ord($key[$i + 2]) & 0xff) << 16;
    		case 2: $k1 ^= (ord($key[$i + 1]) & 0xff) << 8;
    		case 1: $k1 ^= (ord($key[$i]) & 0xff);
    		$k1  = ((($k1 & 0xffff) * 0xcc9e2d51) + (((($k1 >> 16) * 0xcc9e2d51) & 0xffff) << 16)) & 0xffffffff;
    		$k1  = $k1 << 15 | $k1 >> 17;
    		$k1  = ((($k1 & 0xffff) * 0x1b873593) + (((($k1 >> 16) * 0x1b873593) & 0xffff) << 16)) & 0xffffffff;
    		$h1 ^= $k1;
    	}
    	$h1 ^= $klen;
    	$h1 ^= $h1 >> 16;
    	$h1  = ((($h1 & 0xffff) * 0x85ebca6b) + (((($h1 >> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
    	$h1 ^= $h1 >> 13;
    	$h1  = (((($h1 & 0xffff) * 0xc2b2ae35) + (((($h1 >> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
    	$h1 ^= $h1 >> 16;
    	return base_convert($h1,10,32);
    }
}
//Usage: $icon=new Identicon('identicon'); $icon->image(128, 128);
?>