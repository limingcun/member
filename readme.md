## 概览步骤

Web服务器环境分两部分：PHP环境和NodeJs环境。

PHP环境就是服务端的后台代码，NodeJS环境用于Admin管理后台。

要实现运行，总的来说分如下步骤：

- PHP运行需要.env文件，用于配置数据库、参数等基础设置；
- composer安装PHP库，用于PHP服务器的依赖运行；
- npm安装NodeJS包，用于Admin管理后台的依赖运行；

## 运行环境

- Nginx 1.8+
- PHP 7.1+
- Mysql 5.7+
- Redis 3.0+


## 环境部署


1. 克隆源代码

```shell
> git clone https://git.coding.net/beemind/istore-usercenter.git
```
2. 创建数据库

统一使用utf8mb4

```mysql
CREATE DATABASE istore_crm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
```
3. 配置env，填写相关配置

```shell
cp .env.example .env
composer install
php artisan key:generate
php artisan jwt:secret
```
填写下面相关的配置
```
# Wechat
WECHAT_WORK_CONTACTS_AGENT_ID=
WECHAT_WORK_AGENT_CONTACTS_SECRET=
WECHAT_WORK_CROP_ID=

# 微信 小程序
WECHAT_MINI_PROGRAM_APPID=
WECHAT_MINI_PROGRAM_SECRET=
WECHAT_MINI_PROGRAM_TOKEN=
WECHAT_MINI_PROGRAM_AES_KEY=

# 微信 支付
WECHAT_PAYMENT_SANDBOX=false
WECHAT_PAYMENT_APPID=
WECHAT_PAYMENT_MCH_ID=
WECHAT_PAYMENT_KEY=
WECHAT_PAYMENT_CERT_PATH=
WECHAT_PAYMENT_KEY_PATH=

# 高德地图key
GAODE_APP_KEY=

# 七牛
QINIU_URL=

# 餐道
CANDAO_URL=
CANDAO_ACCESS_KEY=
CANDAO_SECRET=

# 微信小程序模板
## 下单成功
WECHAT_TEMPLATE_PAID_ID=
## 取餐提醒
WECHAT_TEMPLATE_PROCESSED_ID=
```


4. migrate

```shell
php artisan migrate
```
5. 配置 cron

/etc/cron.d 增加 istore
```
* * * * * root /usr/bin/php /var/www/istore-web/artisan schedule:run >> /dev/null 2>&1
```
重启cron


6. 前端环境

 ```shell
 ##克隆前端项目代码
 > git clone https://git.coding.net/beemind/istore-crm-web-v1.0.git
 npm --registry https://registry.npm.taobao.org install
 npm install

 // OR
 cnpm install
 ```
 
##在前端项目下创建env.js文件，填写如下代码
module.exports = {
  app_port: 8080,
  is_server: false,
  host: 'http://localhost:8080',
  app_url: 'http://www.istoremember.com',  ##这里填写后台跨越域名
  app_lang: 'zh-CN'
}

执行
 ```shell
 npm run dev
 ```
```

