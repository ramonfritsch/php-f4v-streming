Usage
-----

	<?
	include("mp4/Mp4.php");
	
	$mp4 = new Mp4("file.f4v");
	$start_time = 10; //seconds
	$end_time = 30; //seconds

	header("Content-type: video/f4v");
	echo $mp4->trim($start_time, $end_time);
	?>

TODO: Create some proxy.php file ready to use by some existing flash video player.
TODO: Make massive tests using different .f4v from differents encoders.
TODO: Optimize the memory usage

