<?php

namespace dotars;

class DotarsInit
{
    public $doTarsServerName;
    public $doTarsServantName;
    public $doTarsObjName;
    public $doTarsIP;
    public $doTarsType;

    public static function index()
    {
        $this->doTarsIP = self::mustRead('主控IP','validateIp');
        $this->doTarsType = self::mustRead('类型,服务端或者客户端(server\client)','validateType');
        $doTarsServerCompleteName = ucwords(self::mustRead('服务名(例如:User.Server.Obj)'));
        $doTarsServerCompleteNameArr = explode('.',$doTarsServerCompleteName);
        $this->doTarsServerName = ucwords($doTarsServerCompleteNameArr[0]);
        $this->doTarsServantName = ucwords($doTarsServerCompleteNameArr[1]);
        $this->doTarsObjName = ucwords($doTarsServerCompleteNameArr[2]);
        if($this->doTarsType == 'server' && !self::validateFile('../'.$this->doTarsServerName.$this->doTarsServantName.'.tars')) {
            shell_exec('rm -rf ./tmp composer.json');
            exit($this->doTarsServerName.$this->doTarsServantName.'.tars 文件不存在，请定义在项目名同级！');

        }
        mkdir('./src',0777,true);
        mkdir('./tars',0777,true);
        copy('./vender','./src/');

        if($this->doTarsType == 'server' ) {
            self::createServer();
        }
        if($this->doTarsType == 'client') {
            self::createClient();
        }
        //exec('rm -rf tmp');
    }
    public static function createServer()
    {
        copy('./tmp/src/server/*', './src/');
        copy('./tmp/tars/server.tars.proto.php', './tars/tars.proto.php');
        $ipNeedReplacePath = [
            './src/conf/ENVConf.php'
        ];
        foreach($ipNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'$doTarsIP', $this->doTarsIP);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        $serverNeedReplacePath = [
            './src/conf/ENVConf.php',
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($serverNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'$doTarsServerName', $this->doTarsServerName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        $servantNeedReplacePath = [
            './src/conf/ENVConf.php',
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($servantNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'$doTarsServantName', $this->doTarsServantName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        $objNeedReplacePath = [
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($objNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,' $doTarsObjName', $this->doTarsObjName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }
        copy('../'.$this->doTarsServerName.$this->doTarsServantName.'.tars', './tars/');
        self::tars2php();
    }

    public static function tars2php()
    {
        $commond = 'cd tars/ && php ../src/vendor/phptars/tars2php/src/tars2php.php ./tars.proto.php';
        exec($commond);

        $outPutDir= './src/servant/'.$this->doTarsServerName.'/'.$this->doTarsServantName.'/'.$this->doTarsObjName;
        if(!is_dir($outPutDir)) {
            throw new \Exception('tars文件生成代码失败',400);
        }
        $outPutDirLs = scandir($outPutDir);
        $outPutImpl = '';
        foreach($outPutDirLs as $item){
            if(strstr($item,'.php')){
                $outPutImpl = $outPutDir.'/'.$item;
            }
        }
        if(!is_file($outPutImpl)){
            throw new \Exception('接口文件获取失败',400);
        }
        $useCode = '';
        $funcCode = '';
        $funcStart = false;
        $fp = fopen($outPutImpl, 'r');
        while($line = fgets($fp, 1024) !== false) {
            if(strpos($line, 'use') === 0) {
                $useCode .= $line."\n";
            }
            if(strpos($lins, '}') !== false) {
                $funcStart = false;
            }
            if($funcStart) {
                $funcCode .= str_replace(';','{}',$line) ."\n";
            }
            if(strpos($lins, '{') !== false) {
                $funcStart = true;
            }
        }
        var_dump([$useCode,$funcCode]);
        $repRes = self::fileReplaceKeyword('./src/impl/IndexServantImpl.php',' $doTarsUseCode', $useCode);
        $repRes = self::fileReplaceKeyword('./src/impl/IndexServantImpl.php',' $doTarsFunctionBody', $funcCode);

    }

    public static function fileReplaceKeyword($path, $keyword, $content)
    {
        try {
            if(is_file($path)){
                return ['code' => 400, 'msg' => '替换['.$path.']时未找到指定文件!'];
            }
            $tmp = file_get_contents($path);
            $tmp = str_replace($key,$content,$tmp);
            if(!file_put_contents($path,$tmp)){
                throw new \Exception('更新内容写入失败', 401);
            }
            $code = true;
            $msg = 'ok';
        } catch (\Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        return ['code' => $code, 'msg' => $msg];
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
    public static function validateDir($dir)
    {
        if(is_dir($dir)) {
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
        if(!file_put_contents('../tars/'.$servantArr[0].$servantArr[1].'/tars.proto.php', $content,FILE_APPEND)){// 这个函数支持版本(PHP 5)
            exit('写入tars.proto.php失败!');
        }
        $commond = 'cd ../tars && php ../src/vendor/phptars/tars2php/src/tars2php.php ./'.$servantArr[0].$servantArr[1].'/tars.proto.php';
        shell_exec($commond);
        $servantPath = './servant/'.$servantArr[0].'/'.$servantArr[1].'/'.$servantArr[2];
	if(self::validateDir($servantPath)){
            echo $servantArr[0].'.'.$servantArr[1].'.'.$servantArr[2].' 服务代码生成成功!!!';
        }else {
            echo $servantArr[0].'.'.$servantArr[1].'.'.$servantArr[2].' 服务代码生成失败!!!';
        }
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
