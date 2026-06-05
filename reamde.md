## 安装框架依赖包
```
composer install
```
## 安装框架依赖包 生成环境
```
composer install --no-dev
```
## 自动加载 开发环境
```
composer dump-autoload
```

### 优化自动加载 生成环境
```
composer dump-autoload --optimize
```

## 复制环境配置为.env
```
cp .env.example to .env and fill it
```


## 生成配置缓存文件

```
php cmd config "opt=cache"
```

### supervisor
启动supervisor
```
supervisord -c /etc/supervisord.conf
```
常用操作
```
supervisorctl stop program_name  # 停止某一个进程，program_name 为 [program:x] 里的 x
supervisorctl start program_name  # 启动某个进程
supervisorctl restart program_name  # 重启某个进程
supervisorctl stop groupworker:  # 结束所有属于名为 groupworker 这个分组的进程 (start，restart 同理)
supervisorctl stop groupworker:name1  # 结束 groupworker:name1 这个进程 (start，restart 同理)
supervisorctl stop all  # 停止全部进程，注：start、restartUnlinking stale socket /tmp/supervisor.sock、stop 都不会载入最新的配置文件
supervisorctl reload  # 载入最新的配置文件，停止原有进程并按新的配置启动、管理所有进程
supervisorctl update  # 根据最新的配置文件，启动新配置或有改动的进程，配置没有改动的进程不会受影响而重启
```



- 配置文件 /etc/supervisord.d/xxx.ini



- 启动出队列进程
```
supervisorctl update
supervisorctl start name
```


### 接口文档地址

- 排期表 https://docs.qq.com/sheet/DV1R2bWpsWUNjT3l0?tab=BB08J2
- 接口文档 https://docs.qq.com/doc/DV0xCWWhTakx5WGxk


### mysql
- root:k+c{K&JpM
- SET PASSWORD = PASSWORD('k+c{K&JpM');
- grant all on duanju_main.* to suser@'%' identified by 'S#c7uf5B';
- grant all on *.* to auser@'%' identified by 'S#c7uf5B';

### 服务器
- 106.54.221.118

### 后台
http://manage.zhilewangluo.com/
admin adm2023