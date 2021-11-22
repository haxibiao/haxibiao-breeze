#!/bin/bash

echo "更新pwa模板 ... "
echo "需要前端打包dist文件夹！！！非重要版本更新，请先在当前项目，更新小版本，避免频繁更新breeze源码库"

base_path=$PWD 
if [ -z $1 ]; then
	echo "第一个参数必传，提供pwa的dist路径"
	exit
fi
dist_pasth=$1

echo "同步css...."
rm -rf $base_path/packages/haxibiao/breeze/public/css/app.*.css
rm -rf $base_path/packages/haxibiao/breeze/public/css/chunk*
rm -rf $base_path/packages/haxibiao/breeze/public/css/normalize.css

cp $dist_pasth/css/* $base_path/packages/haxibiao/breeze/public/css/

echo "同步js...."
rm -rf $base_path/packages/haxibiao/breeze/public/js/app.*.js
rm -rf $base_path/packages/haxibiao/breeze/public/js/app.*.map
rm -rf $base_path/packages/haxibiao/breeze/public/js/chunk*

rm -rf $base_path/packages/haxibiao/breeze/public/js/service-worker.js
rm -rf $base_path/packages/haxibiao/breeze/public/js/precache-manifest*

cp -f $dist_pasth/js/* $base_path/packages/haxibiao/breeze/public/js/
cp -f $dist_pasth/service-worker.js $base_path/packages/haxibiao/breeze/public/service-worker.js
cp $dist_pasth/precache-manifest* $base_path/packages/haxibiao/breeze/public/js/

echo "同步img...."
cp -rf $dist_pasth/img/* $base_path/packages/haxibiao/breeze/public/img

echo "同步html..."
cp -rf $dist_pasth/index.html $base_path/packages/haxibiao/breeze/resources/views/pwa/index.blade.php
