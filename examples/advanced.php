<?php
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
$c1 = $captcha->generate();

$c1->output("png",40); //Output Captcha to Browser, as JPG Image with 40% Quality
$c1->file("output/c1.png","png",50); //Save Captcha to PNG File
$c1->file("output/c1.jpg","jpg",50); //Save Captcha to JPG File
$c1->file("output/c1.gif","gif"); //Save Captcha to GIF File

$_SESSION["captcha_text"] = $c1->text; //Store Captcha Text in Session
$_SESSION["captcha_array"] = $c1->array; //Store Captcha Words Array in Session







//-----   Change Settings
$captcha->set(array(
	"height" => 200,
	"width" => 400,
	"source" => realCaptcha::RANDOM,
));

$c2 = $captcha->generate();
$c2->file("output/c2.png","png",50);






//----   Define Temporary settings while calling the Generator Method
$c3 = $captcha->generate(array(
	"background_color" => array(255,0,0),
	"text_color" => array(255,255,255)
));

$c3->file("output/c3.jpg","jpg",50);





//----  Take Direct Input
$captcha->set(array(
	"source" => realCaptcha::INPUT,
));

//Pass a String
$c4 = $captcha->generate("Example");
$c4->file("output/c4.jpg","jpg",50);

//Pass An Array
$c5 = $captcha->generate(array("Example","Text"));
$c5->file("output/c5.jpg","jpg",50);





//---- Settings and Input both Together
$c6 = $captcha->generate("Example", array(
	"source" => realCaptcha::INPUT,
));

$c6->file("output/c6.jpg","jpg",50);





//----   Using User-Defined Functions as source
$captcha->set(array(
	"source" => realCaptcha::uFUNCTION,
));

//Set The Text Function
$captcha->textFunction(function(){
	return array(
		"Example",
		"Function"
	);
});

$c7 = $captcha->generate();
$c7->file("output/c7.jpg","jpg",50);

?>