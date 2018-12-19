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
            shell_exec('rm -rf ./tmp composer.json');
            exit($doTarsServerName.$doTarsServantName.'.tars 文件不存在，请定义在项目名同级！');

        }
        $commond = './tmp/init.sh '.$doTarsIP.' '.$doTarsType.' '.$doTarsServerName.' '.$doTarsServantName.' '.$doTarsObjName;
        chmod('./tmp/init.sh', 0777);
        echo shell_exec($commond);
        shell_exec('rm -rf tmp');
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
    public static function servant()
    {
        $servantFile = self::mustRead('Tars文件名');
        $servantFile = self::servantName($servantFile);
        if(!self::validateFile('../tars/'.$servantFile)) {
            exit($servantFile.'文件不存在tars目录中！');
        }
        $servantPath = ucwords(self::mustRead('服务地址(Servant.Server.Obj)','validateServantPath'));
        $servantArr = explode('.',$servantPath);
        $commond = 'mkdir ../tars/'.$servantArr[0].$servantArr[1].' && touch ../tars/'.$servantArr[0].$servantArr[1].'/tars.proto.php && mv ../tars/'.$servantFile.' ../tars/'.$servantArr[0].$servantArr[1].'/';
        shell_exec($commond);
        $content = "<?php
return array(
    'appName' => '".$servantArr[0]."',
    'serverName' => '".$servantArr[1]."',
    'objName' => '".$servantArr[2]."',
    'withServant' => false, 
    'tarsFiles' => array(
        './".$servantArr[0].$servantArr[1]."/".$servantFile."',
    ),
    'dstPath' => '../src/servant',
    'namespacePrefix' => 'src\servant',
);
";
        if($f = file_put_contents('../tars/'.$servantArr[0].$servantArr[1].'/tars.proto.php', $content,FILE_APPEND)){// 这个函数支持版本(PHP 5)
            exit('写入tars.proto.php失败!');
        }
        $commond = 'cd ../tars && php ../src/vendor/phptars/tars2php/src/tars2php.php ./'.$servantArr[0].$servantArr[1].'/tars.proto.php';
    }
    public static function servantName($name)
    {
        if(strrchr($name,'.tars') == '.txt'){
            return $name;
        }else{
            return $name.'.tars';
        }
    }
    public static function validateServantPath($data)
    {
        $servantArr = explode('.',$data);
        if(count($servantArr) != 3) {
            return false;
        }
        if($servantArr[0] == '' || $servantArr[1] == '' || $servantArr[2] == '') {
            return false;
        }
        return true;
    }
}
DotarsInit::servant();
