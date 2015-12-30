<?php

//Get all ttf font file in the 'font/apache/' folder
$Directory = new RecursiveDirectoryIterator('font/apache/');
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.ttf$/i', RecursiveRegexIterator::GET_MATCH);
foreach ($Regex as $key => $row) {
    // replace 0 with the field's index/key
    $fontnames[$key]['basename'] = basename($row[0], ".ttf");
    $fontnames[$key]['pathname'] = $row[0];
}
function compareByName($a, $b) {
  return strcmp($a["basename"], $b["basename"]);
}
usort($fontnames, 'compareByName');
$Regex = null;
var_dump($Regex);


$oldfontname = "";
$fontstyle = "";
$fontstyleArray = array();
foreach ($fontnames as $fontfile) {
	$newfontname = split('-', $fontfile['basename']);
	if($newfontname[0] != $oldfontname) {
		if($oldfontname != "" && $fontstyle != "") {
			$lengthfontstyle = split(',', $fontstyle);
			if(count($lengthfontstyle) > 1) {
				$fontstyleArray[$newfontname[0]] = $fontstyle;
			}
		}
		$oldfontname = $newfontname[0];
		if (isset($newfontname[1])) {
			$fontstyle = $newfontname[1];
		} else {
			$fontstyle = "";
		}
	} else {
		if($fontstyle != "") {
			$fontstyle = $fontstyle.", ".$newfontname[1];
		} else {
			$fontstyle = $newfontname[1];
		}
	}
}

//JPG image quality 0-100
$quality = 100;
$allsizearray = [	
	'320x50' => "105x50",
	'120x600' => "120x57",
	'468x60' => "120x60",
	'728x90' => "130x90",
	'970x90' => "130x90",
	'160x600' => "160x56",
	'640x360' => "178x360",
	'180x150' => "180x34",
	'970x250' => "180x250",
	'300x250' => "300x40",
	'300x600' => "300x54",
	'320x480' => "320x57",
	'320x568' => "320x57",
	'336x280' => "336x48",
	'360x640' => "360x57",		
	'480x320' => "480x59",	
];
function create_image($user){
		global $quality;
		global $allsizearray;
		$files="";
		$iteration = 0;
		foreach($allsizearray as $banner => $size) {
				
			if ($iteration != 0) {
				$files .= "##";
			}
			$size = split('x', $size);
			$iwidth = $size[0];
			$iheight = $size[1];
			$bannersize = split('x', $banner);			
			$files .= "covers/cpn_".$bannersize[0]."x".$bannersize[1]."_1&".md5($user['standardwording'].$iteration).".jpg";		
			$file = "covers/cpn_".$bannersize[0]."x".$bannersize[1]."_1&".md5($user['standardwording'].$iteration).".jpg";	
			// define the base image that we lay our text on		
			
			if($user['bgcolor2'] != "") {
				error_log("gradient creation");
				if($user['angle'] == "horizontal") {
					$im = gradient($iwidth, $iheight, array($user['bgcolor'], $user['bgcolor2'], $user['bgcolor'], $user['bgcolor2']));
				} else {
					$im = gradient($iwidth, $iheight, array($user['bgcolor'], $user['bgcolor'], $user['bgcolor2'], $user['bgcolor2']));
				}
			} else {
				$im = imagecreate( $iwidth, $iheight );
				$rgbbgcolor = hex2rgb($user['bgcolor']);
				$color['bgcolor'] = imagecolorallocate($im, $rgbbgcolor[0], $rgbbgcolor[1], $rgbbgcolor[2]);
			}			
			// setup the text colours
			$rgbcolor = hex2rgb($user['color']);			
			$color['color'] = imagecolorallocate($im, $rgbcolor[0], $rgbcolor[1], $rgbcolor[2]);
			
			// this defines the starting height for the text block
			$y = imagesy($im)-($iheight/2)+6;
			$wordingtext = "";
			$wordingtextvertical = "";
			if($user['uppercase']==true) {
				$wordingtext = strtoupper ($user['standardwording']);
				$wordingtextvertical = strtoupper ($user['verticalwording']);
			} elseif($user['lowercase']==true) {
				$wordingtext = strtolower ($user['standardwording']);
				$wordingtextvertical = strtolower ($user['verticalwording']);	
			}
			else {
				$wordingtext = $user['standardwording'];
				$wordingtextvertical = $user['verticalwording'];
			}
			if(($iwidth/3)>$iheight) {				
				$words = explode(" ",$wordingtext);
			} else {
				if($user['verticalwording']!=""){
					$words = explode(" ",$wordingtextvertical);
				} else {
					$words = explode(" ",$wordingtext);
				}
			}
			
			if($iwidth <= 200 && $iheight <= 200) {
				$fontsize = $user['font-size-small'];
				$lineheight = $user['line-height-small'];
			} elseif($iwidth > 360 || $iheight >= 200) {
				$fontsize = $user['font-size-big'];
				$lineheight = $user['line-height-big'];
			} else {
				$fontsize = $user['font-size'];
				$lineheight = $user['line-height'];
			}
			
			$fontRegular = str_replace("-Bold", "-Regular", $user['font']);
			$fontRegular = str_replace("-CondBold", "-CondLight", $fontRegular);
			$fontRegular = str_replace("-Black", "-Regular", $fontRegular);				
			$fontName = str_replace("-Regular", "", $fontRegular);
			$fontBold = str_replace("-Regular", "-Bold", $fontRegular);
			$fontItalic = str_replace("-Regular", "-Italic", $fontRegular);
			$fontBoldItalic = str_replace("-Regular", "-BoldItalic", $fontRegular);
			$wnum = count($words);
			$line = '';
			$text='';
			$boldActive = false;
			$italicActive = false;
			$boldItalicActive = false;
			$dimensions = 0;
			for($i=0; $i<$wnum; $i++){
			  if ($words[$i] == "<br>" || $words[$i] == "<BR>") {
				$text.=($text != '' ? '##'.$words[$i].' ' : $words[$i].' ');
			    $line = $words[$i].' ';
			    continue;
			  }
			  $line .= $words[$i];
			  if($words[$i] == "**") {
				if($boldActive !=true) {
					$boldActive = true;		
				} else {
					$boldActive = false;
				    $text.=($text != '' ? '** ##'.' ' : ' ');
				    $line = '';
				    continue;
				}		  
			  } elseif ($boldActive == true) {
				if (isset($fontstyleArray[$fontName])) {
					$styles = explode(", ", $fontstyleArray[$fontName]);
					foreach ($styles as $style) {
						if($style == "Bold") { 						
							$dimensions = imagettfbbox($fontsize, 0, $fontBold, $line);
						}
					}
				}
			  } else {
				$dimensions = imagettfbbox($fontsize, 0, $fontRegular, $line);
			  }
			  
			  if($words[$i] == "__") {
				if($italicActive !=true) {
					$italicActive = true;		
				} else {
					$italicActive = false;
				    $text.=($text != '' ? '__ ##'.' ' : ' ');
				    $line = '';
				    continue;
				}		  
			  } elseif ($italicActive == true) {
				if (isset($fontstyleArray[$fontName])) {
					$styles = explode(", ", $fontstyleArray[$fontName]);
					foreach ($styles as $style) {
						if($style == "Italic") { 						
							$dimensions = imagettfbbox($fontsize, 0, $fontItalic, $line);
						}
					}
				}
			  } else {
				$dimensions = imagettfbbox($fontsize, 0, $fontRegular, $line);
			  }
			  
			  if($words[$i] == "//") {
				if($boldItalicActive !=true) {
					$boldItalicActive = true;		
				} else {
					$boldItalicActive = false;
				    $text.=($text != '' ? '// ##'.' ' : ' ');
				    $line = '';
				    continue;
				}		  
			  } elseif ($boldItalicActive == true) {
				if (isset($fontstyleArray[$fontName])) {
					$styles = explode(", ", $fontstyleArray[$fontName]);
					foreach ($styles as $style) {
						if($style == "BoldItalic") { 						
							$dimensions = imagettfbbox($fontsize, 0, $boldItalicActive, $line);
						}
					}
				}
			  } else {
				$dimensions = imagettfbbox($fontsize, 0, $fontRegular, $line);
			  }
			  
			  $lineWidth = $dimensions[2] - $dimensions[0];
			  if ($lineWidth > $iwidth) {
			    if ($boldActive == true) {
					$text.=($text != '' ? '** ##'.$words[$i].' ' : $words[$i].' ');
					$line = $words[$i].' ';	
				} elseif ($italicActive == true) {
					$text.=($text != '' ? '__ ##'.$words[$i].' ' : $words[$i].' ');
					$line = $words[$i].' ';	
				} elseif ($boldItalicActive == true) {
					$text.=($text != '' ? '// ##'.$words[$i].' ' : $words[$i].' ');
					$line = $words[$i].' ';	
				} else {
					$text.=($text != '' ? '##'.$words[$i].' ' : $words[$i].' ');
					$line = $words[$i].' ';
				}
			    
			  }
			  else {
			    $text.=$words[$i].' ';
			    $line.=' ';
			  }
			}
			$text= preg_replace("/(<br> *##)|(<br>##)|(## *<br>)|(##<br>)|(<BR> *##)|(<BR>##)|(## *<BR>)|(##<BR>)/", "##", $text);		
			$pattern = "/(##)|(<\s*br\s*\/?>)/";	
			$wordings = preg_split( $pattern, $text );
			$wordingIndex = 0;
			foreach ($wordings as $wording) {				
				$wording = trim($wording);
				if(preg_match("/[a-zA-Z]/i", $wording)){
				    $wordingIndex++;				    
				} else {
					break;
				}				
			}
			unset($wordings[$wordingIndex]);
			if(count($wordings) > 1) {
				$space =  -((count($wordings)*$lineheight)/2)+($lineheight/2);
			} else {
				$space = 0;
			}
			foreach ($wordings as $wording) {
				if (strpos($wording,'**') !== false) {					
					$wording = str_replace("** ", "", $wording);
					$wording = str_replace(" ** ", "", $wording);
					$wording = str_replace(" **", "", $wording);
					$wording = str_replace("**", "", $wording);
					$wording = trim($wording);
					$x = center_text($wording, $fontsize, $iwidth, $fontBold);
					imagettftext($im, $fontsize, 0, $x, $y+$space, $color['color'], $fontBold, $wording);
				} elseif (strpos($wording,'__') !== false) {
					$wording = str_replace("__ ", "", $wording);
					$wording = str_replace(" __ ", "", $wording);
					$wording = str_replace(" __", "", $wording);
					$wording = str_replace("__", "", $wording);
					$wording = trim($wording);
					$x = center_text($wording, $fontsize, $iwidth, $fontItalic);
					imagettftext($im, $fontsize, 0, $x, $y+$space, $color['color'], $fontItalic, $wording);	
				} elseif (strpos($wording,'//') !== false) {
					$wording = str_replace("// ", "", $wording);
					$wording = str_replace(" // ", "", $wording);
					$wording = str_replace(" //", "", $wording);
					$wording = str_replace("//", "", $wording);
					$wording = trim($wording);
					$x = center_text($wording, $fontsize, $iwidth, $fontBoldItalic);
					imagettftext($im, $fontsize, 0, $x, $y+$space, $color['color'], $fontBoldItalic, $wording);	
				} else {
					$wording = trim($wording);
					$x = center_text($wording, $fontsize, $iwidth, $fontRegular);
					imagettftext($im, $fontsize, 0, $x, $y+$space, $color['color'], $fontRegular, $wording);
				}					
				$space = $space+$lineheight;	
			}	
			// create the image
			imagejpeg($im, $file, $quality);
			$iteration++;
		}		
		return $files;	
}

function center_text($string, $font_size, $iwidth, $font){
			$image_width = $iwidth;
			$dimensions = imagettfbbox($font_size, 0, $font, $string);			
			return ceil(($image_width - $dimensions[4]) / 2);				
}

function gradient($w=100, $h=100, $c=array('#FFFFFF','#FF0000','#00FF00','#0000FF'), $hex=true) {
	/*
	Generates a gradient image
	
	Author: Christopher Kramer
	
	Parameters:
	w: width in px
	h: height in px
	c: color-array with 4 elements:
	$c[0]:   top left color
	$c[1]:   top right color
	$c[2]:   bottom left color
	$c[3]:   bottom right color
	
	if $hex is true (default), colors are hex-strings like '#FFFFFF' (NOT '#FFF')
	if $hex is false, a color is an array of 3 elements which are the rgb-values, e.g.:
	$c[0]=array(0,255,255);
	
	*/
	
	$im=imagecreatetruecolor($w,$h);
		
	if($hex) {  // convert hex-values to rgb
		for($i=0;$i<=3;$i++) { 
		$c[$i]=hex2rgb($c[$i]);
		}
	}		
	
	$rgb=$c[0]; // start with top left color
	for($x=0;$x<=$w;$x++) { // loop columns
		for($y=0;$y<=$h;$y++) { // loop rows
			// set pixel color 
			$col=imagecolorallocate($im,$rgb[0],$rgb[1],$rgb[2]);
			imagesetpixel($im,$x-1,$y-1,$col);
			// calculate new color  
			for($i=0;$i<=2;$i++) {
				$rgb[$i]=
				$c[0][$i]*(($w-$x)*($h-$y)/($w*$h)) +
				$c[1][$i]*($x     *($h-$y)/($w*$h)) +
				$c[2][$i]*(($w-$x)*$y     /($w*$h)) +
				$c[3][$i]*($x     *$y     /($w*$h));
			}
		}
	}
	return $im;
}

function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}

$user = array(
		'uppercase' => false,
		'lowercase' => false,
		'standardwording'=> 'Coupon wording',
		'verticalwording'=> '', 
		'font-size'=>'12',
		'line-height'=>'16',
		'font-size-big'=>'16',
		'line-height-big'=>'22',
		'font-size-small'=>'10',
		'line-height-small'=>'14',
		'color'=>'#ffffff',
		'bgcolor'=>'#000000',
		'bgcolor2'=>'',
		'angle'=>'vertical',
		'font'=>'font/Arial.ttf'	
		
);


if(isset($_POST['submit'])){
	error_log ('form submitted');
	$error = array();

	if(strlen($_POST['standardwording'])==0){
		$error[] = 'Please enter a wording';
	}
	
	if(count($error)==0){
		if (isset($_POST['uppercase'])) {
		    $uppercase = true;
		} else {
			$uppercase = false;
		}
		
		if (isset($_POST['lowercase'])) {
		    $lowercase = true;
		} else {
			$lowercase = false;
		}
		
		$user = array(
				'uppercase' => $uppercase,
				'lowercase' => $lowercase,
				'standardwording'=> $_POST['standardwording'],
				'verticalwording'=> $_POST['verticalwording'], 
				'font-size'=>$_POST['fontsize'],
				'line-height'=>$_POST['lineheight'],
				'font-size-big'=>$_POST['fontsizebig'],
				'line-height-big'=>$_POST['lineheightbig'],
				'font-size-small'=>$_POST['fontsizesmall'],
				'line-height-small'=>$_POST['lineheightsmall'],
				'color'=>$_POST['color'],
				'bgcolor'=>$_POST['bgcolor'],
				'bgcolor2'=>$_POST['bgcolor2'],
				'angle'=>$_POST['angle'],
				'font'=>$_POST['font']			
		);		
		
	}
	
}

// run the script to create the image
$filename = create_image($user);
$filenames = split('##', $filename);

/*
$zipname = 'coupons-'.rand(0,1292938).'.zip';
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($filenames as $file) {
  $zip->addFile($file);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zipname);
header('Content-Length: ' . filesize($zipname));
readfile($zipname);
*/

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Coupon's 💫</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script type="text/javascript" src="jszip.min.js"></script>
<script type="text/javascript" src="jszip-utils.min.js"></script>
<script type="text/javascript" src="FileSaver.min.js"></script>
<script type="text/javascript" src="masonry.pkgd.min.js"></script>
<script type="text/javascript" src="imagesloaded.pkgd.min.js"></script>
<script type="text/javascript" src="jscolor.min.js"></script>
<script type="text/javascript" src="materialize/js/materialize.min.js"></script>
<!-- <link rel="stylesheet" type="text/css" href="form-basic.css?ver=<?=rand(0,1292938);?>" /> -->
<link type="text/css" rel="stylesheet" href="materialize/css/materialize.min.css"  media="screen,projection"/>
<link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="icon" href="favicon.ico" type="image/x-icon"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
	<?php foreach ($fontnames as $fontfile) { ?>
	@font-face {
		font-family: "<?php echo $fontfile['basename']; ?>";
		src:  url('<?php echo $fontfile["pathname"]; ?>') format('truetype');
	}
	<?php } ?>
	
	header, main, footer {
      padding-left: 400px;
    }
    
    .material-tooltip { white-space: pre; text-align: left; } 
    
    .side-nav {
	    width: 400px;
    }
    
    .navTitle {
	    position: fixed;
	    padding-right: 400px;
	    margin-left: 0;
	    top: 0;
	    z-index: 100;
    }
    
    .container {
	    width: 100%;
	    padding: 0 5%;
	    margin: 0;
	    max-width: none;
	}
	
	.anglechoice ul.dropdown-content {
		left: -40px !important;
		min-width: 139px;
	}
	
	[type="checkbox"]+label {
		font-size:0.8rem;
	}
	
	/* label color */
   .input-field label {
     color: #9e9e9e;
     top:0;
   }
   /* label focus color */
   .input-field input[type=text]:focus + label {
     color: #9e9e9e;
   }
   /* label underline focus color */
   .input-field input[type=text]:focus {
     border-bottom: 1px solid #000;
     box-shadow: 0 1px 0 0 #000;
   }
   /* valid color */
   .input-field input[type=text].valid {
     border-bottom: 1px solid #000;
     box-shadow: 0 1px 0 0 #000;
   }
   /* invalid color */
   .input-field input[type=text].invalid {
     border-bottom: 1px solid #000;
     box-shadow: 0 1px 0 0 #000;
   }
   /* icon prefix focus color */
   .input-field .prefix.active {
     color: #000;
   }
   
   .dropdown-content li>a, .dropdown-content li>span {
	   color:#000;
   }
   
   [type="checkbox"]:checked+label:before {
	   border-right: 2px solid #f57c00;
	   border-bottom: 2px solid #f57c00;
   }

    @media only screen and (max-width : 992px) {
      header, main, footer {
        padding-left: 0;
      }
    }

</style>
</head>

<body class="grey lighten-3">
	<header>
		<div id="nav-mobile" class="side-nav fixed" style="left: 0px; z-index: 200;">			
			<form class="col s12" action="" method="post" id="couponGenerator">
 				<input type="submit" value="Submit" name="submit" id="submitHide" class="hide" />
				<div class="row">
					<h5 class="header col s12 orange-text text-darken-2" style="font-weight: 200">Wording</h5>
				</div>
				<div class="row">
					<div class="col s6">
						<input type="checkbox" name="uppercase" id="uppercase" <?php if(isset($_POST['uppercase'])){ ?>checked<?php }?>>
						<label for="uppercase">Uppercase</label>
					</div>
					<div class="col s6">
						<input type="checkbox" name="lowercase" id="lowercase" <?php if(isset($_POST['lowercase'])){ ?>checked<?php }?>>
						<label for="lowercase">Lowercase</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<input type="text" value="<?php if(isset($_POST['standardwording'])){echo $_POST['standardwording'];}?>" name="standardwording" id="standardwording" class="tooltipped validate" data-position="right" data-delay="50" data-tooltip="How to format your text:
Line break: Your Text <br> Your Text
Bold: ** Your Text **
Italic: __ Your Text __
Bold Italic: // Your Text //" required>
						<label for="standardwording">Coupon wording</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<input type="text" value="<?php if(isset($_POST['verticalwording'])){echo $_POST['verticalwording'];}?>" name="verticalwording" id="verticalwording" class="tooltipped" data-position="right" data-delay="50" data-tooltip="You can add a second wording
in case you need a different presentation
for more vertical coupons">
						<label for="verticalwording">Vertical Coupon wording</label>
					</div>
				</div>
				<div class="row">
					<h5 class="header col s12 orange-text text-darken-2" style="font-weight: 200">Font</h5>
				</div>
				<div class="row">
					<div class="input-field col s9 customFont">
						<select name="font">
						<?php $oldfontname = ""; ?>
						<?php $fontstyle = ""; ?>
						<?php foreach ($fontnames as $fontfile) { ?>
							<?php $newfontname = split('-', $fontfile['basename']); ?>
							<?php if($newfontname[0] != $oldfontname) { ?>
								<?php if($oldfontname != "" && $fontstyle != "") { ?>
									<?php $lengthfontstyle = split(',', $fontstyle); ?>
									<?php if(count($lengthfontstyle) > 1) { ?>
										-Regular</option>
										<?php $fontstyleArray[$newfontname[0]] = $fontstyle; ?>
									<?php } else { ?>
										-Regular</option>
									<?php } ?>
								<?php } else { ?>
									</option>
								<?php } ?>
								<option value="<?php echo $fontfile['pathname']; ?>" <?php if(isset($_POST['font']) and $_POST['font'] == $fontfile['pathname']){?>selected="selected"<?php } ?>><?php echo $newfontname[0]; ?>											<?php $oldfontname = $newfontname[0]; ?>
								<?php if (isset($newfontname[1])) { ?>
									<?php $fontstyle = $newfontname[1]; ?>
								<?php } else { ?>
									<?php $fontstyle = ""; ?>
								<?php } ?>
							<?php } else { ?>
								<?php if($fontstyle != "") { ?>
									<?php $fontstyle = $fontstyle.", ".$newfontname[1]; ?>
								<?php } else { ?>
									<?php $fontstyle = $newfontname[1]; ?>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						-Regular</option>
<!--	
						  <option value="font/Arial.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Arial.ttf"){?>selected="selected"<?php } ?>>Arial</option>
						  <option value="font/Arial-Black.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Arial-Black.ttf"){?>selected="selected"<?php } ?>>Arial Black</option>
						  <option value="font/Comic-Sans-MS.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Comic-Sans-MS.ttf"){?>selected="selected"<?php } ?>>Comic-Sans-MS</option>
						  <option value="font/Courier-New.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Courier-New.ttf"){?>selected="selected"<?php } ?>>Courier-New</option>
						  <option value="font/Minerva-ModernRegular.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Minerva-ModernRegular.ttf"){?>selected="selected"<?php } ?>>Minerva Modern Regular</option>
						  <option value="font/MyriadPro-Regular.otf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/MyriadPro-Regular.otf"){?>selected="selected"<?php } ?>>MyriadPro Regular</option>
						  <option value="font/MyriadPro-Bold.otf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/MyriadPro-Bold.otf"){?>selected="selected"<?php } ?>>MyriadPro Bold</option>
						  <option value="font/Tahoma.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Tahoma.ttf"){?>selected="selected"<?php } ?>>Tahoma</option>
						  <option value="font/Tahoma-Bold.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Tahoma-Bold.ttf"){?>selected="selected"<?php } ?>>Tahoma Bold</option>
						  <option value="font/Trebuchet-MS.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Trebuchet-MS.ttf"){?>selected="selected"<?php } ?>>Trebuchet-MS</option>
						  <option value="font/WeibeiSC-Bold.otf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/WeibeiSC-Bold.otf"){?>selected="selected"<?php } ?>>WeibeiSC Bold</option>
						  <option value="font/Capriola-Regular.ttf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Capriola-Regular.ttf"){?>selected="selected"<?php } ?>>Capriola</option>
						  <option value="font/Apercu-Bold.otf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Apercu-Bold.otf"){?>selected="selected"<?php } ?>>Apercu Bold</option>
						  <option value="font/Eames-Century-Modern-Bold.otf" <?php if(isset($_POST['font']) and $_POST['font'] == "font/Eames-Century-Modern-Bold.otf"){?>selected="selected"<?php } ?>>Eames Century Modern Bold</option>
-->  
						</select>
					</div>
					<div class="input-field col s3">
						<input class="jscolor" type="text" value="<?php if(isset($_POST['color'])){echo $_POST['color'];} else { echo "#ffffff"; }?>" name="color" id="color">
						<label for="color">Wording color</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s6">
						<i class="material-icons prefix">format_color_fill</i>
						<input class="jscolor" type="text" value="<?php if(isset($_POST['bgcolor'])){echo $_POST['bgcolor'];} else { echo "#000000"; }?>" name="bgcolor" id="bgcolor">
						<label for="bgcolor">BG color</label>
					</div>
					<div class="input-field col s3 gradientchoice">
						<input class="jscolor {required:false}" type="text" value="<?php if(isset($_POST['bgcolor2'])){echo $_POST['bgcolor2'];}?>" name="bgcolor2" id="bgcolor2">
						<label for="bgcolor2">Gradient</label>
					</div>
					<div class="input-field col s3 anglechoice">
						<select name="angle">
							<option value="vertical" <?php if(isset($_POST['angle']) and $_POST['angle'] == 'vertical'){?>selected="selected"<?php } ?>>Vertical</option>
							<option value="horizontal" <?php if(isset($_POST['angle']) and $_POST['angle'] == 'horizontal'){?>selected="selected"<?php } ?>>Horizontal</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s6">
						<i class="material-icons prefix">format_size</i>
						<input type="text" value="<?php if(isset($_POST['fontsizesmall'])){echo $_POST['fontsizesmall'];} else { echo "10"; }?>" name="fontsizesmall" id="fontsizesmall">
						<label for="fontsizesmall">Small font size</label>
					</div>				
					<div class="input-field col s6">
						<i class="material-icons prefix">format_line_spacing</i>
						<input type="text" value="<?php if(isset($_POST['lineheightsmall'])){echo $_POST['lineheightsmall'];} else { echo "14"; }?>" name="lineheightsmall" id="lineheightsmall">
						<label for="lineheightsmall">Small line height</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s6">
						<i class="material-icons prefix">format_size</i>
						<input type="text" value="<?php if(isset($_POST['fontsize'])){echo $_POST['fontsize'];} else { echo "12"; }?>" name="fontsize" id="fontsize">
						<label for="fontsize">Standard font size</label>
					</div>				
					<div class="input-field col s6">
						<i class="material-icons prefix">format_line_spacing</i>
						<input type="text" value="<?php if(isset($_POST['lineheight'])){echo $_POST['lineheight'];} else { echo "16"; }?>" name="lineheight" id="lineheight">
						<label for="lineheight">Standard line height</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s6">
						<i class="material-icons prefix">format_size</i>
						<input type="text" value="<?php if(isset($_POST['fontsizebig'])){echo $_POST['fontsizebig'];} else { echo "16"; }?>" name="fontsizebig" id="fontsizebig">
						<label for="fontsizebig">Big font size</label>
					</div>				
					<div class="input-field col s6">
						<i class="material-icons prefix">format_line_spacing</i>
						<input type="text" value="<?php if(isset($_POST['lineheightbig'])){echo $_POST['lineheightbig'];} else { echo "22"; }?>" name="lineheightbig" id="lineheightbig">
						<label for="lineheightbig">Big line height</label>
					</div>
				</div>
			</form>
		</div>
	</header>
	<main>
		<nav class="top-nav white navTitle">
	        <div class="container">
	          <div class="nav-wrapper">
		          <a class="page-title orange-text text-darken-2" style="font-weight: 200; font-size: 30px;">The magical mystery 💫</a>
		          <ul id="nav-mobile" class="right hide-on-med-and-down">
			        <li><a onclick="downloadAllImages()" class="black-text"><i class="tiny material-icons left">file_download</i>Download Zip</a></li>
			        <li><a id="submitForm" onclick="submitForm()" class="black-text"><i class="material-icons left">style</i>Update Coupons</a></li>
			      </ul>
	          </div>
	        </div>
	      </nav>
		<div class="container" style="margin-top:100px; margin-bottom: 50px;">
			
			
<!--
			<div class="row">
				<h1 class="orange-text text-darken-2" style="font-weight: 200">The magical mystery 💫</h1>
			</div>
			<div class="row pushpin white">
				<a class="waves-effect waves-light btn orange darken-4" onclick="downloadAllImages()"><i class="material-icons left">file_download</i>Download Zip</a>
				<a id="submitForm" class="waves-effect waves-light btn orange darken-2" onclick="submitForm()"><i class="material-icons left">style</i>Update Coupons</a>
			</div>
-->
			<div class="row grid">
				<?php foreach ($filenames as $filejpg) { ?>
				<div class="grid-item" style="opacity: 0;">
					<div class="card-panel hoverable small">
						<div class="card-image">
				            <img src="<?=$filejpg;?>?id=<?=rand(0,1292938);?>">
				        </div>
				        <div class="card-content">
							<h6><?php 	$couponname = split('&', $filejpg);
									$couponname = split('/', $couponname[0]);
									echo $couponname[1]; ?></h6>
				        </div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</main>
<script>	
	$('.grid').imagesLoaded( function() {
		var $grid = $('.grid').masonry({
		  // options
		  itemSelector: '.grid-item',
		  columnWidth: 200
		});
		index=1;
		$('.grid-item').each(function( index ) {
			
		  $(this).delay(100*index).fadeTo( "slow", 1 );	
		  index++;
		});	  
	});

	
	$(document).ready(function() {
	    $('select').material_select();
	    $(".customFont li span").each(function(){
		    var stringFontName = $(this).text();
		    stringFontName = stringFontName.replace(/\s\s+/g, ' ');
		    $(this).text(stringFontName.replace(" ", ""));
		    $(this).css('font-family', $(this).text());
		});
	    if( $('#standardwording').val() ) {
	    	Materialize.toast('💩 Hey! Your coupons are generated, enjoy mate', 4000);
	    }
	});
 
	function downloadAllImages(){
		var zip = new JSZip();
		var deferreds = [];
		<?php foreach ($filenames as $filejpg) { ?>
			<?php 	$filename = split('/', $filejpg);
			$filename = split('_', $filename[1]);	    	
			?> 
			deferreds.push( addToZip(zip, "<?=$filejpg;?>", "<?=$filename[1];?>") );
		<?php } ?>
		$.when.apply(window, deferreds).done(generateZip);
	}
	$('#submitForm').click(function() {
		$('#submitHide').trigger('click');
	});
	
	function generateZip(zip) {
		var content = zip.generate({type:"blob"});
		// see FileSaver.js
		saveAs(content, "coupon-<?=rand(0,1292938);?>.zip");
	}
	function addToZip(zip, imgLink, i) {
		var deferred = $.Deferred();
		JSZipUtils.getBinaryContent(imgLink, function (err, data) {
			if(err) {
				alert("Problem happened when download img: " + imgLink);
				console.erro("Problem happened when download img: " + imgLink);
				deferred.resolve(zip); // ignore this error: just logging
				// deferred.reject(zip); // or we may fail the download
			} else {
				zip.file("cpn_"+i+"_1.jpg", data, {binary:true});
				deferred.resolve(zip);
			}
	 	});
	 	return deferred;
	}
 
</script>
</body>
</html>
