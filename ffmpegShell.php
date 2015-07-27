<?php

    //$temp=iconv("utf8", "big5",'temp'); //將資料夾名稱編碼為big5，utf8是我寫程式所用的編碼
    $path='C:\inetpub\wwwroot\PhotoCollage\temp'; //路徑，我習慣額外設定
    if(!file_exists($path))
	mkdir($path,'0777'); //建立資料夾!!!!


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
	//聲音取樣頻率
	$arformat = '-ar 44100';
	//影片大小
	$videosize = '-s 1280*720';
	//解碼保存格式
	$videorawdata = '-pix_fmt yuv420p';
	//影片暫存路徑 中間檔
	$datatemptemp = 'C:\inetpub\wwwroot\PhotoCollage\temp\temp';
	//影片暫存路徑 準備被合併檔
	$datatempout = 'C:\inetpub\wwwroot\PhotoCollage\temp\out';
	//影片暫存路徑 合併檔
	$datatempmix = 'C:\inetpub\wwwroot\PhotoCollage\temp\mix.avi';
	//加入音樂指令
	$addmusic = '-filter_complex amix=inputs=2:duration=first:dropout_transition=1';
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
		if($effect[$i]==1 && $i<$str_cut[0]-1)
		{
			$temp = $temp.' -i '.$nullmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -vf fade=in:0:25 -y '.$datatemptemp.$i.'.avi';
			$fadeout = ($second[$i]/1000)*25-25;
			if($voice[$i]==1 && $second[$i]!='')
			{
				$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi'.' -i '.$PicPathrow['RecPath'].' '.$addmusic.' -vf fade=out:'.$fadeout.':25'.' '.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$videorawdata.' '.$arformat.' -y '.$datatempout.$i.'.avi';
			}
			else
			{
				$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -i '.$nullmusic.' '.$addmusic.' -vf fade=out:'.$fadeout.':25'.' '.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$videorawdata.' '.$arformat.' -y '.$datatempout.$i.'.avi';
			}
			$videosec += $second[$i]/1000; 
		}
		else if($effect[$i]==1 && $i==$str_cut[0]-1)
		{
			$temp = $temp.' -i '.$nullmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -vf fade=in:0:25 -y '.$datatemptemp.$i.'.avi';
			$fadeout = ($second[$i]/1000)*25-50;
			if($voice[$i]==1 && $second[$i]!='')
			{
				$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi'.' -i '.$PicPathrow['RecPath'].' '.$addmusic.' -vf fade=out:'.$fadeout.':25'.' '.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$videorawdata.' '.$arformat.' -y '.$datatempout.$i.'.avi';
			}
			else
			{
				$temp = $temp.' & '.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -i '.$nullmusic.' '.$addmusic.' -vf fade=out:'.$fadeout.':25'.' '.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$videorawdata.' '.$arformat.' -y '.$datatempout.$i.'.avi';
			}
			$videosec += $second[$i]/1000; 
		}
		else if($effect[$i]==0)
		{
			if($voice[$i]==1 && $second[$i]!='')
			{
				$temp = $temp.' -i '.$nullmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -y '.$datatemptemp.$i.'.avi & ';
				$temp = $temp.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -i '.$PicPathrow['RecPath'].' '.$addmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -y '.$datatempout.$i.'.avi';
				$videosec += $second[$i]/1000; 
			}
			else
			{
				$temp = $temp.' -i '.$nullmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -y '.$datatemptemp.$i.'.avi & ';
				$temp =$temp.$ffmpeg.' -i '.$datatemptemp.$i.'.avi -i '.$nullmusic.' '.$addmusic.' -t '.msToTime($second[$i]).' '.$videosize.' '.$videoformat.' '.$arformat.' '.$videorawdata.' -y '.$datatempout.$i.'.avi';
				$videosec += $second[$i]/1000; 
			}
		}
		$temp = $temp.' & ';
		
	}
	if(($music = $str_cut[count($str_cut)-1])==1)
	{
		$temp = $temp.$ffmpeg.' -i C:\inetpub\wwwroot\PhotoCollage\temp\\'.$pid[0].'.mp3 -af afade=t=out:st='.($videosec-3).':d=3 -y '.$datatempout.'.mp3 & ';
	}
	$temp = $temp.$ffmpeg.' -i "concat:';
	for($run = 0;$run < $str_cut[0];$run++)
	{
		if($run<$str_cut[0]  && $run+1!=$str_cut[0])
			$temp = $temp.$datatempout.$run.'.avi|';
		else if($run+1==$str_cut[0])
			$temp = $temp.$datatempout.$run.'.avi" '.$copyvideomusic.' '.$arformat;

	}
	if(($music = $str_cut[count($str_cut)-1])==1)
	{
		$temp = $temp.' -y '.$datatempmix.' & '.$ffmpeg.' -i '.$datatempmix.' -i '.$datatempout.'.mp3 '.$addmusic.' -t '.$videosec.' '.$arformat.' '.$videorawdata.' '.$videosize.' -y  '.$videofinalpath;
	}
	else
	{
		$temp = $temp.' '.$videorawdata.' '.$videosize.' -y '.$videofinalpath;
	}
	
	$fap = fopen('C:\inetpub\wwwroot\PhotoCollage\temp\output1.txt','w');
	fputs($fap,$str);
	$fp = fopen('C:\inetpub\wwwroot\PhotoCollage\temp\output.txt','w');
	fputs($fp,$temp);
	
	
	$last = system($temp,$return_var);

	echo "finish!!";
	}
	function msToTime($ms) {
		$seconds = intval($ms / 1000);
		$ms = $ms % 1000;
		$str = ":" . sprintf("%02d", $seconds % 60) . '.' . $ms;  
		$minutes = intval($seconds / 60);  
		$str = ":" . sprintf("%02d", $minutes % 60) . $str;  
		$hours = intval($minutes / 60);  
		$str = $hours . $str; 
		return $str;  
	}
        /*$log = 'C:\inetpub\wwwroot\PhotoCollage\temp';
SureRemoveDir($log , true); // 第二個參數: true 連 temp 目錄也刪除

function SureRemoveDir($dir, $DeleteMe) {

if(!$dh = @opendir($dir)) return;

while (false !== ($obj = readdir($dh))) {

if($obj=='.' || $obj=='..') continue;

if (!@unlink($dir.'/'.$obj)) SureRemoveDir($dir.'/'.$obj, true);

}

if ($DeleteMe){

closedir($dh);

@rmdir($dir);

}

}*/
?>
//語音問題解法：先將每個照片都置入空音軌在加入語音，這樣語音如果長度比照片時間短，語音最後面會補上空音軌不至於在合併的時候產生語音被提前播放或是其他錯誤等問題。
//每行指令都要加入影片大小的設定以及音訊頻率的設定，才不會在最後合併的時候出現格式不符合的問題。
//沒有語音的也要合併兩次音軌並使用$addmusic這個指令，不然會出錯。