<?
class Mp4Box_stsc extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $entries = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			$this->entries[] = new Mp4Box_stsc_entry(pReadInt32($this->mp4->readP) - 1, pReadInt32($this->mp4->readP), pReadInt32($this->mp4->readP));
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->chunk + 1);
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->samples);
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->id);
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
}

class Mp4Box_stsc_entry {
	public $chunk;
	public $samples;
	public $id;
	
	public function __construct($tmp_chunk, $tmp_samples, $tmp_id) {
		$this->chunk = $tmp_chunk;
		$this->samples = $tmp_samples;
		$this->id = $tmp_id;
	}
}
?>