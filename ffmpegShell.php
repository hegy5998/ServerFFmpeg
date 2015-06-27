<?php
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> parent of eccab33... Revert "有阿"
//有阿有阿
=======
//阿囉哈(產生衝突啦)
>>>>>>> origin/master
<<<<<<< HEAD
=======
//有嗎有嗎
>>>>>>> parent of 0c74a5d... 有阿
=======
//有阿有阿
>>>>>>> origin/master
=======
>>>>>>> parent of eccab33... Revert "有阿"
	if(file_exists('C:\inetpub\wwwroot\PhotoCollage\final\final1.mp4'))
	{
		echo "File is exist!";
	}
	else
	{
	$dbhost = 'localhost';   	//IP
	$dbuser = 'root';   		//DB User
	$dbpass = '121443651';   	//DB Password
	$dbname = 'photocollage';	//DB name
	
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');

	mysql_select_db($dbname, $conn);
	
	$str = @$_POST['commend'];
	//$str = '5 2 5 0 0 1 4 7 0 0 1 1 6 0 1 0 3 3 0 0 0 5 8 0 1 1 1';
	$str_cut=explode(" ",$str);
	echo $str."\n";
	
	$pid = array();
	$second = array();
	$effect = array();
	$voice = array();
	$reversal = array();
	$music=0;
	$videosec=0;
	
	$ffmpeg = 'C:\inetpub\wwwroot\PhotoCollage\ffmpeg\bin\ffmpeg';

	for($i=0,$temp = count($str_cut)-2,$nextstr = 5;$temp>1;$temp -= 5,$i++)
	{	
		if($i==0)
		{
			$pid[$i] = $str_cut[1];
			$second[$i] = $str_cut[2];
			$reversal[$i] = $str_cut[3];
			$effect[$i] = $str_cut[4];
			$voice[$i] = $str_cut[5]; 
		}
		else
		{
			$pid[$i] = $str_cut[1+$nextstr];
			$second[$i] = $str_cut[2+$nextstr];
			$reversal[$i] = $str_cut[3+$nextstr];
			$effect[$i] = $str_cut[4+$nextstr];
			$voice[$i] = $str_cut[5+$nextstr]; 
			$nextstr += 5;
		}echo $pid[$i]." ".$second[$i]." ".$reversal[$i]." ".$effect[$i]." ".$voice[$i]."\n";

	}

	//$ffmpeg = 'C:\inetpub\wwwroot\PhotoCollage\ffmpeg\bin\ffmpeg';
	$title = ' -loop 1 -i';
	$temp ='';
	for($i=0,$x=1;$i<$str_cut[0];$i++,$x++)
	{
		if($pid[$i]!='')
		{
			$Picpath = mysql_query("SELECT * FROM photo WHERE Pid=$pid[$i]");
			$PicPathrow = mysql_fetch_array($Picpath);
			$temp = $temp.$ffmpeg.$title." ".$PicPathrow['Ppath'];
		}
		if($voice[$i]==1 && $second[$i]!='')
		{
			$temp = $temp.' -i '.$PicPathrow['RecPath'].' -t '.$second[$i].' -c:v libx264';
			$videosec += $second[$i]; 
		}
		else
		{
			$temp = $temp.' -i C:\inetpub\wwwroot\PhotoCollage\pictures\Kris\movie_tmp\null.mp3 -t '.$second[$i].' -c:v libx264';
			$temp = $temp.' -t '.$second[$i].'  -c:v libx264';
			$videosec += $second[$i]; 
		}
		
		$temp = $temp.' -pix_fmt yuv420p';
		if($effect[$i]==1 && $i<4)
		{
			$temp = $temp.' -vf fade=in:0:25 -y C:\inetpub\wwwroot\PhotoCollage\temp\temp'.$i.'.avi';
			$fadeout = $second[$i]*25-25;
			$temp = $temp.' & '.$ffmpeg.' -i C:\inetpub\wwwroot\PhotoCollage\temp\temp'.$i.'.avi -vf fade=out:'.$fadeout.':25 -c:v libx264 -pix_fmt yuv420p -y C:\inetpub\wwwroot\PhotoCollage\temp\out'.$i.'.avi';
		}
		else if($effect[$i]==1 && $i==$str_cut[0]-1)
		{
			$temp = $temp.' -vf fade=in:0:25 -y C:\inetpub\wwwroot\PhotoCollage\temp\temp'.$i.'.avi';
			$fadeout = $second[$i]*25-50;
			$temp = $temp.' & '.$ffmpeg.' -i C:\inetpub\wwwroot\PhotoCollage\temp\temp'.$i.'.avi -vf fade=out:'.$fadeout.':25 -c:v libx264 -pix_fmt yuv420p -y C:\inetpub\wwwroot\PhotoCollage\temp\out'.$i.'.avi';
		}
		else if($effect[$i]==0 && $i<4)
		{
			$temp = $temp.' -y C:\inetpub\wwwroot\PhotoCollage\temp\out'.$i.'.avi';
		}
		$temp = $temp.' & ';
		
	}
	$temp = $temp.$ffmpeg.' -i "concat:';
	for($run = 0;$run < $str_cut[0];$run++)
	{
		if($run<$str_cut[0]  && $run+1!=$str_cut[0])
			$temp = $temp.'C:\inetpub\wwwroot\PhotoCollage\temp\out'.$run.'.avi|';
		else if($run+1==$str_cut[0])
			$temp = $temp.'C:\inetpub\wwwroot\PhotoCollage\temp\out'.$run.'.avi" -acodec copy -vcodec copy';

	}
	if(($music = $str_cut[count($str_cut)-1])==1)
	{
		$temp = $temp.' -y C:\inetpub\wwwroot\PhotoCollage\temp\mix.avi & '.$ffmpeg.' -i C:\inetpub\wwwroot\PhotoCollage\temp\mix.avi -i C:\inetpub\wwwroot\PhotoCollage\temp\\'.$Pid[0].'.mp3 -filter_complex amix=inputs=2:duration=first:dropout_transition=1 -t '.$videosec.' -s 1080*720 -y  C:\inetpub\wwwroot\PhotoCollage\final\final.mp4';
	}
	else
	{
		$temp = $temp.' -s 1080*720 -y C:\inetpub\wwwroot\PhotoCollage\final\final.mp4';
	}
	
	//echo $temp;
	$fap = fopen('C:\inetpub\wwwroot\PhotoCollage\temp\output1.txt', 'w');
	fputs($fap,$str);
	$fp = fopen('C:\inetpub\wwwroot\PhotoCollage\temp\output.txt', 'w');
	fputs($fp,$temp);
	
	
	$last = system($temp,$return_var);

	echo "finish!!";
	}
?>