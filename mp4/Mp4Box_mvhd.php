<?
class Mp4Box_mvhd extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $creationTime;
	public $modificationTime;
	public $timeScale;
	public $duration;
	public $rate;
	public $volume;
	public $reserved1;
	public $reserved2;
	public $reserved3;
	public $matrix = array();
	public $predefined = array();
	public $nextTrackId;
	
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
		
		$this->rate = pReadSI32($this->mp4->readP);
		$this->volume = pReadSI16($this->mp4->readP);
		$this->reserved1 = pReadInt16($this->mp4->readP);
		$this->reserved2 = pReadInt32($this->mp4->readP);
		$this->reserved3 = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < 9; $i++) {
			$this->matrix[] = pReadInt32($this->mp4->readP);
		}
		
		for ($i = 0; $i < 6; $i++) {
			$this->predefined[] = pReadInt32($this->mp4->readP);
		}
		
		$this->nextTrackId = pReadInt32($this->mp4->readP);
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
		
		pWriteSI32($this->mp4->writeP, $this->rate);
		pWriteSI16($this->mp4->writeP, $this->volume);
		pWriteInt16($this->mp4->writeP, $this->reserved1);
		pWriteInt32($this->mp4->writeP, $this->reserved2);
		pWriteInt32($this->mp4->writeP, $this->reserved3);
		
		for ($i = 0; $i < 9; $i++) {
			pWriteInt32($this->mp4->writeP, $this->matrix[$i]);
		}
		
		for ($i = 0; $i < 6; $i++) {
			pWriteInt32($this->mp4->writeP, $this->predefined[$i]);
		}
		
		pWriteInt32($this->mp4->writeP, $this->nextTrackId);
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: timeScale: " . $this->timeScale . ", duration: " . $this->duration . ", rate: " . $this->rate . ", volume: " . $this->volume . "]";
	}
}
?>