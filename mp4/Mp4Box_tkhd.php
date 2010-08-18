<?
class Mp4Box_tkhd extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $creationTime;
	public $modificationTime;
	public $trackId;
	public $reserved1;
	public $duration;
	public $reserved2;
	public $reserved3;
	public $layer;
	public $predefined;
	public $volume;
	public $reserved4;
	public $matrix = array();
	public $width;
	public $height;
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		
		if ($this->version == 0) {
			$this->creationTime = pReadInt32($this->mp4->readP);
			$this->modificationTime = pReadInt32($this->mp4->readP);
			$this->trackId = pReadInt32($this->mp4->readP);
			$this->reserved1 = pReadInt32($this->mp4->readP);
			$this->duration = pReadInt32($this->mp4->readP);
		} else {
			$this->creationTime = pReadInt64($this->mp4->readP);
			$this->modificationTime = pReadInt64($this->mp4->readP);
			$this->trackId = pReadInt32($this->mp4->readP);
			$this->reserved1 = pReadInt32($this->mp4->readP);
			$this->duration = pReadInt64($this->mp4->readP);
		}
		
		$this->reserved2 = pReadInt32($this->mp4->readP);
		$this->reserved3 = pReadInt32($this->mp4->readP);
		$this->layer = pReadInt16($this->mp4->readP);
		$this->predefined = pReadInt16($this->mp4->readP);
		$this->volume = pReadSI16($this->mp4->readP);
		$this->reserved4 = pReadInt16($this->mp4->readP);
		
		for ($i = 0; $i < 9; $i++) {
			$this->matrix[] = pReadInt32($this->mp4->readP);
		}
		
		$this->width = pReadSI32($this->mp4->readP);
		$this->height = pReadSI32($this->mp4->readP);
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		
		if ($this->version == 0) {
			pWriteInt32($this->mp4->writeP, $this->creationTime);
			pWriteInt32($this->mp4->writeP, $this->modificationTime);
			pWriteInt32($this->mp4->writeP, $this->trackId);
			pWriteInt32($this->mp4->writeP, $this->reserved1);
			pWriteInt32($this->mp4->writeP, $this->duration);
		} else {
			pWriteInt64($this->mp4->writeP, $this->creationTime);
			pWriteInt32($this->mp4->writeP, $this->modificationTime);
			pWriteInt32($this->mp4->writeP, $this->trackId);
			pWriteInt32($this->mp4->writeP, $this->reserved1);
			pWriteInt64($this->mp4->writeP, $this->duration);
		}
		
		pWriteInt32($this->mp4->writeP, $this->reserved2);
		pWriteInt32($this->mp4->writeP, $this->reserved3);
		pWriteInt16($this->mp4->writeP, $this->layer);
		pWriteInt16($this->mp4->writeP, $this->predefined);
		pWriteSI16($this->mp4->writeP, $this->volume);
		pWriteInt16($this->mp4->writeP, $this->reserved4);
		
		for ($i = 0; $i < 9; $i++) {
			pWriteInt32($this->mp4->writeP, $this->matrix[$i]);
		}
		
		pWriteSI32($this->mp4->writeP, $this->width);
		pWriteSI32($this->mp4->writeP, $this->height);
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: trackId: " . $this->trackId . ", duration: " . $this->duration . ", volume: " . $this->volume . ", layer: " . $this->layer . ", width: " . $this->width . ", height: " . $this->height . "]";
	}
}
?>