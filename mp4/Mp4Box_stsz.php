<?
class Mp4Box_stsz extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $sampleSize;
	public $count;
	public $sampleSizes = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->sampleSize = pReadInt32($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		if ($this->sampleSize == 0) {
			for ($i = 0; $i < $this->count; $i++) {
				$this->sampleSizes[] = pReadInt32($this->mp4->readP);
			}
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->sampleSize);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		if ($this->sampleSize == 0) {
			for ($i = 0; $i < $this->count; $i++) {
				pWriteInt32($this->mp4->writeP, $this->sampleSizes[$i]);
			}
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
}
?>