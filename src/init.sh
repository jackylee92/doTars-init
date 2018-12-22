#!/bin/sh

del_file()
{
  echo "* * * 您终止了操作，项目生成失败 * * *"
  rm -rf tmp
  exit 1
}
trap del_file 2
doTarsIP=$1
doTarsType=$2
doTarsServerName=$3
doTarsServantName=$4
doTarsObjName=$5

# 首字母转大写
doTarsServerName=`echo "$doTarsServerName" | awk '{for (i=1;i<=NF;i++)printf toupper(substr($i,0,1))substr($i,2,length($i))" ";printf "\n"}' `
doTarsServerName=`echo "$doTarsServerName" | sed 's/ //g'`
doTarsServantName=`echo "$doTarsServantName" | awk '{for (i=1;i<=NF;i++)printf toupper(substr($i,0,1))substr($i,2,length($i))" ";printf "\n"}' `
doTarsServantName=`echo "$doTarsServantName" | sed 's/ //g'`
doTarsObjName=`echo "$doTarsObjName" | awk '{for (i=1;i<=NF;i++)printf toupper(substr($i,0,1))substr($i,2,length($i))" ";printf "\n"}' `
doTarsObjName=`echo "$doTarsObjName" | sed 's/ //g'`

echo '正在初始化'${doTarsType}'框架'
echo '主控IP : '${doTarsIP}
echo '服务名称 : '${doTarsServerName}
echo 'Servant名称 : '${doTarsServantName}
echo 'Obj名称 : '${doTarsObjName}

mkdir src
mv vendor src

if [ ${doTarsType} == "server" ];then
    cd tmp/src/server
    mv ./* ../../../src/
    cd ../../
    rm -rf tars/client.tars.proto.php
    mv tars/server.tars.proto.php tars/tars.proto.php
    mv tars ../
fi

if [ ${doTarsType} == "client" ];then
    cd tmp/src/client
    mv ./* ../../../src/
    cd ../../
    rm -rf tars/server.tars.proto.php
    mv tars/client.tars.proto.php tars/tars.proto.php
    mv tars ../
fi

cd ../


sed -i "s/\${doTarsServerName}/${doTarsServerName}/g" `grep '\${doTarsServerName}' -rl ./tars/*`
sed -i "s/\${doTarsServantName}/${doTarsServantName}/g" `grep '\${doTarsServantName}' -rl ./tars/*`
sed -i "s/\${doTarsObjName}/${doTarsObjName}/g" `grep '\${doTarsObjName}' -rl ./tars/*`


sed -i "s/\${doTarsIP}/${doTarsIP}/g" `grep '\${doTarsIP}' -rl ./src/*`
sed -i "s/\${doTarsServerName}/${doTarsServerName}/g" `grep '\${doTarsServerName}' -rl ./src/*`
sed -i "s/\${doTarsServantName}/${doTarsServantName}/g" `grep '\${doTarsServantName}' -rl ./src/*`
sed -i "s/\${doTarsObjName}/${doTarsObjName}/g" `grep '\${doTarsObjName}' -rl ./src/*`

# -------------client over ----------------

if [ ${doTarsType} == "server" ];then
    tarsFileName=${doTarsServerName}${doTarsServantName}.tars
    echo "正在copy[${tarsFileName}]文件。。。"
    mv ../${tarsFileName} tars/
    cd tars
    php ../src/vendor/phptars/tars2php/src/tars2php.php ./tars.proto.php

    cd ../src/servant/${doTarsServerName}/${doTarsServantName}/${doTarsObjName}
    baseImplaceClassFileName=`ls *php`
    if [ ! -n "${baseImplaceClassFileName}" ];then
        echo "[ ERROR ] : tars文件错误，未生成接口文件，请删除项目重试！"
        exit 0
    fi

    doTarsFunctionBody=$(cat ${baseImplaceClassFileName} | sed 's#\/#\\\/#g' | sed 's#\$#\$#g' | sed 's#\*#\\\*#g' | sed 's#\&#\\\&#g' | sed -n '/{/,/}/p' | grep -Ev '(^interface|}$)' | cut -f 1,2 |  sed  "s/;/{}/g" | sed -r 's@$@ '\\\\n[:space:][:space:][:space:][:space:]'@')
    doTarsFunctionUse=$(grep -E 'use' ${baseImplaceClassFileName} | sed 's#\/#\\\/#g' | sed 's#\$#\$#g' | sed 's#\*#\\\*#g' | sed 's#\\#\\\\#g' | sed 's#\&#\\\&#g' | sed 's# #[:space:]#g' | sed -r 's@$@ '\\\\n'@')

    baseImplaceClassFileName=`echo $baseImplaceClassFileName | sed 's/.php//g'`

    cd ../../../../..

    sed -i "s/\${doTarsServantImplName}/${baseImplaceClassFileName}/g"  `grep '\${doTarsServantImplName}' -rl ./src/*`
    
    sed -i "s#\${doTarsFunctionBody}#$(echo ${doTarsFunctionBody})#g" ./src/impl/IndexServantImpl.php
    sed -i "/implements/i\\$(echo $doTarsFunctionUse)" ./src/impl/IndexServantImpl.php
    sed -i "s/\[:space:\]/ /g" ./src/impl/IndexServantImpl.php
fi

echo "[ Success ] : 创建成功！！！！"

mv composer.* src/
