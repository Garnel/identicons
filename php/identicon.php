<?php
    /*
    identicon.js v1.0
    maogm12@gmail.com
    */
    
    /*helper functions*/
    function HuetoRGB( $v1, $v2, $vH )
    {
        if ( $vH < 0 )
        {
            $vH += 1;
        }
        if ( $vH > 1 )
        {
            $vH -= 1;
        }
        if ( ( 6 * $vH ) < 1 )
        {
            return ( $v1 + ( $v2 - $v1 ) * 6 * $vH );
        }
        if ( ( 2 * $vH ) < 1 )
        {
            return ( $v2 );
        }
        if ( ( 3 * $vH ) < 2 )
        {
            return ( $v1 + ( $v2 - $v1 ) * ( ( 2 / 3 ) - $vH ) * 6 );
        }
        return ( $v1 );
    }

    function HSLtoRGB ( $H, $S, $L )
    {
        if ( $S == 0 )
        {
            $R = $L * 255;
            $G = $L * 255;
            $B = $L * 255;
        }
        else
        {
            if ( $L < 0.5 )
            {
                $var_2 = $L * ( 1 + $S );
            }
            else
            {
                $var_2 = ( $L + $S ) - ( $S * $L );
            }

            $var_1 = 2 * $L - $var_2;

            $R = 255 * HuetoRGB( $var_1, $var_2, $H + ( 1 / 3 ) );
            $G = 255 * HuetoRGB( $var_1, $var_2, $H );
            $B = 255 * HuetoRGB( $var_1, $var_2, $H - ( 1 / 3 ) );
        }

        return array('r' => $R, 
                     'g' => $G,
                     'b' => $B);
    }

    function gen_color($h = null, $s = null, $l = null) {
        //Generate a random nice color.'''
        if (is_null($h)) {
            //Void solid red, green, blue
            $h = rand(20, 310)/1000 + rand(0,2)/3;
        } else {
            $h = ($h-floor($h*3)/3)*0.29 + 0.02 + floor($h*3)/3;
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

        return HSLtoRGB($h, $s, $l);
    }
    
    function gen_identicon($width = 8, $height = 8, $text = '') {
        $hash = md5($text);
        $h = hexdec(substr($hash, 13, 3))/4096;
        $s = hexdec(substr($hash, 21, 3))/4096;
        $l = hexdec(substr($hash, 29, 3))/4096;
        $rgb = gen_color($h, $s, $l);
        
        //create a white empty image
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);
        
        //get the hash-specified color
        $color = imagecolorallocate($im, $rgb['r'], $rgb['g'], $rgb['b']);
        
        //get image-related lengthes
        $px_len = floor(min($width, $height)/8);
        $icon_edge = $px_len*8;
        $top = floor(($height - $icon_edge)/2);
        $left = floor(($width - $icon_edge)/2);

        // 01234567 for foreground
        for ($i = 0; $i < strlen($hash); $i++) { //should be 32 times
            if (strpos('01234567', $hash[$i]) != false) {
                $xl = $left + $i%4*$px_len;
                $xr = $left + (7-$i%4)*$px_len;
                $y = $top + floor($i/4)*$px_len;
                imagefilledrectangle($im, $xl, $y, $xl+$px_len-1, $y+$px_len-1, $color);
                imagefilledrectangle($im, $xr, $y, $xr+$px_len-1, $y+$px_len-1, $color);
            }
        }
        
        return $im;
    }
    
    //get some vars
    $width  = isset($_REQUEST['width'])  ? $_REQUEST['width']  : 8;
    $height = isset($_REQUEST['height']) ? $_REQUEST['height'] : 8;
    $text   = isset($_REQUEST['text'])   ? $_REQUEST['text']   : '';

    //use gd lib
    $im = gen_identicon($width, $height, $text);
    
    Header("Content-type: image/png");
    ImagePNG($im);  //display the image
    ImageDestroy($im);  //destroy the image
