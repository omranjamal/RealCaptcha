<?php
session_start();

//Import the Class File
include("../captcha.class.php");

//Initialization & Setings
$captcha = new realCaptcha(array(
	"height" => 200,
	"width" => 500,
	"source" => realCaptcha::DICTIONARY,
	"number_of_words" => 2,
	"fonts_dir" => "../resources/fonts/",
	"dictionary_file" => "../resources/dictionary.php"
));

//Generating the Captcha...
$c = $captcha->generate();

//Output Captcha to Browser, as JPG Image with 40% Quality
$c->output("jpg",40);

//Save Text to Session
$_SESSION["captcha_text"] = $c->text;
?>