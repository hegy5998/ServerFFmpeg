<?php
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
	
	$pid = array();      //照片資料庫ID
	$second = array();   //照片秒數
	$effect = array();   //是否要淡入淡出
	$voice = array();    //照片使否加入語音
	$reversal = array(); //照片是否需要翻轉
	$music=0;            //是否有背景音樂
	$videosec=0;         //影片總秒數
	
	//ffmpeg路徑位置
	$ffmpeg = 'C:\inetpub\wwwroot\PhotoCollage\ffmpeg\bin\ffmpeg';
	//空音樂路徑
	$nullmusic = 'C:\inetpub\wwwroot\PhotoCollage\pictures\Kris\movie_tmp\null.mp3';
	//影片解碼格式
	$videoformat = '-c:v libx264';
	//解碼保存格式
	$videorawdata = '-pix_fmt yuv420p';
	//影片暫存路徑 中間檔
	$datatemptemp = 'C:\inetpub\wwwroot\PhotoCollage\temp\temp';
	//影片暫存路徑 準備被合併檔
	$datatempout = 'C:\inetpub\wwwroot\PhotoCollage\temp\out';
	//影片暫存路徑 合併檔
	$datatempmix = 'C:\inetpub\wwwroot\PhotoCollage\temp\mix.avi';
	//加入音樂指令
	$addmusic = '-filter_complex amix=inputs=2:duration=first:dropout_transition=1 -t';
	//影片輸出路徑
	$videofinalpath = 'C:\inetpub\wwwroot\PhotoCollage\final\final.mp4';
	//聲音、影像訊號 複製 指令
	$copyvideomusic = '-acodec copy -vcodec copy';
	
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
			$temp = $temp.' -i '.$PicPathrow['RecPath'].' -t '.$second[$i].' '.$videoformat;
			$videosec += $second[$i]; 
		}
		else
		{
			$temp = $temp.' -i '.$nullmusic.' -t '.$second[$i].' '.$videoformat;
			$temp = $temp.' -t '.$second[$i].' '.$videoformat;
			$videosec += $second[$i]; 
		}
		
		$temp = $temp.' '.$videorawdata;
		if($effect[$i]==1 && $i<4)
		{
			$temp = $temp.' -vf fade=in:0:25 -y '.$datatemptemp.$i.'.avi';
			$fadeout = $second[$i]*25-25;
			$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -vf fade=out:'.$fadeout.':25'.' '.$videoformat.' '.$videorawdata.' -y '.$datatempout.$i.'.avi';
		}
		else if($effect[$i]==1 && $i==$str_cut[0]-1)
		{
			$temp = $temp.' -vf fade=in:0:25 -y '.$datatemptemp.$i.'.avi';
			$fadeout = $second[$i]*25-50;
			$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -vf fade=out:'.$fadeout.':25'.' '.$videoformat.' '.$videorawdata.' -y '.$datatempout.$i.'.avi';
		}
		else if($effect[$i]==0 && $i<4)
		{
			$temp = $temp.' -y '.$datatempout.$i.'.avi';
		}
		$temp = $temp.' & ';
		
	}
	$temp = $temp.$ffmpeg.' -i "concat:';
	for($run = 0;$run < $str_cut[0];$run++)
	{
		if($run<$str_cut[0]  && $run+1!=$str_cut[0])
			$temp = $temp.$datatempout.$run.'.avi|';
		else if($run+1==$str_cut[0])
			$temp = $temp.$datatempout.$run.'.avi" '.$copyvideomusic;

	}
	if(($music = $str_cut[count($str_cut)-1])==1)
	{
		$temp = $temp.' -y '.$datatempmix.' & '.$ffmpeg.' -i '.$datatempmix.' -i C:\inetpub\wwwroot\PhotoCollage\temp\\'.$Pid[0].'.mp3 '.$addmusic.' '.$videosec.' -s 1080*720 -y  '.$videofinalpath;
	}
	else
	{
		$temp = $temp.' -s 1080*720 -y '.$videofinalpath;
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