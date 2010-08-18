<?
global $Mp4AbstractBox_UNIQUE_IDS;
$Mp4AbstractBox_UNIQUE_IDS = 0;

class Mp4AbstractBox {
	public $uniqueId;
	public $mp4;
	public $boxInfo;
	public $children;
	protected $_content;
	public $parent;
	
	function __construct(&$tmp_mp4, $tmp_depth = -1) {
		global $Mp4AbstractBox_UNIQUE_IDS;
		
		$this->uniqueId = $Mp4AbstractBox_UNIQUE_IDS++;
		$this->boxInfo = new Mp4AbstractBoxInfo();
		$this->boxInfo->depth = $tmp_depth;
		$this->mp4 = $tmp_mp4;
		$this->children = array();
	}
	
	public function read() {
		if ($this->boxInfo->type != "ROOT") {
			$this->boxInfo->position = ftell($this->mp4->readP);
			$this->boxInfo->size = pReadInt32($this->mp4->readP);
			$this->boxInfo->type = fread($this->mp4->readP, 4);
			
			if ($this->boxInfo->size == 1) {
				$this->boxInfo->headerLength = 16;
				$this->boxInfo->size = pReadInt64($this->mp4->readP);
			} else {
				$this->boxInfo->headerLength = 8;
			}
		}
		
		if ($this->isContainer()) {
			if (MP4_DEBUG) {
				$string_representation = $this->toString();
				if ($string_representation != "") {
					echo str_repeat("&nbsp;", $this->boxInfo->depth * 4) . $string_representation . "<br>";
				}
			}
		
			$i = 0;
			while ($this->_keepReadingChildren() && $i++ < 10) {
				$pos = ftell($this->mp4->readP);
				
				fseek($this->mp4->readP, $pos + 4);
				$next_type = fread($this->mp4->readP, 4);
				fseek($this->mp4->readP, $pos);
				
				switch ($next_type) {
					case "stco":
					case "co64":
						$child = new Mp4Box_stco($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "ctts":
						$child = new Mp4Box_ctts($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "mdhd":
						$child = new Mp4Box_mdhd($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "mvhd":
						$child = new Mp4Box_mvhd($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "stsd":
						$child = new Mp4Box_stsd($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "stss":
						$child = new Mp4Box_stss($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "stsz":
						$child = new Mp4Box_stsz($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "tkhd":
						$child = new Mp4Box_tkhd($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "trak":
						$child = new Mp4Box_trak($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "stsc":
						$child = new Mp4Box_stsc($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "stts":
						$child = new Mp4Box_stts($this->mp4, $this->boxInfo->depth + 1);
						break;
					case "mdat":
						$child = new Mp4Box_mdat($this->mp4, $this->boxInfo->depth + 1);
						break;
					default:
						$child = new Mp4AbstractBox($this->mp4, $this->boxInfo->depth + 1);
						break;
				}
				
				$child->parent = $this;
				$child->read();
				$this->children[] = $child;
			}
		} else {
			$this->readContent();
			fseek($this->mp4->readP, $this->boxInfo->position + $this->boxInfo->size);
			
			if (MP4_DEBUG) {
				$string_representation = $this->toString();
				if ($string_representation != "") {
					echo str_repeat("&nbsp;", $this->boxInfo->depth * 4) . $string_representation . " (" . $this->boxInfo->size . ")<br>";
				}
			}
		}
	}
	
	protected function readContent() {
		$this->_content = fread($this->mp4->readP, $this->boxInfo->size - $this->boxInfo->headerLength);
	}
	
	public function write() {
		$this->boxInfo->position = ftell($this->mp4->writeP);
		
		if ($this->boxInfo->type == "ROOT") {
			foreach ($this->children as $child) {
				$child->write();
			}
		} else {
			if ($this->boxInfo->type == "co64") {
				$this->boxInfo->type = "stco";
				$this->boxInfo->headerLength = 8;
			}
		
			//.write header
			if ($this->boxInfo->headerLength == 8) {
				pWriteInt32($this->mp4->writeP, $this->boxInfo->size);
				fwrite($this->mp4->writeP, $this->boxInfo->type, 4);
			} else {
				pWriteInt32($this->mp4->writeP, 1);
				fwrite($this->mp4->writeP, $this->boxInfo->type, 4);
				pWriteInt64($this->mp4->writeP, $this->boxInfo->size);
			}
			
			if ($this->isContainer()) {
				foreach ($this->children as $child) {
					$child->write();
				}
			} else {
				//.write content
				$this->writeContent();
			}
			
			//.write size again
			$this->boxInfo->size = ftell($this->mp4->writeP) - $this->boxInfo->position;
			fseek($this->mp4->writeP, $this->boxInfo->position);
			if ($this->boxInfo->headerLength == 8) {
				pWriteInt32($this->mp4->writeP, $this->boxInfo->size);
			} else {
				fseek($this->mp4->writeP, 8, SEEK_CUR);
				pWriteInt64($this->mp4->writeP, $this->boxInfo->size);
			}
			
			fseek($this->mp4->writeP, $this->boxInfo->position + $this->boxInfo->size); 
		}
	}
	
	protected function writeContent() {
		fwrite($this->mp4->writeP, $this->_content, strlen($this->_content));
	}
	
	protected function toString() {
		if ($this->boxInfo->type != "ROOT") {
			return "['" . $this->boxInfo->type . "' :: size: " . $this->boxInfo->size . ", position: " . $this->boxInfo->position . "]";
		}
	}
	
	public function dispose() {
		if ($this->isContainer()) {
			foreach ($this->children as $child) {
				$child->dispose();
			}
		} else {
			$this->boxInfo = null;
			$this->_content = null;
			$this->mp4 = null;
			$this->children = null;
			$this->parent = null;
		}
	}
	
	public function isContainer() {
		switch ($this->boxInfo->type) {
			case "ROOT":
			case "moov":
			case "trak":
			case "mdia":
			case "minf":
			case "stbl":
			case "udta":
				return true;
		}
		
		return false;
	}
	
	private $_empty = null;
	public function &box($tmp_type, $tmp_offset = 0) {
		$size = sizeof($this->children);
		for ($i = 0; $i < $size; $i++) {
			if ($this->children[$i]->boxInfo->type == $tmp_type) {
				if ($tmp_offset-- == 0) {
					return $this->children[$i];
				}
			}
		}
		
		if ($tmp_type == "stco") {
			return $this->box("co64", $tmp_offset);
		}
		
		return $this->_empty;
	}
	
	public function boxesOfType($tmp_type) {
		$size = sizeof($this->children);
		$r = array();
		for ($i = 0; $i < $size; $i++) {
			if ($this->children[$i]->boxInfo->type == $tmp_type) {
				$r[] = $this->children[$i];
			}
		}
		
		return $r;
	}
	
	public function removeBox($tmp_box) {
		$size = sizeof($this->children);
		$new_array = array();
		for ($i = 0; $i < $size; $i++) {
			if ($tmp_box->uniqueId == $this->children[$i]->uniqueId) {
				$index = $i;
				break;
			}
		}
		
		array_splice($this->children, $index, 1);
	}
	
	private function _keepReadingChildren() {
		if ($this->boxInfo->type == "ROOT") {
			$stat = fstat($this->mp4->readP);
			return ftell($this->mp4->readP) < (int)$stat["size"];
		} else {
			return ftell($this->mp4->readP) < $this->boxInfo->position + $this->boxInfo->size;	
		}
	}
}

class Mp4AbstractBoxInfo {
	public $position; //box position offset in file
	public $size; //content size including header
	public $type; //string type
	public $headerLength; //8 or 12 bytes of header
	public $depth = 0;
}
?>