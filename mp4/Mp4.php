<?
include("binary.php");
include("Mp4AbstractBox.php");
include("Mp4Box_ctts.php");
include("Mp4Box_stco.php");
include("Mp4Box_mdhd.php");
include("Mp4Box_mvhd.php");
include("Mp4Box_stsd.php");
include("Mp4Box_stss.php");
include("Mp4Box_stsz.php");
include("Mp4Box_tkhd.php");
include("Mp4Box_trak.php");
include("Mp4Box_stsc.php");
include("Mp4Box_stts.php");
include("Mp4Box_mdat.php");

if (!defined("MP4_DEBUG")) {
	define("MP4_DEBUG", false);
}

function print_r2($tmp_var) {
	echo "<pre>" . print_r($tmp_var, true) . "</pre>";
}

class Mp4 extends Mp4AbstractBox {
	public $readP;
	public $writeP;
	
	function __construct($tmp_filename_or_string = "", &$tmp_p = "") {
		parent::__construct($this);
		
		$this->boxInfo->type = "ROOT";
		
		if (is_resource($tmp_p)) {
			$this->_fromFile($tmp_p);
		} else if (strlen($tmp_filename_or_string) < 300) {
			$this->_fromFile(fopen($tmp_filename_or_string, "r"));
		} else {
			$this->_fromString($tmp_filename_or_string);
		}
	}
	
	private function _fromFile(&$tmp_p) {
		$this->readP = $tmp_p;
		fseek($this->readP, 0);
		
		//.read boxes
		if (MP4_DEBUG) { echo "------------------------ READ BOXES<br>"; };
		$this->read();
	}
	
	private function _fromString($tmp_string) {
		$p = tmpfile();
		fwrite($p, $tmp_string);
		$this->_fromFile($p);
	}
	
	public function trim($tmp_start_time = 0, $tmp_end_time = 0) {
		$this->writeP = tmpfile();
		
		//.get end/start positions
		if (MP4_DEBUG) { echo "------------------------ GET END/START POSITIONS<br>"; };
		$old_mdat_position = $this->box("mdat")->boxInfo->position;
		$moov = $this->box("moov");
		$moov_time_scale = $moov->box("mvhd")->timeScale;
		$mdat_size = $this->box("mdat")->boxInfo->size - $this->box("mdat")->boxInfo->headerLength;
		$start = (int)($tmp_start_time * $moov_time_scale + 0.5);
		$end = (int)($tmp_end_time * $moov_time_scale + 0.5);
		$traks = $moov->boxesOfType("trak");
		$i = 0;
		for ($pass = 0; $pass < 2; $pass++) {
			foreach ($traks as $trak) {
				$stbl = $trak->box("mdia")->box("minf")->box("stbl");
				
				if ($pass == 0 && !$stbl->box("stss")) { continue; }
				if ($pass == 1 && $stbl->box("stss")) { continue; }
				
				$trak->computeChunksAndSamples();
				if ($trak->samplesCount <= 1) {
					$moov->removeBox($trak);
					continue;
				}
			
				if (MP4_DEBUG) {
					echo "TRAK " . $i . ":<br>";
				}
				
				$trak_time_scale = $trak->box("mdia")->box("mdhd")->timeScale;
				
				if ($start == 0) {
					$trak->startSample = 0;
				} else {
					$stts = $stbl->box("stts");
					$start = (int)$stts->getSample($start * ($trak_time_scale / $moov_time_scale));
					
					if (MP4_DEBUG) {
						echo "start=" . $start . " (trak time)<br>";
						echo "start=" . ($stts->getTime($start) / $trak_time_scale) . " (seconds)<br>";
					}
					
					if ($stss = $stbl->box("stss")) {
						$start = $stss->getNearestKeyframe($start + 1) - 1;
					}
					
					if (MP4_DEBUG) {
						echo "start=" . $start . "(zero based keyframe)<br>";
					}
					
					$trak->startSample = $start;
					
					$start = (int)($stts->getTime($start) * ($moov_time_scale / $trak_time_scale));
					
					if (MP4_DEBUG) {
						echo "start=" . $start . " (moov time)<br>";
						echo "start=" . ($start / $moov_time_scale) . " (seconds)<br>";
					}
				}
				
				if ($end == 0) {
					$trak->endSample = $trak->samplesCount;
				} else {
					$stts = $stbl->box("stts");
					$end = (int)$stts->getSample($end * ($trak_time_scale / $moov_time_scale));
					
					if (MP4_DEBUG) {
						echo "end=" . $end . " (trak time)<br>";
						echo "end=" . ($stts->getTime($end) / $trak_time_scale) . " (seconds)<br>";
					}
					
					if ($end >= $trak->samplesCount) {
						$end = $trak->samplesCount;
					} else if ($stss = $stbl->box("stss")) {
						$end = $stss->getNearestKeyframe($end + 1) - 1;
					}
					
					if (MP4_DEBUG) {
						echo "end=" . $end . "(zero based keyframe)<br>";
					}
					
					$trak->endSample = $end;
					
					$end = (int)($stts->getTime($end) * ($moov_time_scale / $trak_time_scale));
					
					if (MP4_DEBUG) {
						echo "end=" . $end . " (moov time)<br>";
						echo "end=" . ($end / $moov_time_scale) . " (seconds)<br>";
					}
				}
			
				$i++;
			}
		}
		
		//.modify headers
		if (MP4_DEBUG) { echo "------------------------ MODIFY HEADERS<br>"; };
		$traks = $moov->boxesOfType("trak");
		$trak_i = 0;
		$skip_from_start = PHP_INT_MAX;
		$end_offset = 0;
		$moov_duration = 0;
		foreach ($traks as $trak) {
			$stbl = $trak->box("mdia")->box("minf")->box("stbl");
			
			if ($trak->samplesCount <= 1) { continue; }
			
			if (MP4_DEBUG) {
				echo "TRAK " . $trak_i++ . ":<br>";
			}
			
			$start = $trak->startSample;
			$end = $trak->endSample;
			
			//stts
			$stts = $stbl->box("stts");
			$entries = 0;
			$s = $start;
			while ($s < $end) {
				$sample_count = 1;
				$sample_duration = $trak->samples[$s + 1]->pts - $trak->samples[$s]->pts;
				while (++$s < $end) {
					if (($trak->samples[$s + 1]->pts - $trak->samples[$s]->pts) != $sample_duration) {
						break;
					}
					$sample_count++;
				}
				
				$stts->entries[$entries]->sampleCount = $sample_count;
				$stts->entries[$entries]->sampleDuration = $sample_duration;
				$entries++;
			}
			$stts->count = $entries;
			
			//ctts
			$ctts = $stbl->box("ctts");
			if ($ctts) {
				$entries = 0;
				$s = $start;
				
				while ($s < $end) {
					$sample_count = 1;
					$sample_offset = $trak->samples[$s]->cto;
					while (++$s < $end) {
						if ($trak->samples[$s]->cto != $sample_offset) {
							break;
						}
						$sample_count++;
					}
					$ctts->entries[$entries]->sampleCount = $sample_count;
					$ctts->entries[$entries]->sampleOffset = $sample_offset;
					$entries++;
				}
				
				if (MP4_DEBUG) {
					echo "ctts->count " . $ctts->count . " -> " . $entries . " end: " . $end . "<br>";
				}
				$ctts->count = $entries;
			}
			
			//chunkmap
			$stsc = $stbl->box("stsc");
			if ($stsc) {
				for ($i = 0; $i < $trak->chunksCount; $i++) {
					if (($trak->chunks[$i]->sample + $trak->chunks[$i]->size) > $start) {
						break;
					}
				}
				
				$stsc_entries = 0;
				$chunk_start = $i;
				if ($trak->chunksCount > 0) {
					$samples = $trak->chunks[$i]->sample + $trak->chunks[$i]->size - $start;
					$id = $trak->chunks[$i]->id;
					
					$stsc->entries[$stsc_entries]->chunk = 0;
					$stsc->entries[$stsc_entries]->samples = $samples;
					$stsc->entries[$stsc_entries]->id = $id;
					$stsc_entries++;
					
					if ($i < $trak->chunksCount) {
						for ($i += 1; $i < $trak->chunksCount; $i++) {
							$next_size = $trak->chunks[$i]->size;
							if (($trak->chunks[$i]->sample + $trak->chunks[$i]->size) > $end) {
								$next_size = $end - $trak->chunks[$i]->sample;
							}
							
							if ($next_size != $samples) {
								$samples = $next_size;
								$id = $trak->chunks[$i]->id;
								$stsc->entries[$stsc_entries]->chunk = $i - $chunk_start;
								$stsc->entries[$stsc_entries]->samples = $samples;
								$stsc->entries[$stsc_entries]->id = $id;
								$stsc_entries++;
							}
							
							if ($trak->chunks[$i]->sample + $next_size == $end) {
								break;
							}
						}
					}
				}
				
				$chunk_end = $i + 1;
				$stsc->count = $stsc_entries;
				
				$stco = $stbl->box("stco");
				$entries = 0;
				for ($i = $chunk_start; $i < $chunk_end; $i++) {
					$stco->entries[$entries] = $stco->entries[$i];
					$entries++;
				}
				$stco->count = $entries;
				
				$stco->entries[0] = $trak->samples[$start]->pos;
			}
			
			//process sync samples
			if ($stss = $stbl->box("stss")) {
				$entries = 0;
				
				for ($i = 0; $i < $stss->count; $i++) {
					if ($stss->sampleNumbers[$i] >= $start + 1) {
						break;
					}
				}
				
				$stss_start = $i;
				for (; $i < $stss->count; $i++) {
					$sync_table = $stss->sampleNumbers[$i];
					if ($sync_table >= $end + 1) {
						break;
					}
					$stss->sampleNumbers[$entries] = $sync_table - $start;
					$entries++;
				}
				$stss->count = $entries;
			}
			
			//process sample sizes
			$stsz = $stbl->box("stsz");
			if ($stsz) {
				if ($stsz->sampleSize == 0) {
					$entries = 0;
					for ($i = $start; $i < $end; $i++) {
						$stsz->sampleSizes[$entries] = $stsz->sampleSizes[$i];
						$entries++;
					}
				}
				if (MP4_DEBUG) {
					echo "stsz->count " . $stsz->count . " -> " . ($end - $start) . "<br>";
				}
				$stsz->count = $end - $start;
			}
			
			$skip = $trak->samples[$start]->pos - $trak->samples[0]->pos;
			if ($skip <= $skip_from_start) {
				$skip_from_start = $skip;
				if (MP4_DEBUG) {
					echo "Trak can skip " . $skip . "bytes<br>";
				}
			}
			
			if ($end < $trak->samplesCount) {
				$end_pos = $trak->samples[$end]->pos;
				if ($end_pos > $end_offset) {
					$end_offset = $end_pos;
					if (MP4_DEBUG) {
						echo "New endpos=" . $end_pos . "<br>";
						echo "Trak can skip " . ($old_mdat_position + $mdat_size - $end_offset) . " bytes at end<br>";
					}
				}
			}
			
			$stts = $stbl->box("stts");
			$trak_duration = $stts->getDuration();
			$mdhd = $trak->box("mdia")->box("mdhd");
			$trak_time_scale = $mdhd->timeScale;
			$duration = $trak_duration * ($moov_time_scale / $trak_time_scale);
			$mdhd->duration = $trak_duration;
			$trak->box("tkhd")->duration = $duration;
			if (MP4_DEBUG) {
				echo "trak: new_duration=" . $duration . "<br>";
			}
			
			if ($duration > $moov_duration) {
				$moov_duration = $duration;
			}
		}
		
		$moov = $this->box("moov");
		$moov->box("mvhd")->duration = $moov_duration;
		if (MP4_DEBUG) {
			echo "moov: new_duration=" . ($moov_duration / $moov_time_scale) . " seconds<br>";
		}
		
		//.write headers
		if ($this->box("ftyp")) {
			$this->box("ftyp")->write();
		}
		
		$this->box("moov")->write();
		
		if ($this->box("uuid")) {
			$this->box("uuid")->write();
		}
		
		//.adjust offsets
		$new_mdat_position = ftell($this->writeP);
		$offset = $new_mdat_position - $old_mdat_position;
		$offset -= $skip_from_start;
		
		if (MP4_DEBUG) {
			echo "mdat_start=" . $old_mdat_position . "<br>";
		}
		
		if ($end_offset > 0) {
			if (MP4_DEBUG) {
				echo "mdat_size=" . $mdat_size . " end_offset=" . $end_offset . "<br>";
			}
			$mdat_size = $end_offset - $old_mdat_position;
		}
		
		$old_mdat_position += $skip_from_start;
		$mdat_size -= $skip_from_start;
		
		$mdat = $this->box("mdat");
		$mdat->startOffset = $skip_from_start;
		$mdat->targetSize = $mdat_size;
		
		
		$moov = $this->box("moov");
		$traks = $moov->boxesOfType("trak");
		foreach ($traks as $trak) {
			$stbl = $trak->box("mdia")->box("minf")->box("stbl");
			$stco = $stbl->box("stco");
			
			$stco->applyOffset($offset);
			fseek($this->writeP, $stco->boxInfo->position);
			$stco->write();
		}
		
		fseek($this->writeP, $new_mdat_position);
		
		//.write mdat
		$this->box("mdat")->write();
		
		$this->dispose();
		
		$size = ftell($this->writeP);
		fseek($this->writeP, 0);
		$r = fread($this->writeP, $size);
		fclose($this->writeP);
		fclose($this->readP);
		
		return $r;
	}
}
?>