<?php

namespace dotars;

class DotarsInit
{
    public static function index()
    {
        try{
            $doTarsIP = self::mustRead('主控IP','validateIp');
            $doTarsType = self::mustRead('类型,服务端或者客户端(server\client)','validateType');
            $doTarsServerCompleteName = ucwords(self::mustRead('服务名(例如:User.Server.Obj)','validateServantPath'));

            $doTarsServerCompleteNameArr = explode('.',$doTarsServerCompleteName);
            $doTarsServerName = ucwords($doTarsServerCompleteNameArr[0]);
            $doTarsServantName = ucwords($doTarsServerCompleteNameArr[1]);
            $doTarsObjName = ucwords($doTarsServerCompleteNameArr[2]);
            if($doTarsType == 'server' && !self::validateFile('../'.$doTarsServerName.$doTarsServantName.'.tars')) {
                self::cleanTmp();
                exit($doTarsServerName.$doTarsServantName.'.tars 文件不存在，请定义在项目名同级！');

            }
            mkdir('./src',0777,true);
            mkdir('./tars',0777,true);
            self::copyDir('./vendor', './src/');

            if($doTarsType == 'server' ) {
                self::createServer($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName);
            }
            if($doTarsType == 'client') {
                self::createClient($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName);
            }
            self::cleanTmp();
            exit(' [Success] 欢迎使用doTars生成！！！');
        } catch(\Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            self::cleanTmp();
            exit(' [Error] doTars生成失败 [Code]'.$code.' [Msg]'.$msg.'！！！');
        }
    }
    public static function createClient($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName)
    {
        self::copyDir('./tmp/src/client/*','./src/');
        self::copyDir('./tmp/tars/client.tars.proto.php', './tars/tars.proto.php');
        // replace ip
        $ipNeedReplacePath = [
            './src/conf/ENVConf.php'
        ];
        foreach($ipNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsIP}', $doTarsIP);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsServerName
        $serverNeedReplacePath = [
            './src/conf/ENVConf.php',
            './tars/tars.proto.php',
        ];
        foreach($serverNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsServerName}', $doTarsServerName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsServantName
        $servantNeedReplacePath = [
            './src/conf/ENVConf.php',
            './tars/tars.proto.php',
        ];
        foreach($servantNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsServantName}', $doTarsServantName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsObjName
        $objNeedReplacePath = [
            './tars/tars.proto.php',
        ];
        foreach($objNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsObjName}', $doTarsObjName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }
    }
    public static function createServer($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName)
    {
        self::copyDir('./tmp/src/server/*','./src/');
        self::copyDir('./tmp/tars/server.tars.proto.php', './tars/tars.proto.php');
        // replace ip
        $ipNeedReplacePath = [
            './src/conf/ENVConf.php'
        ];
        foreach($ipNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsIP}', $doTarsIP);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsServerName
        $serverNeedReplacePath = [
            './src/conf/ENVConf.php',
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($serverNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsServerName}', $doTarsServerName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsServantName
        $servantNeedReplacePath = [
            './src/conf/ENVConf.php',
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($servantNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsServantName}', $doTarsServantName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }

        // replace doTarsObjName
        $objNeedReplacePath = [
            './src/impl/IndexServantImpl.php',
            './src/services.php',
            './tars/tars.proto.php',
        ];
        foreach($objNeedReplacePath as $item){
            $repRes = self::fileReplaceKeyword($item,'${doTarsObjName}', $doTarsObjName);
            if($repRes['code'] !== true) {
                throw new \Exception($repRes['msg'], $repRes['code']);
            }
        }
        self::copyDir('../'.$doTarsServerName.$doTarsServantName.'.tars', './tars/');
        self::tars2php($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName);
    }

    public static function tars2php($doTarsIP, $doTarsServerName, $doTarsServantName, $doTarsObjName)
    {
        $commond = 'cd tars/ && php ../src/vendor/phptars/tars2php/src/tars2php.php ./tars.proto.php';
        exec($commond);

        $outPutDir= './src/servant/'.$doTarsServerName.'/'.$doTarsServantName.'/'.$doTarsObjName;
        if(!is_dir($outPutDir)) {
            throw new \Exception('tars文件生成代码失败',400);
        }
        $outPutDirLs = scandir($outPutDir);
        $outPutImpl = '';
        $outPutImpl = '';
        foreach($outPutDirLs as $item){
            if(strstr($item,'.php')){
                $outPutImpl = str_replace('.php','',$item);
            }
        }
        $outPutImplDir = $outPutDir.'/'.$outPutImpl.'.php';
        if(!is_file($outPutImplDir)){
            throw new \Exception('接口文件获取失败',400);
        }
        $useCode = '';
        $funcCode = '';
        $funcStart = false;
        $fp = fopen($outPutImplDir, 'r');
        while(($line = fgets($fp, 1024)) !== false) {
            if(strpos($line, 'use') === 0) {
                $useCode .= $line;
            }
            if(strpos($line, '}') !== false) {
                $funcStart = false;
            }
            if($funcStart) {
                $funcCode .= str_replace(';','{}',$line);
            }
            if(strpos($line, '{') !== false) {
                $funcStart = true;
            }
        }
        $repRes = self::fileReplaceKeyword('./src/impl/IndexServantImpl.php','${doTarsUseCode}', $useCode);
        $repRes = self::fileReplaceKeyword('./src/impl/IndexServantImpl.php','${doTarsFunctionBody}', $funcCode);
        $repRes = self::fileReplaceKeyword('./src/impl/IndexServantImpl.php','${doTarsServantImplName}', $outPutImpl);
        $repRes = self::fileReplaceKeyword('./src/services.php','${doTarsServantImplName}', $outPutImpl);

    }

    public static function copyDir($dir,$toDir)
    {
        exec('cp -rf '.$dir.' '.$toDir);
    }

    public static function cleanTmp()
    {
        exec('rm -rf tmp composer*');
    }

    public static function fileReplaceKeyword($path, $keyword, $content)
    {
        try {
            if(!is_file($path)){
                return ['code' => 400, 'msg' => '替换['.$path.']时未找到指定文件!'];
            }
            $tmp = file_get_contents($path);
            $tmp = str_replace($keyword,$content,$tmp);
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
            echo 1;
            $tips = '请填写正确的'.$name;
            echo 2;
            $data = self::read($tips);
            echo 3;
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
DotarsInit::index();
