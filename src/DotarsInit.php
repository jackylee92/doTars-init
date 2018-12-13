<?php

namespace dotars;

class DotarsInit
{
	public static function index()
	{
		$str = self::read();
		self::addFile('./jiaohu.txt',$str);
	}
	public static function read($str = '请输入')
	{
		//提示输入
		fwrite(STDOUT, $str . ":");
		//获取用户输入数据
		$result = trim(fgets(STDIN));
		return trim($result);
	}
	public static function addFile($file_name,$content){
		$myfile = fopen($file_name, "a+") or die("Unable to open file!");
		fwrite($myfile, $content);
		fclose($myfile);
	}
}
