<?
class Mp4Box_stco extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $entries = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			if ($this->boxInfo->type == "co64") {
				$this->entries[] = pReadInt64($this->mp4->readP);
			} else {
				$this->entries[] = pReadInt32($this->mp4->readP);
			}
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			if ($this->boxInfo->type == "co64") {
				pWriteInt64($this->mp4->writeP, $this->entries[$i]);
			} else {
				pWriteInt32($this->mp4->writeP, $this->entries[$i]);
			}
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
	
	public function applyOffset($tmp_offset) {
		for ($i = 0; $i < $this->count; $i++) {
			$this->entries[$i] += $tmp_offset;
		}
	}
}
?>