<?
class Mp4Box_stts extends Mp4AbstractBox {
	public $version;
	public $flags;
	public $count;
	public $entries = array();
	
	protected function readContent() {
		$this->version = pReadInt8($this->mp4->readP);
		$this->flags = pReadInt24($this->mp4->readP);
		$this->count = pReadInt32($this->mp4->readP);
		
		for ($i = 0; $i < $this->count; $i++) {
			$this->entries[] = new Mp4Box_stts_entry(pReadInt32($this->mp4->readP), pReadInt32($this->mp4->readP));
		}
	}
	
	protected function writeContent() {
		pWriteInt8($this->mp4->writeP, $this->version);
		pWriteInt24($this->mp4->writeP, $this->flags);
		pWriteInt32($this->mp4->writeP, $this->count);
		
		for ($i = 0; $i < $this->count; $i++) {
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->sampleCount);
			pWriteInt32($this->mp4->writeP, $this->entries[$i]->sampleDuration);
		}
	}
	
	protected function toString() {
		return "[" . strtoupper($this->boxInfo->type) . " :: count: " . $this->count . "]";
	}
	
	public function getSample($tmp_time) {
		$ret = 0;
		$time_count = 0;
		for ($stts_index = 0; $stts_index < $this->count; $stts_index++) {
			$sample_count = $this->entries[$stts_index]->sampleCount;
			$sample_duration = $this->entries[$stts_index]->sampleDuration;
			if (($time_count + ($sample_duration * $sample_count)) >= $tmp_time) {
				$stts_count = ($tmp_time - $time_count + $sample_duration - 1) / $sample_duration;
				$time_count += $stts_count * $sample_duration;
				$ret += $stts_count;
				break;
			} else {
				$time_count += $sample_duration * $sample_count;
				$ret += $sample_count;
			}
		}
		
		return $ret;
	}
	
	public function getTime($tmp_sample) {
		$ret = 0;
		$stts_index = 0;
		$sample_count = 0;
		
		while ($stts_index < $this->count) {
			$table_sample_count = $this->entries[$stts_index]->sampleCount;
			$table_sample_duration = $this->entries[$stts_index]->sampleDuration;
			if ($sample_count + $table_sample_count > $tmp_sample) {
				$stts_count = $tmp_sample - $sample_count;
				$ret += $stts_count * $table_sample_duration;
				break;
			} else {
				$sample_count += $table_sample_count;
				$ret += $table_sample_count * $table_sample_duration;
				$stts_index++;
			}
		}
		
		return $ret;
	}
	
	public function getDuration() {
		$duration = 0;
		for ($i = 0; $i < $this->count; $i++) {
			$sample_count = $this->entries[$i]->sampleCount;
			$sample_duration = $this->entries[$i]->sampleDuration;
			$duration += $sample_duration * $sample_count;
		}
		
		return $duration;
	}
}

class Mp4Box_stts_entry {
	public $sampleCount;
	public $sampleDuration;
	
	public function __construct($tmp_sample_count, $tmp_sample_duration) {
		$this->sampleCount = $tmp_sample_count;
		$this->sampleDuration = $tmp_sample_duration;
	}
}
?>