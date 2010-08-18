<?
class Mp4Box_stsd extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $entries = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			$entry = new Mp4Box_stsd_entry(pReadInt32($this->mp4->readP) - 8, pReadInt32($this->mp4->readP));
			$entry->buf = fread($this->mp4->readP, $entry->len);
			$this->entries[] = $entry;
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->len + 8);
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->fourCC);
			fwrite($this->mp4->writeP, $this->entries[$i]->buf, $this->entries[$i]->len);
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
}

class Mp4Box_stsd_entry {
	public $len;
	public $fourCC;
	public $buf;
	
	public function __construct($tmp_len, $tmp_fourcc) {
		$this->len = $tmp_len;
		$this->fourCC = $tmp_fourcc;
	}
}
?>