<?php
include 'barcode.php';

print_r();

$generator = new barcode_generator();

$format = "png";
$symbology = "ean-128";
$options = array(
	'w'=>'150', 'h'=>'35'
);

$data = $_GET["data"];
$fname = "img/".$data.".png";

/* Output directly to standard output. */
$generator->output_image($format, $symbology, $data, $options);

/* Create bitmap image. */
$image = $generator->render_image($symbology, $data, $options);
imagepng($image, $fname);
imagedestroy($image);

/* Generate SVG markup. */
$svg = $generator->render_svg($symbology, $data, $options);
echo $svg;
?>