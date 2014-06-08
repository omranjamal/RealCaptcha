<?php
/*
RealCaptcha
Version: BETA 1
Author: Omran jamal ( www.about.me/omran.jamal )

License:
This Code is Released under the 'MIT' License
http://projects.dichrome.tk/realCaptcha/license.php

Note: Only the codes in this file are Licensed under MIT policy, the fonts included with the package
may be under a different Open Source, Commercial Friendly license. Never the less you are free to
use the fonts and all their licenses allow redistribution and commercial use.
*/

namespace RealCaptcha;

class realCaptchaOutput{
    public $array = array();
    public $text = "";
    public $image = NULL;

    public function file($path, $format, $quality=100){
        switch($format){
            case "jpg":
                imagejpeg($this->image, $path, $quality);
                break;

            case "png":
                imagepng($this->image, $path, round( ($quality/100)*9 ) );
                break;

            case "gif":
                imagegif($this->image, $path);
                break;

            default:
                throw new Exception("Image Format not supported", 5);

        }
    }

    public function output($format, $quality=100){
        switch($format){
            case "jpg":
                header('Content-Type: image/jpeg');
                imagejpeg($this->image, NULL, $quality);
                break;

            case "png":
                header('Content-Type: image/png');
                imagepng($this->image, NULL, round( ($quality/100)*9 ) );
                break;

            case "gif":
                header('Content-Type: image/gif');
                imagegif($this->image);
                break;

            default:
                throw new Exception("Image Format not supported", 5);

        }
    }

    public function returnGD(){
        return $this->image;
    }

    public function __construct($image,$text){

        $this->array = $text;
        $this->image = $image;
        imageinterlace($this->image, TRUE);

        foreach($text as $key => $word){
            $space = " ";
            if($key == 0){
                $space = "";
            }
            $this->text.= $space.$word;
        }

    }
}


class RealCaptcha{
    //Settings Constants
    const DICTIONARY = 1;
    const uFUNCTION = 2;
    const RANDOM = 3;
    const INPUT = 4;

    const GREY_VARIABLE = 1;

    //Default Settings
    public $settings = array(
        "width" => 500,
        "height" => 200,

        "source" => realCaptcha::RANDOM,
        "dictionary_file" => "dictionary.php",
        "fonts_dir" => "fonts/",

        "number_of_words" => 2,
        "random_length" => FALSE,

        "background_color" => realCaptcha::GREY_VARIABLE,
        "text_color" => array(0,0,0)
    );



    //User Defined Text Generator Function Container
    public $udiff_TextFunction = FALSE;


    // Fix For: User-Text-Function Call...
    // There seems to be a problem calling functions incorporated with object properties
    public function __call($method, $args) {
        if(isset($this->$method) && is_callable($this->$method)) {
            return call_user_func_array($this->$method, $args);
        }
    }


    //Configurator
    public function set(array $settings){
        if($settings){
            //Merge and Overlap Default and User Settings
            $this->settings = array_merge($this->settings, $settings);

        }
    }


    //Init Default and User Defined settings
    public function __construct($settings=FALSE){
        if($settings) $this->set($settings);
    }


    //Set User Defined Text Generator Function
    public function textFunction($function){
        if(is_callable($function)){
            $this->udiff_TextFunction = $function;
        }else{
             throw new Exception('Provided Parameter is not a Function', 2);
        }
    }


    //<Word Functions>
        public function dictionaryText(&$settings=FALSE){
            //init settings
            $settings = !$settings? $this->settings : $settings;

            if (!file_exists($settings["dictionary_file"]))
                throw new Exception("Error Finding dictionary file {$settings["dictionary_file"]}", 4);

            $size = filesize($settings["dictionary_file"]);

            //Minimum number of characters required
            $minimum = 50*$settings["number_of_words"];

            //starting character from a random point in the file keeping minimum characters into consideration....
            $start = rand(0, $size-$minimum);
            $file = fopen($settings["dictionary_file"],"r");

            //Set File pointer to random point...
            fseek($file, $start);

            //read minimum number of characters and strip all non english characters
            //$raw = preg_replace("/[^a-zA-Z\s]/", "", fread($file, $minimum));

            //read minimum number of characters and replace all non english characters
            $raw = realCaptcha::remove_accents(fread($file, $minimum));

            $array = str_split($raw);

            //Final Array container...
            $text_array = array();

            //current Letter...
            $pointer = 0;

            //current word being fetched...
            $index = 0;

            //Temporary text container
            $temp = "";

            //if the first " " (space) character was reached or not...
            $first_reach = FALSE;

            //infinite loop, until break...
            while(TRUE){

                //if current character doesn't exist, stop the loop
                if (empty($array[$pointer]))
                    break;

                //if current character is a letter and a space has already been reached, add the character to temporary...
                if( $first_reach && $array[$pointer] != " " ){
                    $temp.= $array[$pointer];
                }

                //if current character is a space and a space has already been reached before
                if( $first_reach && $array[$pointer] == " " ){
                    $temp_size = strlen($temp);

                    //check temporary size and is under 3 characters long, reject and turncate temporary...
                    if($temp_size<3){
                        $temp = "";
                    }else{ // if over 3, then add the word to delivery storage, turncate temp
                        $text_array[$index] = $temp;
                        $temp = "";

                        //If the max number of words are reached, stop the loop
                        if($index == $settings["number_of_words"]-1){
                            break;
                        }else{ // If not max, prepare for new word insertion...
                            $index++;
                        }
                    }
                }

                //check for the first space character encountered...
                if(!$first_reach && $array[$pointer] == " " ){
                    $first_reach = TRUE;
                }

                $pointer++;
            }

            return $text_array;
        }

        //Replace all non english characters with an equivalent
        public function remove_accents($str, $charset='utf-8')
        {
            $str = htmlentities($str, ENT_NOQUOTES, $charset);

            $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
            $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
            $str = preg_replace('#&[^;]+;#', '', $str);

            return $str;
        }

        private function randomText(&$settings=FALSE){

            //Check if user set custom random_length or use session default or Random length
            $length = $settings && $settings["random_length"] ? $settings["random_length"] : $this->settings["random_length"] ? $this->settings["random_length"] : rand(5,9);

            //container
            $text = "";

            for($i=0; $i<$length; $i++){
                //Character type mode...
                $mode = rand(1,3);

                switch($mode){
                    case 1: //Numeric
                        $text.= chr(rand(48,57));
                        break;
                    case 2: //Upper-case English Letters
                        $text.= chr(rand(65,90));
                        break;
                    case 3: //Lower-case English letters
                        $text.= chr(rand(97,122));
                        break;
                }

            }
            return array($text);
        }
    //</Word Functions>

    private function font_list(&$settings=FALSE){
        $settings = !$settings? $this->settings : $settings;

        if(!is_dir($settings["fonts_dir"])){ //Check validity of directory
            throw new Exception("Error Finding fonts Directory", 4);
        }else{
            $raw = scandir($settings["fonts_dir"]); // Directory Listing...
            return array_slice($raw, 2); // Retur list without the soecial entries...
        }
    }

    private function xy_theta($x, $offset, $curve_intensity, $sign, $assumtion){
        global $height, $width;
        $r = $curve_intensity;
        $p = $assumtion;

        $x = $x+$offset;

        $y = ($sign*$r)*sin( (pi()/$p)*$x );
        $theta = atan( ( $sign*$r )*( pi()/$p )*cos( (pi()/$p)*$x ) );

        return array("y"=>abs($y), "theta"=>rad2deg($theta));
    }


    private function generate_text($text, &$settings=FALSE){
        $settings = !$settings? $this->settings : $settings;

        if($settings["height"]>$settings["width"]){
            $height = $settings["width"];
        }else{
            $height = $settings["height"];
        }
        $fonts = $this->font_list();

        //Co-Ordinate containers
        $up_left = array("x"=>0, "y"=>0);
        $low_right = array("x"=>0, "y"=>0);

        //60% height
        $font_size = floor((60/100)*$height);

        //image canvas
        // 140% Defined Height
        $canvas_height = $height+(40/100)*$height;
        // 10% padding + 5px spacing + total size assumption
        //Assumed String Length
        $assumtion = (($font_size+5)*strlen($text));
        $padding = floor((10/100)*$assumtion);
        $canvas_width = $assumtion + $padding*2;

        $canvas = imagecreatetruecolor($canvas_width, $canvas_height);

        //Background Color
        $background = imagecolorallocate($canvas, $settings["background_color"][0] , $settings["background_color"][1] , $settings["background_color"][2]);

        //Set the Background Color
        imagefilledrectangle($canvas, 0, 0, $canvas_width, $canvas_height, $background);

        $raw = str_split($text);
        $i = 0;
        $cursor = $padding;

        $offset = rand(0, $assumtion);
        $font = $settings["fonts_dir"].$fonts[rand(0,count($fonts)-1)];
        $curve_intensity = rand(10, ceil((30/100)*$height));

        $plus = rand(0,1);
        if(!$plus){
            $sign = -1;
        }else{
            $sign = 1;
        }

        while($i<strlen($text)){
            //Get Letter Location and rotation
            extract($this->xy_theta($cursor, $offset, $curve_intensity, $sign, $assumtion));

            //from the top
                $y_coordinate = $canvas_height-(30/100)*$height+$y;
                $letter = @imagettftext($canvas, $font_size, $theta/* Rotation */, $cursor, $y_coordinate, imagecolorallocate($canvas, $settings["text_color"][0], $settings["text_color"][1], $settings["text_color"][2]), $font, $raw[$i] );
                if(!$letter){
                    return FALSE;
                }
                $cursor = $letter[2]; //Lower-right corner, X co-ordinate

                if($i == 0){ //If 1st Letter
                    $up_left["x"] = $letter[0];
                    $up_left["y"] = $letter[7];
                }elseif($i == (strlen($text)-1) ){ //If last letter
                    $low_right["x"] = $letter[2];
                }

                $low_right["y"] = max($low_right["y"], $letter[3]/* Lower-right corner, Y co-ordinate */);
                $up_left["y"] = min($up_left["y"], $letter[7]/* Upper-left corner, Y co-ordinate */);

            $i++;
        }

        return array("p1"=> $up_left, "p2"=> $low_right, "image"=> $canvas, "total"=> array("height"=>$canvas_height, "width"=>$canvas_width ));

    }


    private function calculate_sampler($res){
        //FInd width and Height
        $w = $res["p2"]["x"] - $res["p1"]["x"];
        $h = $res["p2"]["y"] - $res["p1"]["y"];

        //Horizontal Padding Spcae..
        $w10 = floor((10/100)*$w);
        //Vertical Padding Space
        $h20 = floor((20/100)*$h);

        //Sampling Start point X
        if(($res["p1"]["x"]-$w10) < 0){
            $src_x = 0;
        }else{
            $src_x = $res["p1"]["x"]-$w10;
        }

        //Sampling Start point Y
        if(($res["p1"]["y"]-$h20) < 0){
            $src_y = 0;
        }else{
            $src_y = $res["p1"]["y"]-$h20;
        }

        //Sampling Height
        if(($src_y+$h+$h20) > $res["total"]["height"]){
            $src_h = $res["total"]["height"]-$src_y;
        }else{
            $src_h = $h+$h20;
        }

        //Sampling Width
        if(($src_x+$w+$w10) > $res["total"]["width"]){
            $src_w = $res["total"]["width"]-$src_x;
        }else{
            $src_w = $w+$w10;
        }

        return array(
            "src_x" => $src_x,
            "src_y" => $src_y,
            "src_h" => $src_h,
            "src_w" => $src_w
        );
    }


    public function generate($p1=FALSE, $p2=FALSE){

        //locate Words and Settings
        $tmp_words = FALSE;
        if( isset($p1[0]) || is_string($p1) ){

            $tmp_words = is_string($p1)?array($p1):$p1;
            $settings = array_merge( $this->settings, $p2?$p2:array());

        }elseif( isset($p2[0]) || is_string($p2) ){

            $tmp_words = is_string($p2)?array($p2):$p2;
            $settings = array_merge( $this->settings, $p1?$p1:array());

        }elseif($p1 && !$p2){

            $settings = array_merge( $this->settings, $p1);

        }else{

            $settings = $this->settings;

        }

        //Get/Check words...
        switch($settings["source"]){
            case realCaptcha::INPUT:
                if(!$tmp_words){ //If user forgets to give input
                    throw new Exception("Input is missing.", 3);
                }else{
                    $words = $tmp_words;
                }
                break;

            case realCaptcha::uFUNCTION:
                if(!$this->udiff_TextFunction){ //if user forgets to define a function
                    throw new Exception("Text Function not defined, choose another ode or define a text function.", 1);
                }else{
                    $tmp = $this->udiff_TextFunction();
                }

                if(!is_array($tmp)){ //if it returns string
                    $words = array($tmp);
                }else{
                    $words = $tmp;
                }
                break;

            case realCaptcha::RANDOM:
                $words = $this->randomText($settings);
                break;

            case realCaptcha::DICTIONARY:
                $words = $this->dictionaryText($settings);
                break;
        }


        //GD Image Container
        $resources = array();

        //Sampling Co-ordinates COntainer
        $samplers = array();

        //Prepare Generator Color, choose a random shade of white to grey is GREY_VARIABLE
        if($settings["background_color"] == realCaptcha::GREY_VARIABLE){
            $grey = rand(180,255);
            $background_color = array($grey,$grey,$grey);
        }else{
            $background_color = $settings["background_color"];
        }

        $generator_settings = $settings;
        $generator_settings["background_color"] = $background_color;

        //Populate respective container variables with Images and Sample Co-Ordinates
        $i=0;
        while($i<count($words)){
            //FIX: GLyph Loading Problem, if problem occurs: RETRY...
            $tmp = $this->generate_text($words[$i], $generator_settings);
            if($tmp!=FALSE){
                $resources[] = $tmp;
                $samplers[] = $this->calculate_sampler($tmp);
                $i++;
            }
        }


        //Total Generated Dimensions
        $total_generated = array("height"=>0, "width"=>0);
        foreach($samplers as $sample){
            $total_generated["height"] = $total_generated["height"]+$sample["src_h"];
            $total_generated["width"] = $total_generated["width"]+$sample["src_w"];
        }

        { //Landscape mode
            //Final Sampleing and Positioning Data COntainer
            $finals = array();
            $i = 0;
            $dst_x = 0;

            foreach($samplers as $sample){
                $temp_width =
                    //Scale width to ratio
                    $finals[$i]["dst_w"] = floor(($sample["src_w"]/$total_generated["width"])*$settings["width"]);
                    $finals[$i]["dst_rw"] = $finals[$i]["dst_w"] - ((5/100)*$finals[$i]["dst_w"]);

                //scale height to width
                $finals[$i]["dst_h"] = floor(($finals[$i]["dst_w"]/$sample["src_w"])*$settings["height"]);
                $finals[$i]["dst_rh"] = $finals[$i]["dst_h"] - ((5/100)*$finals[$i]["dst_h"]);

                if($finals[$i]["dst_h"]>$settings["height"]){
                    $finals[$i]["dst_w"] = floor(($settings["height"]/$finals[$i]["dst_h"])*$finals[$i]["dst_w"]);
                    $finals[$i]["dst_rw"] = $finals[$i]["dst_w"] - ((5/100)*$finals[$i]["dst_w"]);

                    $finals[$i]["dst_h"] = $settings["height"];
                    $finals[$i]["dst_rh"] = $finals[$i]["dst_h"] - ((5/100)*$finals[$i]["dst_h"]);

                    if($i==0){
                        $finals[$i]["dst_x"] = 0 + round(($temp_width-$finals[$i]["dst_w"])/2);
                    }else{
                        $dst_x = $dst_x + $temp_width;
                        $finals[$i]["dst_x"] = $dst_x + round(($temp_width-$finals[$i]["dst_w"])/2);
                    }
                }else{
                    if($i==0){
                        $finals[$i]["dst_x"] = 0;
                    }else{
                        $dst_x = $finals[$i]["dst_x"] = $dst_x + $finals[$i-1]["dst_w"];
                    }
                }

                $finals[$i]["dst_y"] = round(($settings["height"] - $finals[$i]["dst_h"])/2);

                $i++;
            }
        }


        $canvas = imagecreatetruecolor($settings["width"], $settings["height"]);
        $background = imagecolorallocate($canvas, $background_color[0] , $background_color[1] , $background_color[2]);
        imagefilledrectangle($canvas, 0, 0, $settings["width"], $settings["height"], $background);

        $i=0;
        foreach($finals as $final){
            imagecopyresampled(
                $canvas,
                $resources[$i]["image"],
                $final["dst_x"],
                $final["dst_y"],
                $samplers[$i]["src_x"],
                $samplers[$i]["src_y"],
                $final["dst_rw"],
                $final["dst_rh"],
                $samplers[$i]["src_w"],
                $samplers[$i]["src_h"]
            );

            $i++;
        }

        return new realCaptchaOutput($canvas, $words);
    }
}
?>
