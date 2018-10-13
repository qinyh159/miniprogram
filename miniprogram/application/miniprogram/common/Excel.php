<?php

class Excel {
	protected $arrExcel = [];

	protected function _iconv($data) {
		static $mb = false;
		if( $mb === false ) {
			$mb = function_exists('mb_detect_encoding');
		}

		if( $mb ) {
			if( is_array($data) ) {
				foreach ($data as $key => &$value) {
					if( is_array($value) ) {
						$value = $this->_iconv($value);
					} else {
						$value = iconv("UTF-8//IGNORE", "GB18030//IGNORE", $value);
					}
				}
			} else {
				$data = iconv("UTF-8//IGNORE", "GB18030//IGNORE", $data);
			}
		}
		return $data;
	}

	public function pushExcel($arrData) {
		$this->arrExcel[] = $arrData;
	}

	public function arrToExcel($fileName,$arrData = [],$cmd = false) {
		$fileName = strtr($fileName,['.csv' => '.xlsx']);
		if( empty($arrData) ) {
			$arrData = $this->arrExcel;
		}
		//创建新的PHPExcel对象
		// include_once 'miniprogram/app/common/PHPExcel.php';
		Vendor('phpexcel.PHPExcel');
		Vendor('phpexcel.PHPExcel.IOFactory');
		Vendor('phpexcel.PHPExcel.Reader.Excel5');
		$objPHPExcel = new PHPExcel();
		$objProps = $objPHPExcel->getProperties();

		$index = 1;
		foreach ($arrData as $row) {
			$key = ord("A");
			$arrKey = [$key,$key - 1];
			foreach($row as $v) {
				$colum = chr($arrKey[0]);
				if( $arrKey[1] >= ord("A") ) {
					$colum = chr($arrKey[1]) . $colum;
				}

				if( $arrKey[0] == ord("Z") ) {
					$arrKey[0] = ord("A");
					$arrKey[1]++;
				} else {
					$arrKey[0]++;
				}

				$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.($index), $v);
			}
			++$index;
		}

		$objPHPExcel->getActiveSheet()->setTitle('Simple');
		$objPHPExcel->setActiveSheetIndex(0);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		if( $cmd ) {
			$objWriter->save($fileName);
		} else {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Cache-Control: max-age=0');
			header("Content-Disposition:attachment;filename={$fileName}");
			$objWriter->save('php://output'); //文件通过浏览器下载
		}
	}
}
