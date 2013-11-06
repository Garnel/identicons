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
        $md5 = md5($this->text);
        $this->hash = '';
        
        //Get 32-bits hash string
        for ($i = 0; $i < strlen($md5); $i++) { //md5 hash should be 32-bits
            if (strpos('01234567', $md5[$i]) !== false) { //should be !==
                $this->hash .= '1';
            } else {
                $this->hash .= '0';
            }
        }
        
        //Get color
        $h = hexdec(substr($md5, 13, 3))/4096;
        $s = hexdec(substr($md5, 21, 3))/4096;
        $l = hexdec(substr($md5, 29, 3))/4096;
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
}
//Usage: $icon=new Identicon('maogm12@gmail.com'); $icon->image(128, 128);
?>