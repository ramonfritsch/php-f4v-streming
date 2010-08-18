<?
class Mp4Box_stss extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $sampleNumbers = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			$this->sampleNumbers[] = pReadInt32($this->mp4->readP);
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			pWriteInt32($this->mp4->writeP, $this->sampleNumbers[$i]);
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
	
	public function getNearestKeyFrame($tmp_sample) {
		for ($i = 0; $i < $this->count; $i++) {
			if ($this->sampleNumbers[$i] > $tmp_sample) {
				break;
			}
		}
		
		if ($tmp_sample == $this->sampleNumbers[$i]) {
			return $tmp_sample;
		} else {
			return $this->sampleNumbers[max(0, $i - 1)];
		}
	}
}
?>