<?
class Mp4Box_trak extends Mp4AbstractBox {
	public $chunksCount = 0;
	public $chunks = array();
	public $samplesCount = 0;
	public $samples = array();
	
	public $startSample;
	public $endSample;
	
	public function computeChunksAndSamples() {
		$stco = $this->box("mdia")->box("minf")->box("stbl")->box("stco");
		$stco_samples = 0;
		
		if (!$stco || $stco->count == 0) {
			return;
		}
		
		$this->chunksCount = $stco->count;
		for ($i = 0; $i < $this->chunksCount; $i++) {
			$this->chunks[$i] = new Mp4Box_trak_chunk($stco->entries[$i]);
		}
		
		//chunkmap
		$stsc = $this->box("mdia")->box("minf")->box("stbl")->box("stsc");
		$last = $this->chunksCount;
		$i = $stsc->count;
		while ($i > 0) {
			--$i;
			
			for ($j = $stsc->entries[$i]->chunk; $j < $last; $j++) {
				$this->chunks[$i]->id = $stsc->entries[$i]->id;
				$this->chunks[$i]->size = $stsc->entries[$i]->samples;
			}
			$last = $stsc->entries[$i]->chunk;
		}
		
		//points of chunks
		$stsz = $this->box("mdia")->box("minf")->box("stbl")->box("stsz");
		$sample_size = $stsz->sampleSize;
		$s = 0;
		for ($j = 0; $j < $this->chunksCount; $j++) {
			$this->chunks[$j]->sample = $s;
			$s += $this->chunks[$j]->size;
		}
		$stco_samples = $s;
		
		if ($sample_size == 0) {
			$this->samplesCount = $stsz->count;
		} else {
			$this->samplesCount = $s;
		}
		
		if ($sample_size == 0) {
			for ($i = 0; $i < $this->samplesCount; $i++) {
				$this->samples[$i] = new Mp4Box_trak_sample($stsz->sampleSizes[$i]);
			}
		} else {
			for ($i = 0; $i < $this->samplesCount; $i++) {
				$this->samples[$i] = new Mp4Box_trak_sample($sample_size);
			}
		}
		$this->samples[$i] = new Mp4Box_trak_sample($sample_size);
		
		//points
		$stts = $this->box("mdia")->box("minf")->box("stbl")->box("stts");
		$s = 0;
		$pts = 0;
		$entries = $stts->count;
		for ($j = 0; $j < $entries; $j++) {
			$sample_count = $stts->entries[$j]->sampleCount;
			$sample_duration = $stts->entries[$j]->sampleDuration;
			for ($i = 0; $i < $sample_count; $i++) {
				$this->samples[$s]->pts = $pts;
				++$s;
				$pts += $sample_duration;
			}
		}
		$this->samples[$s]->pts = $pts;
		
		//composition times
		$ctts = $this->box("mdia")->box("minf")->box("stbl")->box("ctts");
		if ($ctts) {
			$s = 0;
			for ($j = 0; $j < $ctts->count; $j++) {
				$sample_count = $ctts->entries[$j]->sampleCount;
				$sample_offset = $ctts->entries[$j]->sampleOffset;
				for ($i = 0; $i < $sample_count; $i++) {
					if ($s == $this->samplesCount) {
						if (MP4_DEBUG) {
							echo "WARNING: ctts count should be " . $this->samplesCount . " - " . sizeof($this->samples) ."<br>";
						}
					}
						
					$this->samples[$s]->cto = $sample_offset;
					$s++;
				}
				$this->samples[$s]->cto = $sample_offset;
			}
		}
		
		//sample offsets
		$s = 0;
		for ($j = 0; $j < $this->chunksCount; $j++) {
			$pos = $this->chunks[$j]->pos;
			for ($i = 0; $i < $this->chunks[$j]->size; $i++) {
				if ($s >= $this->samplesCount) {
					if (MP4_DEBUG) {
						echo "WARNING: stco count should be " . $this->samplesCount . " - " . sizeof($this->samples) ."<br>";
					}
				}
				
				$this->samples[$s]->pos = $pos;
				$pos += $this->samples[$s]->size;
				$s++;
			}
		}
		
		$stss = $this->box("mdia")->box("minf")->box("stbl")->box("stss");
		if ($stss) {
			for ($i = 0; $i < $stss->count; $i++) {
				$s = $stss->sampleNumbers[$i] - 1;
				$this->samples[$s]->isSS = true;
				$this->samples[$s]->isSmoothSS = true;
			}
		} else {
			for ($i = 0; $i < $this->samplesCount; $i++) {
				$this->samples[$i]->isSS = true;
			}
		}
		$this->samples[$this->samplesCount]->isSS = true;
		$this->samples[$this->samplesCount]->isSmoothSS = true;
	}
}

class Mp4Box_trak_chunk {
	public $pos;
	public $id;
	public $size;
	public $sample;
	
	public function __construct($tmp_pos) {
		$this->pos = $tmp_pos;
	}
}

class Mp4Box_trak_sample {
	public $size;
	public $pts;
	public $cto;
	public $isSS;
	public $isSmoothSS;
	public $pos;
	
	public function __construct($tmp_size) {
		$this->size = $tmp_size;
	}
}
?>