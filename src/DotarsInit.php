<?php

namespace dotars;

class DotarsInit
{

	public static function index()
    {
        $param = [
            'doTarsIP' => self::mustRead('主控IP'),
            'doTarsType' => self::mustRead('类型(server\client)'),
            'doTarsServerName' => self::mustRead('服务名'),
            'doTarsServantName' => self::mustRead('Servant名'),
            'doTarsObjName' => self::mustRead('Obj名'),
        ];
		self::addFile('./param.txt',json_encode($param,JSON_UNESCAPED_UNICODE));
	}

    public static function mustRead($name='')
    {
        $tips = '请输入'.$name;
        $data = self::read($tips);
        while($data == '')
        {
            $tips = $name.'必填';
            $data = self::read($tips);
        }
        return $data;
    }

	public static function read($str = '请输入')
	{
		fwrite(STDOUT, $str . ":");
		$result = trim(fgets(STDIN));
		return trim($result);
	}

	public static function addFile($file_name,$content){
		$myfile = fopen($file_name, "a+") or die("Unable to open file!");
		fwrite($myfile, $content);
		fclose($myfile);
	}
}
