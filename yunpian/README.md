
# 使用说明

```
在composer.json 文件中加入
"yuanshuai/ys-yunpian" : "*"
然后命令行执行composer update --prefer-dist
```

### 使用


```
在配置文件中加入：
<?php
	'yunpian'=>[
		'class'=>'yuanshuai\yunpian\Yunpian',
		'config'=>[
			'appkey'=>appkey,
			'codeTemp'=>模板
		]
	]
?>
```



