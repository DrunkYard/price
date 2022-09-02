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
	  padding: 1px;
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
	    position: relative;
	    font-size: 36px;
		color: black;
	}

	.strikethrough:before {
	    border-bottom: 3px solid black;
	    position: absolute;
	    content: "";
	    width: 150%;
	    height: 0%;
	    transform: rotate(-30deg);
	}

	.percent {
		transform-origin: left;
		transform: rotate(-20deg); 
		font-size: 21px;
		font-weight: 900;
	}

	.price {
			width: 8.1cm;
			height: 4.1cm;
	}
	
	.new_price {
		font-size: 36px;
	}
	
	.old_price {
		font-size: 11px;
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
Выберите файл-отчет(Gestori): <input type="file" name="filename" size="10"><br><br>
Выберите файл-акция: <input type="file" name="filename2" size="10">
<!---<br><br>Введите процент скидки: <input type="text" name="percent">
<br><br>Введите коэфициент для весового товара (кг -> г): <input type="text" name="kof"><br>
<i>(Например чтобы получить цену за 100г введите коэфициент 10,<br>
т.е. текущая цена из Excel файла будет поделена на коэфициент<br>
если коэфициент не указан, то преобразованя не будет)</i>-->
<br><br><input type="submit" name='small' value="Создать ценники">
<!--- <input type="submit" name='big' value="Большой ценник"> -->
<br><br><input type="button" value="Для печати" onclick="rembutton()">
</form>
_END;

	if ($_FILES) {
		$filename = $_FILES['filename']['name'];
		$filename2 = $_FILES['filename2']['name'];
		move_uploaded_file ($_FILES['filename']['tmp_name'], $filename);
		move_uploaded_file ($_FILES['filename2']['tmp_name'], $filename2);
		$today = date("d.m.Y");
		
		$file_xls = PHPExcel_IOFactory::load("$filename");
		$file_xls2 = PHPExcel_IOFactory::load("$filename2");
		
		$file_xls->setActiveSheetIndex(0);
		$file_xls2->setActiveSheetIndex(0);
		
		$sheet = $file_xls->getActiveSheet();
		$sheet2 = $file_xls2->getActiveSheet();

		$rows = $sheet->getHighestRow();
		$rows2 = $sheet2->getHighestRow();

		// $depart = explode(":", $sheet->getCellByColumnAndRow(0, 2)->getValue());
		// $depart = explode(" ", trim($depart[1]));

		$count = $sheet->getHighestRow() > $sheet2->getHighestRow() ? $sheet->getHighestRow() : $sheet2->getHighestRow();

		for ($i = 8; $i <= $count; $i++) {
			$name[$sheet->getCellByColumnAndRow(8, $i)->getValue()] = $sheet->getCellByColumnAndRow(9, $i)->getValue();

			$name[$sheet2->getCellByColumnAndRow(1, $i)->getValue()] = $sheet2->getCellByColumnAndRow(2, $i)->getValue();

			$code[$sheet->getCellByColumnAndRow(8, $i)->getValue()] = $sheet->getCellByColumnAndRow(12, $i)->getValue();

			$cost1[$sheet->getCellByColumnAndRow(8, $i)->getValue()] = $sheet->getCellByColumnAndRow(17, $i)->getValue();

			$cost2[$sheet2->getCellByColumnAndRow(1, $i)->getValue()] = $sheet2->getCellByColumnAndRow(7, $i)->getValue();

			$ed_sv[$sheet->getCellByColumnAndRow(8, $i)->getValue()] = $sheet->getCellByColumnAndRow(11, $i)->getValue();
		}

		$i = 0;
		foreach ($name as $key => $val) {
			if ((array_key_exists($key, $cost1)) and 
			 (array_key_exists($key, $cost2))) {
				$tovar[$i++] = $key.";".$code[$key].";".$val.";".$ed_sv[$key].";".$cost1[$key].";".$cost2[$key];
			}
		}

		if ($_POST['small']) {
			echo "<table class='rows' width='100%'>";
			for ($i = 0; $i <= count($tovar); $i+=2) {
				if (explode(";", $tovar[$i])[2] != '') {
					$new_price = explode(";", $tovar[$i])[4];
					$old_price = explode(";", $tovar[$i])[5];
					$procent = round(((explode(";", $tovar[$i])[5] - explode(";", $tovar[$i])[4])/explode(";", $tovar[$i])[5]) * 100);

					$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
					$old_kop = explode('.', number_format(round($old_price, 1), 2, '.', ''));						

					$full_name = explode(";", $tovar[$i])[2];
					if (strlen($full_name) <= 55) $font_size = '12px';
					if (strlen($full_name) <= 45) $font_size = '14px';
					if (strlen($full_name) <= 35) $font_size = '16px';
					if (strlen($full_name) <= 25) $font_size = '18px';

					if ($new_kop[0] > 1000) { $price_size = '52px';}
					else { $price_size = '64px'; }			

					exec('curl "http://price.loc/wper/code.php?data='.explode(";", $tovar[$i])[1].'"');

					$fname = "img/".explode(";", $tovar[$i])[1].".png";
					
					echo "<tr class='rowe' style='page-break-inside: avoid;' width='50%'><td align='right'><table class='price' width='100%'>";

					echo "<tr><td colspan='5' align='left'><b id='txt' style='font-size: ".$font_size."'>".$full_name."</b></td></tr>";

					echo "<tr><td rowspan='2' style='font-size: 24px; font-family: Impact'><b>Скидка</b></td><td colspan='2'>старая</td><td align='right' rowspan='3' style='font-size: ".$price_size."; font-family: Impact'><b>".$new_kop[0]."</b></td><td rowspan='2' style='font-size: 21px; font-family: Impact'>".$new_kop[1]."</td></tr>";

					echo "<tr><td>цена</td><td><b style='font-size: 21px;
					  font-family: Impact'>".$old_kop[1]."</b></td></tr>";					

					echo "<tr><td class='percent' align='left' rowspan='2' style='font-size: 36px; font-family: Impact'><b>-".$procent."%</b></td><td align='right' style='font-size: 30px; font-family: Impact; vertical-align: top;' class='strikethrough'>".$old_kop[0]."</td><td></td><td></td></tr>";

					echo "<tr><td align='right' colspan='4' rowspan='2'><img src='".$fname."'></td></tr>";
					echo "<tr><td align='left' colspan='1' style='font-size: 8px;'>".$today."</td></tr></table></td>";

					$new_price = explode(";", $tovar[$i+1])[4];
					$old_price = explode(";", $tovar[$i+1])[5];

					$procent = round(((explode(";", $tovar[$i + 1])[5] - explode(";", $tovar[$i + 1])[4])/explode(";", $tovar[$i + 1])[5]) * 100);

					$new_kop = explode('.', number_format(round($new_price, 1), 2, '.', ''));
					$old_kop = explode('.', number_format(round($old_price, 1), 2, '.', ''));

					$full_name = explode(";", $tovar[$i+1])[2];

					exec('curl "http://price.loc/wper/code.php?data='.explode(";", $tovar[$i+1])[1].'"');

					$fname = "img/".explode(";", $tovar[$i+1])[1].".png";
					if (strlen($full_name) <= 55) $font_size = '12px';
					if (strlen($full_name) <= 45) $font_size = '14px';
					if (strlen($full_name) <= 35) $font_size = '16px';
					if (strlen($full_name) <= 25) $font_size = '18px';

					if ($new_kop[0] > 1000) { $price_size = '52px';}
					else { $price_size = '64px'; }
					
					if ($full_name != '') {
						echo "<td align='left'><table width='100%' class='price'>";
						
						echo "<tr ><td colspan='5' align='left'><b id='txt' style='font-size: ".$font_size."'>".$full_name."</b></td></tr>";

						echo "<tr><td rowspan='2' style='font-size: 24px; font-family: Impact'><b>Скидка</b></td><td colspan='2'>старая</td><td align='right' rowspan='3' style='font-size: ".$price_size."; font-family: Impact'><b>".$new_kop[0]."</b></td><td rowspan='2' style='font-size: 21px; font-family: Impact'>".$new_kop[1]."</td></tr>";

						echo "<tr><td>цена</td><td><b style='font-size: 21px; font-family: Impact'>".$old_kop[1]."</b></td></tr>";					

						echo "<tr><td class='percent' align='left' rowspan='2' style='font-size: 36px; font-family: Impact'><b>-".$procent."%</b></td><td align='right' style='font-size: 30px; font-family: Impact; vertical-align: top;' class='strikethrough'>".$old_kop[0]."</td><td></td><td></td></tr>";

						echo "<tr><td align='right' colspan='4' rowspan='2'><img src='".$fname."'></td></tr>";
						echo "<tr><td align='left' colspan='1' style='font-size: 8px;'>".$today."</td></tr>";
					}
					echo "</table></td>";
				}
			}
			echo "</table>";
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