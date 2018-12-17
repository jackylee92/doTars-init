<?php

namespace dotars;

class DotarsInit
{

    public static function index()
    {
        $doTarsIP = self::mustRead('主控IP','validateIp');
        $doTarsType = self::mustRead('类型(server\client)','validateType');
        $doTarsServerName = ucwords(self::mustRead('服务名'));
        $doTarsServantName = ucwords(self::mustRead('Servant名'));
        $doTarsObjName = ucwords(self::mustRead('Obj名'));
        if($doTarsType == 'server' && !self::validateFile('../'.$doTarsServerName.$doTarsServantName.'.tars')) {
            echo $doTarsServerName.$doTarsServantName.'.tars 文件不存在，请定义在项目名同级！';
            shell_exec('rm -rf ./tmp composer.json');
            exit();

        }
        $commond = './tmp/init.sh '.$doTarsIP.' '.$doTarsType.' '.$doTarsServerName.' '.$doTarsServantName.' '.$doTarsObjName;
        echo $commond;
        chmod('./tmp/init.sh', 0777);
        echo shell_exec($commond);
    }

    public static function mustRead($name='',$functionName='validateTrue')
    {
        $tips = '请输入'.$name;
        $data = self::read($tips);
        while($data == '' || !call_user_func([new self,$functionName],$data))
        {
            $tips = '请填写正确的'.$name;
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
    public static function validateIp($data)
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        if (preg_match($preg, $data) ){
            return true;
        }else{
            return false;
        }
    }
    public static function validateTrue($data)
    {
        return true;
    }

    public static function validateType($data)
    {
        if(in_array($data,['client','server'])) {
            return true;
        }else {
            return false;
        }
    }
    public static function validateFile($dir)
    {
        if(is_file($dir)) {
            return true;
        }else{
            return false;
        }
    }
}
