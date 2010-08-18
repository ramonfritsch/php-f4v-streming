<?
class Mp4Box_ctts extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $entries = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			$this->entries[] = new Mp4Box_ctts_entry(pReadInt32($this->mp4->readP), pReadInt32($this->mp4->readP));
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->sampleCount);
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->sampleOffset);
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
}

class Mp4Box_ctts_entry {
	public $sampleCount;
	public $sampleOffset;
	
	public function __construct($tmp_sample_count, $tmp_sample_offset) {
		$this->sampleCount = $tmp_sample_count;
		$this->sampleOffset = $tmp_sample_offset;
	}
}
?>