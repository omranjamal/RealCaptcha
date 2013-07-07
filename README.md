RealCaptcha - BETA 1
=====================

A PHP Library that uses the [PHP-GD](http://php.net/manual/en/book.image.php) Extension to generate Captchas with only PHP.  
RealCaptcha is released under the [MIT License](http://projects.dichrome.tk/realCaptcha/license.php). Visit the project [Homepage](http://projects.dichrome.tk/realCaptcha/) for more information.

**Note:** This code is still in its Beta stage so it maybe prone to many bugs...  
**Note:** The Fonts included here are not created or owned by the author of the code,
but none the less, their liscenses are open-source, commercial-friendly and permit redistribution

![Example Image](http://projects.dichrome.tk/realCaptcha/images/example.jpg)




##Features

+ Custom Fonts
+ Customizable Colors
+ Supports 4 different captcha text sources

    + __Input__<br>

    + __Custom Generator Functions__<br> You can define a custom function of your own that generates the text that should be in the captcha.<br>

    + __Random Text Generator__<br> Generates a random string of Letters and Numbers.<br>

    + __Dictionary File__<br> Can use any file containing space separated words as a dictionary file to choose words from.

+ Captcha Image Compression
+ 3 different output formats ( **jpg**, **png**, **gif** )
+ Output locations
    + To Browser
    + To File
    + Return GD Resource Handle



Basic Usage
---------------------------
###Basic
	include("captcha.class.php");
	$captcha = new realCaptcha();
	$captcha->generate()->output("jpg");


###Configuring
All three of these code output similar Captchas to the browser in JPG Format.  

While Initializing

	$realCaptcha = new realCaptcha(array(
		"height" = 200,
		"width" = 500,
		"number_of_words" = 2
	));

	$captcha = $realCaptcha->generate();
	$captcha->output("jpg");

The Settings you provide at Initialzation will be used everytime the `generate()` method is called.  

Using the ***`set()`*** method

	$realCaptcha = new realCaptcha();

	$realCaptcha->set(array(
		"height" = 200,
		"width" = 500,
		"number_of_words" = 2
	));

	$captcha = $realCaptcha->generate();
	$captcha->output("jpg");

The settings you define with the `set()` method will overwrite the settings you provided at initialization and will be used
every time the `generate()` method is called.  

While Generating


	$realCaptcha = new realCaptcha();

	$captcha = $realCaptcha->generate(array(
		"height" = 200,
		"width" = 500,
		"number_of_words" = 2
	));
	$captcha->output("jpg");

The `generate()` method will give high priority to the settings passed to it as an argument. These setting will be forgotten
as soon as the captcha is finished generating.

###Dealing with output
	$realCaptcha = new realCaptcha();
	$captcha = $realCaptcha->generate();

The `generate()` method returns an Output object it has two methods and a buch of Properties.

####The Methods
+ `file( path, format, quality)`: Writes Captcha to a file
+ `output( format, quality)`: Sets appropriate image format headers and sends the Image data to browser

####The Properties
+ `text`: Contains a string words that are present in the captcha image, seperated by spaces
+ `array`: Contains an Array of the words present in the captcha image.

####Example
This example generates a captcha and stores the captcha text  in a session variable so that it can be 
matched later and output the captcha image to browser and also daves the image to a file named `example.jpg`

	session_start();
	$realCaptcha = new realCaptcha();

	$captcha = $realCaptcha->generate();
	$_SESSION["captcha_text"] = $captcha->text;
	$captcha->output("jpg",100);
	$captcha->file("example.jpg","jpg",100);


###Saving Captcha to file
To save captcha to file, you can use the **`file( file_path, format, quality)`** method.

	$captcha = $realCaptcha->generate();
	$captcha->file("file.jpg" ,"jpg", 90);

Full List of settings
--------------------------------
+ **height** (a positive Integer) : Height of the generated Captcha Image.
+ **width** (a positive integer) : Width of the generated Captcha Image.
+ **source** (realCaptcha source type constant): Where to get the captcha text from.
	+ **`realCaptcha::INPUT`**: Requires you to pass the captcha text while calling `generate()` method
	+ **`realCaptcha::RANDOM`**: Generates a random string of letters and numbers.
	+ **`realCaptcha::DICTIONARY`**: Selects words at random from the dictionary file.
	+ **`realCaptcha::uFUNCTION`**: Requires you to set a custom made function that returns the captcha text.
+ **dictionary_file** (a valid file path): Path to the dictionary File.
+ **fonts_dir** (a valid directory path): Path to the dictory containing all the fonts.
+ **number_of_words** (a positive integer): Number of words the captcha image should contain.
+ **random_length** (a positive integer): the length of the randomly generated string.
+ **background_color** (realCaptcha variable_grey setting constant or array)
	+ **`realCaptcha::GREY_VARIABLE`** : Randomly chooses a shade from white to light ash.
	+ **`array( int, int, int)`** : A numerical array containing RGB values.
+ **text_color** (array): A numerical array containing RGB values.


Advanced Usage
---------------------------
###Setting Background and Text color
	$captcha = new realCaptcha(array(
		"background_color" => array(255,0,0), //Bright Red
		"text_color" => array(255,255,255) //White
	));

	$captcha->generate()->output("jpg");

###Using direct Input
Both these codes are correct but work in different ways and the settings persist 
for different fractions of the run cycle.

Setting at Initialization

	$captcha = new realCaptcha(array(
		"source" => realCaptcha::INPUT
	));

	$captcha->generate("Text")->output("jpg");


Setting at Generator

	$captcha = new realCaptcha();
	$captcha->generate("Text", array("source" => realCaptcha::INPUT))->output("jpg");

It is Completely ok to pass String or an Array as input into the generator method,
thus both the following teo lines of code are valid

	$captcha->generate("Text")->output("jpg");
	$captcha->generate(array("example","text"))->output("jpg");

###Using Custom Text Generator Function
Unlike __Direct Input__ , Custom Functions cannot be set and declared at the __generator__ method,
custom function usage has to be declared in the initialization settings or through the `set()` method
and the function has to be define through the `textFunction()` method as the first argument. All prior to 
calling the `generate()` method in which you intend to use the Custom function.

####Example
	$captcha = new realCaptcha(array(
		"source" => realCaptcha::uFUNCTION
	));

	$captcha->textFunction(function(){
		return array("returned","text");
	});

	$captcha->generate()->output("jpg");


Liscense
---------
RealCaptcha is released under the [MIT License](http://projects.dichrome.tk/realCaptcha/license.php). Visit the project [Homepage](http://projects.dichrome.tk/realCaptcha/) for more information.
