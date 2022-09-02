<?php
require_once('Classes/PHPExcel/IOFactory.php');

echo <<<_END
<html>
<head>
<title>Ценник</title>
<style type="text/css">
   	table.price {
	  border-collapse: collapse;
	  border: 1px solid black;
	}

	table.big_price {
		border-collapse: separate;
	  	border: 1px solid black;	
	}

	// table {
	//   border-collapse: collapse;
	// }

	// table, th, td {
	//   border: 1px solid black;
	// }

	.strikethrough {
		display: inline-block;
	    position: relative;
	    font-size: 40px;
	}

	.strikethrough:before, .strikethrough:after {		
		border-bottom: 2px solid black;
		position: absolute;
		content: "";
		left: 0;
		top: 50%;
		width: 100%;
		height: 1px;
		transform: rotate(15deg);
	}

	.strikethrough:after {
	    transform: rotate(-15deg);
	}

	@media print {
		.price {
			width: 8.1cm;
			height: 4.1cm;
		}
		.big_price {
			page-break-inside: avoid;
			width: 100%;
			height: 100%;
		}
		.new_price {
			font-size: 36px;
		}
		
		.old_price {
			font-size: 11px;
		}

	}
</style>
<script src='jquery-3.4.1.min.js'></script>
</head>
<body>
<form method="post" action="index.php" enctype="multipart/form-data" class="remform">
Выберите файл:<input type="file" name="filename" size="10">
<br><br>Введите процент скидки: <input type="text" name="percent">
<br><br>Введите коэфициент для весового товара (кг -> г): <input type="text" name="kof"><br>
<i>(Например чтобы получить цену за 100г введите коэфициент 10,<br>
т.е. текущая цена из Excel файла будет поделена на коэфициент<br>
если коэфициент не указан, то преобразованя не будет)</i>
<br><br><input type="submit" name='small' value="Маленький ценник">
<input type="submit" name='big' value="Большой ценник">
<br><br><input type="button" value="Для печати" onclick="rembutton()">
</form>
_END;

	if ($_FILES) {
		$filename = $_FILES['filename']['name'];
		move_uploaded_file ($_FILES['filename']['tmp_name'], $filename);
		$today = date("d.m.Y");
		
		$file_xls = PHPExcel_IOFactory::load("$filename");
		$file_xls->setActiveSheetIndex(0);
		$sheet = $file_xls->getActiveSheet();

		$rows = $sheet->getHighestRow();
		$font_size = '14px';

		if ($_POST['small']) {
			if ($rows == 8) {
				if ($_POST['percent']) {
					$new_price = $sheet->getCellByColumnAndRow(17, 8)->getValue() - ($sheet->getCellByColumnAndRow(17, 8)->getValue() * ($_POST['percent']/100));
				} else {
					$new_price = $sheet->getCellByColumnAndRow(17, 8)->getValue() - ($sheet->getCellByColumnAndRow(17, 8)->getValue() * 1); 
				}

				$old_price = $sheet->getCellByColumnAndRow(17, 8)->getValue();

				$ed = '1 '.$sheet->getCellByColumnAndRow(11, 8)->getValue();
				if ($_POST['kof']) {
					if (is_numeric($_POST['kof'])) {
						if ($ed == '1 кг') {
							$new_price = round($new_price / $_POST['kof'], 2);
							$old_price = round($old_price / $_POST['kof'], 2);
							$ed = (1000/$_POST['kof']).' г.';
						}
					}
				}

				$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
				$old_kop = explode('.', number_format($old_price, 2, '.', ''));

				$full_name = $sheet->getCellByColumnAndRow(9, 8)->getValue();
				if (strlen($full_name) <= 55) $font_size = '12px';
				if (strlen($full_name) <= 45) $font_size = '14px';
				if (strlen($full_name) <= 35) $font_size = '16px';
				if (strlen($full_name) <= 25) $font_size = '18px';

				exec('curl "http://10.114.1.70/test/frontol/code.php?data='.$sheet->getCellByColumnAndRow(12, 8)->getValue().'"');

				$fname = "img/".$sheet->getCellByColumnAndRow(12, 8)->getValue().".png";

				echo "<table class='price' width='50%'>";
				echo "<tr ><td colspan='4' align='center' style='font-size: '".$font_size."'><b>".$full_name."</b></td></tr><tr></tr>";
				echo "<tr><td colspan='4' align='left' style='font-size: 11px'>".$ed."</td></tr>";
				echo "<tr><td width='11px' style='font-size: 11px; font-family: Arial'>старая<br>цена</td><td align='left' style='font-size: 11px; font-family: Impact'><b style='font-size: 30px' class='strikethrough'>".$old_kop[0]."<sup style='vertical-align: top; font-size: 14px;'>".$old_kop[1]."</sup></b></td>";
				echo "<td width='11px' style='font-size: 11px; font-family: Arial'></td><td align='right' style='font-size: 11px; font-family: Impact'><b style='font-size: 60px'>".$new_kop[0]."<sup style='vertical-align: top; font-size: 20px;'>".$new_kop[1]."</sup></b></td></tr>";
				echo "<tr><td align='left' colspan='1' style='font-size: 8px'>".$today."</td><td colspan='3' align='right'><img src='".$fname."'></td></tr>";
				echo "</table></td>";
			} else {
				echo "<table class='rows' width='100%'>";
				for ($i = 8; $i <= $sheet->getHighestRow(); $i+=2) {
					if ($sheet->getCellByColumnAndRow(9, $i)->getValue() != '') {
						if ($_POST['percent']) {
							$new_price = $sheet->getCellByColumnAndRow(17, $i)->getValue() - ($sheet->getCellByColumnAndRow(17, $i)->getValue() * ($_POST['percent']/100)); 
						} else {
							$new_price = $sheet->getCellByColumnAndRow(17, $i)->getValue() - ($sheet->getCellByColumnAndRow(17, $i)->getValue() * 1); 
						}

						$old_price = $sheet->getCellByColumnAndRow(17, $i)->getValue();

						$ed = '1 '.$sheet->getCellByColumnAndRow(11, $i)->getValue();
						if ($_POST['kof']) {
							if (is_numeric($_POST['kof'])) {
								if ($ed == '1 кг') {
									$new_price = round($new_price / $_POST['kof'], 2);
									$old_price = round($old_price / $_POST['kof'], 2);
									$ed = (1000/$_POST['kof']).' г.';
								}
							}
						}

						$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
						$old_kop = explode('.', number_format(round($old_price, 1), 2, '.', ''));						

						$full_name = $sheet->getCellByColumnAndRow(9, $i)->getValue();
						if (strlen($full_name) <= 55) $font_size = '12px';
						if (strlen($full_name) <= 45) $font_size = '14px';
						if (strlen($full_name) <= 35) $font_size = '16px';
						if (strlen($full_name) <= 25) $font_size = '18px';

						exec('curl "http://10.114.1.70/test/frontol/code.php?data='.$sheet->getCellByColumnAndRow(12, $i)->getValue().'"');

						$fname = "img/".$sheet->getCellByColumnAndRow(12, $i)->getValue().".png";
						
						echo "<tr class='rowe' style='page-break-inside: avoid;' width='50%'><td align='right'><table class='price' width='100%'>";
						echo "<tr ><td colspan='4' align='center' style='font-size: ".$font_size."'><b>".$full_name."</b></td></tr><tr></tr>";
						echo "<tr><td colspan='4' align='left' style='font-size: 11px'>".$ed."</td></tr>";
						echo "<tr><td width='11px' style='font-size: 11px; font-family: Arial'>старая<br>цена</td><td align='left' style='font-size: 11px; font-family: Impact'><b style='font-size: 30px' class='strikethrough'>".$old_kop[0]."<sup style='vertical-align: top; font-size: 14px;'>".$old_kop[1]."</sup></b></td>";
						echo "<td width='11px' style='font-size: 11px; font-family: Arial'></td><td align='right' style='font-size: 11px; font-family: Impact'><b style='font-size: 48px'>".$new_kop[0]."<sup style='vertical-align: top; font-size: 20px;'>".$new_kop[1]."</sup></b></td></tr>";
						echo "<tr></tr>";
						echo "<tr><td colspan='1' style='font-size: 8px; vertical-align: bottom'>".$today."</td><td colspan='3' align='right'><img src='".$fname."'></td></tr></table></td>";

						if ($_POST['percent']) {
							$new_price = $sheet->getCellByColumnAndRow(17, $i+1)->getValue() - ($sheet->getCellByColumnAndRow(17, $i+1)->getValue() * ($_POST['percent']/100)); 
						} else {
							$new_price = $sheet->getCellByColumnAndRow(15, $i+1)->getValue() - ($sheet->getCellByColumnAndRow(17, $i+1)->getValue() * 1); 
						}

						$old_price = $sheet->getCellByColumnAndRow(17, $i + 1)->getValue();

						$ed = '1 '.$sheet->getCellByColumnAndRow(11, $i+1)->getValue();
						if ($_POST['kof']) {
							if (is_numeric($_POST['kof'])) {
								if ($ed == '1 кг') {
									$new_price = round($new_price / $_POST['kof'], 2);
									$old_price = round($old_price / $_POST['kof'], 2);
									$ed = (1000/$_POST['kof']).' г.';
								}
							}
						}

						$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
						$old_kop = explode('.', number_format(round($old_price, 1), 2, '.', ''));

						$full_name = $sheet->getCellByColumnAndRow(9, $i+1)->getValue();
						if (strlen($full_name) >= 30) $font_size = '18px';
						if (strlen($full_name) >= 40) $font_size = '16px';
						if (strlen($full_name) >= 50) $font_size = '14px';
						if (strlen($full_name) >= 60) $font_size = '12px';

						exec('curl "http://10.114.1.70/test/frontol/code.php?data='.$sheet->getCellByColumnAndRow(12, $i+1)->getValue().'"');

						$fname = "img/".$sheet->getCellByColumnAndRow(12, $i+1)->getValue().".png";
						
						if ($full_name != '') {
							echo "<td align='left'><table width='100%' class='price'>";
							echo "<tr ><td colspan='4' align='center' style='font-size: ".$font_size."'><b>".$full_name."</b></td></tr><tr></tr>";
							echo "<tr><td colspan='4' align='left' style='font-size: 11px'>".$ed."</td></tr>";
							echo "<tr><td width='11px' style='font-size: 11px; font-family: Arial'>старая<br>цена</td><td align='left' style='font-size: 11px; font-family: Impact'><b style='font-size: 30px' class='strikethrough'> ".$old_kop[0]."<sup style='vertical-align: top; font-size: 14px;'>".$old_kop[1]."</sup></b></td>";
							echo "<td width='11px' style='font-size: 11px; font-family: Arial'></td><td align='right' style='font-size: 11px; font-family: Impact'><b style='font-size: 48px'>".$new_kop[0]."<sup style='vertical-align: top; font-size: 20px;'>".$new_kop[1]."</sup></b></td></tr>";

							echo "<tr><td align='left' colspan='1' style='font-size: 8px; vertical-align: bottom'>".$today."</td><td colspan='3' align='right'><img src='".$fname."'></td></tr>";
						}
						echo "</table></td>";
					}
				}
				echo "</table>";
			}
		} elseif ($_POST['big']) {
			for ($i = 8; $i <= $sheet->getHighestRow(); $i++) {
				if ($sheet->getCellByColumnAndRow(9, $i)->getValue() != '') {
					if ($_POST['percent']) {
						$new_price = $sheet->getCellByColumnAndRow(17, $i)->getValue() - ($sheet->getCellByColumnAndRow(17, $i)->getValue() * ($_POST['percent']/100)); 
					} else {
						$new_price = $sheet->getCellByColumnAndRow(17, $i)->getValue() - ($sheet->getCellByColumnAndRow(17, $i)->getValue() * 1); 
					}

					$old_price = $sheet->getCellByColumnAndRow(17, $i)->getValue();

					$ed = '1 '.$sheet->getCellByColumnAndRow(11, $i)->getValue();
					if ($_POST['kof']) {
						if (is_numeric($_POST['kof'])) {
							if ($ed == '1 кг') {
								$new_price = round($new_price / $_POST['kof'], 2);
								$old_price = round($old_price / $_POST['kof'], 2);
								$ed = (1000/$_POST['kof']).' г.';
							}
						}
					}

					$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
					$old_kop = explode('.', number_format(round($old_price, 1), 2, '.', ''));

					$full_name = $sheet->getCellByColumnAndRow(9, $i)->getValue();

					echo "<table class='big_price' width='99%'>";
					echo "<tr><td colspan='4' rowspan='2' align='center' style='font-weight: bold; font-size: 48px'>".$full_name."</td></tr><tr></tr>";
					echo "<tr><td colspan='4' align='left' style='font-weight: bold; font-size: 14px'>".$ed."</td></tr>";
					echo "<tr><td width='11px' style='font-size: 14px; font-family: Arial'>старая<br>цена</td><td align='left' style='font-size: 11px; font-family: Impact'><b style='font-size: 90px' class='strikethrough'> ".$old_kop[0]."<sup>".$old_kop[1]."</sup></b></td></tr>";
					echo "<tr><td width='11px' style='font-size: 36px; font-family: Arial'></td><td align='left' style='font-size: 11px; font-family: Impact'><b style='font-size: 300px'>".$new_kop[0]."<sup>".$new_kop[1]."</sup></b></td></tr>";
					echo "<tr><td align='right' colspan='4' style='font-size: 24px'>".$today."</td></tr>";
					echo "</table></td>";
				}
			}
		}
	}
echo <<<_END
<script>
	function rembutton() {
		$( ".remform" ).remove();
	}
</script>
</body></html>
_END;
?>