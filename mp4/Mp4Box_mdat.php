<?
class Mp4Box_mdat extends Mp4AbstractBox {
	public $startOffset = 0;
	public $targetSize = 0;
		
	protected function writeContent() {
		if ($this->startOffset > 0 || $this->targetSize > 0) {
			$this->_content = substr($this->_content, $this->startOffset, $this->targetSize);
		}
		fwrite($this->mp4->writeP, $this->_content, strlen($this->_content));
	}
}

?>