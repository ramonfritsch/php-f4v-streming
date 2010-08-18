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
?>