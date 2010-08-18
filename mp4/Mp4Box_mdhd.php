<?
class Mp4Box_mdhd extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $creationTime;
	public $modificationTime;
	public $timeScale;
	public $duration;
	public $language;
	public $predefined;
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		
		if ($this->version == 0) {
			$this->creationTime = pReadInt32($this->mp4->readP);
			$this->modificationTime = pReadInt32($this->mp4->readP);
			$this->timeScale = pReadInt32($this->mp4->readP);
			$this->duration = pReadInt32($this->mp4->readP);
		} else {
			$this->creationTime = pReadInt64($this->mp4->readP);
			$this->modificationTime = pReadInt64($this->mp4->readP);
			$this->timeScale = pReadInt32($this->mp4->readP);
			$this->duration = pReadInt64($this->mp4->readP);
		}
		
		$this->language = pReadInt16($this->mp4->readP);
		$this->predefined = pReadInt16($this->mp4->readP);
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		
		if ($this->version == 0) {
			pWriteInt32($this->mp4->writeP, $this->creationTime);
			pWriteInt32($this->mp4->writeP, $this->modificationTime);
			pWriteInt32($this->mp4->writeP, $this->timeScale);
			pWriteInt32($this->mp4->writeP, $this->duration);
		} else {
			pWriteInt64($this->mp4->writeP, $this->creationTime);
			pWriteInt32($this->mp4->writeP, $this->modificationTime);
			pWriteInt64($this->mp4->writeP, $this->timeScale);
			pWriteInt64($this->mp4->writeP, $this->duration);
		}
		
		pWriteInt16($this->mp4->writeP, $this->language);
		pWriteInt16($this->mp4->writeP, $this->predefined);
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: timeScale: " . $this->timeScale . ", duration: " . $this->duration . "]";
	}
}
?>