<?
//File functions
//.read
function pReadChar(&$tmp_p) {
	return ord(fread($tmp_p, 1));
}

function pReadInt8(&$tmp_p) {
	return ord(fread($tmp_p, 1));
}

function pReadInt16(&$tmp_p) {
	$data = fread($tmp_p, 2);
	return (ord($data{0}) << 8) + ord($data{1});
}

function pReadInt24(&$tmp_p) {
	$data = fread($tmp_p, 3);
	return (ord($data{0}) << 16) + (ord($data{1}) << 8) + ord($data{2});
}

function pReadInt32(&$tmp_p) {
	$data = fread($tmp_p, 4);
	return (ord($data{0}) << 24) + (ord($data{1}) << 16) + (ord($data{2}) << 8) + ord($data{3});
}

function pReadInt64(&$tmp_p) {
	return (pReadInt32($tmp_p) << 32) + pReadInt32($tmp_p);
}

function pReadSI16(&$tmp_p) {
	return pReadInt16($tmp_p) >> 8;
}

function pReadSI32(&$tmp_p) {
	return pReadInt32($tmp_p) >> 16;
}

//.write
function pWriteInt8(&$tmp_p, $tmp_value) {
	fwrite($tmp_p, chr(($tmp_value) & 0xFF), 1);
}

function pWriteInt16(&$tmp_p, $tmp_value) {
	$value = chr(($tmp_value >> 8) & 0xFF);
	$value .= chr(($tmp_value) & 0xFF);
	fwrite($tmp_p, $value, 2);
}

function pWriteInt24(&$tmp_p, $tmp_value) {
	$value = chr(($tmp_value >> 16) & 0xFF);
	$value .= chr(($tmp_value >> 8) & 0xFF);
	$value .= chr(($tmp_value) & 0xFF);
	fwrite($tmp_p, $value, 3);
}

function pWriteInt32(&$tmp_p, $tmp_value) {
	$value = chr(($tmp_value >> 24) & 0xFF);
	$value .= chr(($tmp_value >> 16) & 0xFF);
	$value .= chr(($tmp_value >> 8) & 0xFF);
	$value .= chr(($tmp_value) & 0xFF);
	fwrite($tmp_p, $value, 4);
}

function pWriteInt64(&$tmp_p, $tmp_value) {
	$value = chr(($tmp_value >> 56) & 0xFF);
	$value .= chr(($tmp_value >> 48) & 0xFF);
	$value .= chr(($tmp_value >> 40) & 0xFF);
	$value .= chr(($tmp_value >> 32) & 0xFF);
	$value .= chr(($tmp_value >> 24) & 0xFF);
	$value .= chr(($tmp_value >> 16) & 0xFF);
	$value .= chr(($tmp_value >> 8) & 0xFF);
	$value .= chr(($tmp_value) & 0xFF);
	fwrite($tmp_p, $value, 8);
}

function pWriteSI16(&$tmp_p, $tmp_value) {
	pWriteInt16($tmp_p, $tmp_value << 8);
}

function pWriteSI32(&$tmp_p, $tmp_value) {
	pWriteInt32($tmp_p, $tmp_value << 16);
}

//Pack
/*
function packSI16($tmp_value) {
	return pack("n", (int)($tmp_value * 0xFF));
}

function packSI32($tmp_value) {
	return pack("N", (int)($tmp_value * 0xFFFF));
}

function packEmpty($tmp_bytes) {
	return str_repeat(chr(0), $tmp_bytes);
}

function packInt8($tmp_value) {
	return chr($tmp_value & 0xFF);
}

function packInt16($tmp_value) {
	$r = chr(($tmp_value >> 8) & 0xFF);
	$r .= chr(($tmp_value >> 0) & 0xFF);
	
	return $r;
}

function packInt32($tmp_value) {
	$r = chr(($tmp_value >> 24) & 0xFF);
	$r .= chr(($tmp_value >> 16) & 0xFF);
	$r .= chr(($tmp_value >> 8) & 0xFF);
	$r .= chr(($tmp_value >> 0) & 0xFF);
	
	return $r;
}

function packInt64($tmp_value) {
	$r = packInt32($tmp_value >> 32);
	$r .= packInt32($tmp_value >> 0);
	
	return $r;
}

//String functions
function sReadInt8($tmp_string) {
	return ord($tmp_string{0});
}

function sReadInt32($tmp_string) {
	return (ord($tmp_string{0}) << 24) + (ord($tmp_string{1}) << 16) + (ord($tmp_string{2}) << 8) + ord($tmp_string{3});	
}

function sReadInt64($tmp_string) {
	return (ord($tmp_string{0}) << 56) + (ord($tmp_string{1}) << 48) + (ord($tmp_string{2}) << 40) + (ord($tmp_string{3}) << 36) + (ord($tmp_string{4}) << 24) + (ord($tmp_string{5}) << 16) + (ord($tmp_string{6}) << 8) + ord($tmp_string{7});
}*/
?>