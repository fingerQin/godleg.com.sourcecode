[TOC]

## Yaf-Server API 1.0.0 接口文档 ##

### 1 交互方式 ###

#### 1.1 概述 ####

本文档主要针对 `Yaf-Server` 基建项目多端使用参考。

本文档使用 `markdown` 标记语言编写。推荐大家使用 `typora` 这款软件阅读或编写。

工具下载地址: https://www.typora.io/

#### 1.2 API 调用地址 ####

1. 开发环境 `API` 调用地址：
2. 预发布环境 `API` 调用地址：
3. 公测环境 `API` 调用地址：
4. 正式环境 `API` 调用地址：

#### 1.3 交互协议 ####

##### 1.3.1 传递参数 #####

1. 所有接口全部采用 `POST` 方式提交参数。不允许使用 `GET` 方式。
2. 接口请求的参数必须为 `UTF-8` 编码。其他编码不保证结果的正确性。
3. 接口请求参数分为固定参数与业务参数两种。固定参数每次请求都必须提供。业务参数与具体的接口业务相关。 


##### 1.3.2 固定参数说明 #####

| 参数         | 名称          | 必须 |  类型   | 说明                                                        |
| :----------- | :------------ | :--: | :-----: | :---------------------------------------------------------- |
| method       | 接口名称      |  是  | String  | API 接口名称。                                              |
| v            | 接口版本号    |  是  | String  | 接口的升级通过版本来号区别。如：1.0.0                       |
| appid        | 应用标识      |  是  | String  | 通过此参数可以区别是谁(IOS、Android)在调用该接口。          |
| timestamp    | 时间戳        |  是  | Integer | 发起请求时的时间戳。通过这个来让每次请求生成不同的 md5 值。 |
| unique_id    | 设备唯一码    |  是  | String  | 通过这个唯一码可以做设备的限制，非 APP 调用传空字符串。     |
| app_v        | APP 版本号    |  是  | String  | 如果是 APP 客户端此字段必传。否则传空字符串。               |
| platform     | 操作平台      |  是  | Integer | 操作平台：1- IOS、2-Android、3-H5、4-web。                  |
| channel      | 渠道          |  是  | String  | Android 传渠道编号。其他则传空字符串。                      |
| device_token | 信鸽设备TOKEN |  是  | String  | 推送采用信鸽的推送服务。没有传空字符串即可。                |

> 注：服务器端通过 `appid` 得到对应的密钥。然后生成签名与客户端的签名进行对比。所以，通过 `appid` 这个参数就可以识别出是哪个端(`IOS`、`Android`、`活动`)在调用该接口。这样既保证了通信的安全，也保证了不会混用一个密钥导致安全性问题。
>
> 另外一个好处是，我们经常会根据不同渠道打不同的安装包。后续也可以分配单独的 `appid`，想停用该包的时候快速实现。给第三方调用的时候，也可以根据 `appid` 快速停用。
>
> 根据 `channel` 渠道参数进行 `Android` 下载地址的路由切换。

  

##### 1.3.3 接口返回数据格式 #####

| 参数 | 名称     |  类型   | 说明                                     |
| :--- | :------- | :-----: | :--------------------------------------- |
| code | 错误码   | Integer | 200 代表成功，其他值代表错误。           |
| msg  | 错误描述 | String  | 错误的具体描述信息。成功时也会返回信息。 |
| data | 接口数据 | Object  | 错误的时候此参数不返回。HashMap 对象。   |

**成功示例：**

```json
{
    "code": 200,
    "msg": "登录成功",
    "data": {
        "token": "dad3a37aa9d50688b5157698acfd7aee",
        "login_time": "2017-05-04 16:38:33"
    }
}
```

**失败示例:**

```json
{
    "code": 503,
    "msg": "账号或密码不正确"
}
```

> 特别注意：
>
> 1）接口响应数据里面的错误码与 HTTP 协议的状态码是两回事儿。请勿混淆了。
>
> 2）如果服务器返回非 200 状态的 `HTTP` 状态码，请客户端自行处理。避免只以服务器返回的 `json` 数据做提示。因为，这时候是取不到任何 `json` 返回数据。

  

#### 1.4 加密方式 ####

本文档所有的接口全部采用验签形式。即旧版的非对称加密模式不再使用。

##### 1.4.1 验签规则 #####

> 假如有如下请求参数：

| 参数        | 名称    |  必须  |   类型    |
| :-------- | :---- | :--: | :-----: |
| method    | 接口名称  |  是   | String  |
| v         | 接口版本  |  是   | String  |
| appid     | 应用标识  |  是   | String  |
| timestamp | 时间戳   |  是   | Integer |
| unique_id | 设备唯一码 |  是   | String  |
| username  | 账号    |  是   | String  |
| password  | 密码    |  是   | String  |

然后组装成一个数组(Java 称 HashMap)：

```json
[
    'method'    => 'user.login',
    'v'         => '1.0.0',
    'appid'     => 'ios_app_id',
    'timestamp' => '1493898621',
    'unique_id' => '68f66b5b72b864dd389748bffe112f4f',
    'username'  => '18575202691',
    'password'  => '123456'
]
```

然后把这个数组进行 `JSON` 转换。得到如下结果：

```json
{
    "method": "user.login",
    "v": "1.0.0",
    "appid": "ios_app_id",
    "timestamp": "1493898621",
    "unique_id": "68f66b5b72b864dd389748bffe112f4f",
    "username": "18575202691",
    "password": "123456"
}
```

当然，上面是经过我美化过后的 JSON 格式。未格式化的 JSON 字符串内容如下：

```
{"method":"user.login","v":"1.0.0","appid":"ios_app_id","timestamp":"1493898621","unique_id":"68f66b5b72b864dd389748bffe112f4f","username":"18575202691","password":"123456"}
```

这时使用服务器分配给客户端的密钥进行 `MD5` 得到签名。`iOS`、`Android` 的密钥不一样。是单独配置的。这样可以区分每个接口请求是从什么端发送。

> 注：生成的 JSON 一定要在程序中暂存起来，POST 给接口的时候会用到这个字符串。

假使，示例使用的是：**7512100214f62d7de8ba01b281d6da02**

那么这时候用上面未格式化的 `JSON` 与上面的密钥串进行拼接。拼接示例结果如下:

```
{"method":"user.login","v":"1.0.0","appid":"ios_app_id","timestamp":"1493898621","unique_id":"68f66b5b72b864dd389748bffe112f4f","username":"18575202691","password":"123456"}7512100214f62d7de8ba01b281d6da02
```

此时把上面拼接的字符串进行 `MD5`。得到的 `MD5` 值转换成大写。其结果如下：

```
01E62683A5D2B7CD901F5F98C08F10EF
```

 

> 注：由整个加密步骤得到两个东西：
>
> 1）未拼接密钥且未美化的 JSON 字符串。这个对应 POST 到接口里面的 data 参数。
>
> 2）签名。这个对应 POST 到接口里面的 sign 参数。



##### 1.4.2 向服务器接口 POST 参数 #####

假使此时使用的接口地址是：<http://api.yourname.com> 

使用常规的 `POST` 提交方式提交如下两个参数：

| 参数   | 名称   | 说明                       |
| :--- | :--- | :----------------------- |
| data | 接口数据 | 此参数对应上术示例中未格式化的 JSON 数据。 |
| sign | 签名   | 此参数对应上述救命中生成的签名值。        |

> 注：需要每个接口都有不同的参数。但是，这些参数全部序列化成了 JSON，而这个 JSON 内容以 data 参数传递。
>
> 所以，并不是普通的把所有参数都以普通的 POST 方式一个一个 POST 传递。

以上面的示例为准：

```
data:{"method":"user.login","v":"1.0.0","appid":"ios_app_id","timestamp":"1493898621","unique_id":"68f66b5b72b864dd389748bffe112f4f","username":"18575202691","password":"123456"}
sign:01E62683A5D2B7CD901F5F98C08F10EF
```

> 也就是说，不管请求哪个接口。最终 POST 的时候，都只传递 `data` 、`sign` 两个参数。



####  1.5 错误说明

在接口响应的数据中，除了错误 200 之外的其他错误码均代表接口响应错误。基本上大多数错误码都可以直接提示给用户错误信息。但是，有一类错误码，它属于错误码中一种特殊的错误码。

比如，注册的账号已经存在，此时客户端接收到 604 错误的时候，不仅要提示用户“账号已注册”，并且还要跳转到登录界面。

所以，在系统中总共分为三种错误码：200 代表成功处理、503 代表业务处理失败、其他错误有特定含义。

- 200 : 代表请求成功并正确响应了数据。
- 403 : 您没有权限访问。一般发生在跨应用调接口。
- 404 : 您访问的资源不存在。
- 500 : 服务器发生了异常。比如：数据库、Redis 以及各种服务调用异常就会报 500 错误。
- 503 : 业务普通错误的错误码。比如，文章不存在，金额小于 0 等错误提示。
- 504：客户端向服务端接口请求时的参数中 method 不存在报此错误码。此错误正式环境不记录日志。
- 505：客户端向服务端接口请求时的参数中 v 版本号参数不存在报此错误码。此错误正式环境不记录日志。
- 506：客户端向服务端接口请求时的参数中 appid 不存在报此错误码。此错误正式环境不记录日志。
- 507：客户端向服务端接口请求时的参数中 timestamp 不存在报此错误码。此错误正式环境不记录日志。
- 508：客户端请求的 IP 不允许请求。访问特殊类型 API 接口时会限制请求的 IP。
- 509：访问的接口不存在。
- 510：整个系统访问受限的疑似非法 IP。
- 601 : 登录超时，请重新登录。
- 602 : 您还未登录。
- 603：账号被其他人登录。
- 604：账号已注册。
- 605：账号未注册。
- 606：密码已经修改。
- 607：登录密码错误被限制 24 小时方可再次登录。
- 700：短信验证码不正确。

> 当需要其他特殊码进行特殊动作的时候，再约定。 




### 2 接口列表 ###

#### 2.1 初始化接口[system.init]  ####

**所谓初始化接口，是指 APP 启动时第一个请求的接口。**

此接口主要解决接口零散导致启动时造成的请求时间过长而用户体验下降的问题。其次改善了 APP 客户端编码的复杂度。

> 请求参数

| 参数   | 名称            | 必须 |  类型  | 说明                     |
| :----- | :-------------- | :--: | :----: | :----------------------- |
| method | 接口名称        |  是  | String | 接口值 -> system.init    |
| token  | 用户 Token 令牌 |  是  | String | 如果用户未登录传空字符串 |

>说明：token 令牌是用来做用户登录状态判断所用。令牌过期，该接口会返回说明。如果没有过期，则服务器会刷新令牌的过期时间。使其一直处于有效期。除了这个好处，还有另外一个好处：客户端依赖有 token 令牌的情况才进行请求的情况，就可以全部不用请求了。因为 token 令牌失效了的话。客户端就可以把 token 清空。
>

| 参数                   | 名称                         | 类型    | 说明                                                         |
| ---------------------- | ---------------------------- | ------- | ------------------------------------------------------------ |
| token_status           | 用户令牌状态                 | String  | 0 - 令牌无效或已经过期、1 - 令牌正常可用。                   |
| upgrade                | 升级数据                     | Object  | 升级数据对象。明细参见 upgrade. 开头的说明。                 |
| upgrade.upgrade_way    | 升级模式                     | String  | 0 - 不升级、1 - 建议升级、2 - 强制升级、3 - 应用关闭         |
| upgrade.app_v          | 目标 APP 版本                | String  | 需要升级到此版本,如果已经是最新版本，此值为空字符串          |
| upgrade.app_title      | 升级提示的标题               | String  | 需要升级到此版本,如果已经是最新版本，此值为空字符串          |
| upgrade.app_desc       | 升级提示的描述               | String  | 需要升级到此版本,如果已经是最新版本，此值为空字符串          |
| upgrade.app_url        | APP 下载地址                 | String  | 如果已经是最新版本或是IOS版本，则此值为空字符串              |
| upgrade.dialog_repeat  | 升级弹窗重复提示             | Integer | 0 - 否，1 - 是。                                             |
| upgrade.origin_v       | 当前APP版本                  | String  | 原样返回请求过去的用户APP版本号                              |
| start_ad               | 广告对象                     | Object  | 没有启动广告则返回空对象。                                   |
| start_ad.ad_id         | 广告 ID                      | Integer |                                                              |
| start_ad.ad_name       | 广告名称                     | String  |                                                              |
| start_ad.ad_image_url  | 广告图片地址                 | String  |                                                              |
| start_ad.ad_url        | 广告跳转地址                 | String  | 必须实现内链跳转。详情见：系统内外链接规范文档。             |
| app_home_right_btn_url | APP 首页导航右侧按钮跳转 URL | String  | APP 首页导航栏左右两侧各一个按钮。                           |
| app_service            | APP 左侧底部服务中心按钮     | Object  | APP 左侧滑动菜单。                                           |
| app_service.txt        | 服务中心按钮名称             | String  |                                                              |
| app_service.url        | 服务中心按钮 URL             | String  |                                                              |
| app_about              | APP 左侧底部关于我们按钮     | Object  | APP 左侧滑动菜单。                                           |
| app_about.txt          | 关于我们按钮名称             | String  |                                                              |
| app_about.url          | 关于我们按钮 URL             | String  |                                                              |
| app_feedback           | APP 左侧底部意见返回按钮     | Object  | APP 左侧滑动菜单。                                           |
| app_feedback.txt       | 意见反馈按钮名称             | String  |                                                              |
| app_feedback.url       | 意见反馈按钮 URL             | String  |                                                              |
| notice_dialog          | 弹框公告对象                 | Object  | 当没有弹窗公告的时候返回空对象。                             |
| notice_dialog.title    | 公告标题                     | String  |                                                              |
| notice_dialog.summary  | 公告摘要                     | String  |                                                              |
| notice_dialog.edition  | 公告版次                     | String  | 当这个公告在 APP 正确弹出之后，APP 客户端要记录该值在本地。下次拿此值判断，相同则说明已经弹窗了就不再弹二次。 |

示例：

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "token_status": 1,
        "upgrade": {
            "upgrade_way": 2,
            "app_v": "1.0.1",
            "app_title": "端午节新版闹龙舟",
            "app_desc": "端午节新版闹龙舟",
            "app_url": "https://github.com/fingerQin",
            "dialog_repeat": 1,
            "origin_v": "1.0.0"
        },
        "start_ad": {
            "ad_id": 7,
            "ad_name": "APP 启动页广告",
            "ad_image_url": "http://xxx.com/images/voucher/20190502/5cca477a0f163.png",
            "ad_url": "https://github.com/fingerQin"
        },
        "app_home_right_btn_url": "https://github.com/fingerQin",
        "app_service": {
            "txt": "服务中心",
            "url": "https://github.com/fingerQin"
        },
        "app_about": {
            "txt": "关于我们",
            "url": "https://github.com/fingerQin"
        },
        "app_feedback": {
            "txt": "意见反馈",
            "url": "https://github.com/fingerQin"
        },
        "notice_dialog": {
            "title": "恭喜您被选中为2019年锦鲤",
            "summary": "恭喜您被选中为2019年锦鲤",
            "edition": "40000000001556786349"
        }
    }
}
```



#### 2.2 升级接口[system.upgrade] ####

该接口通常用于用户检查当前 APP 是否存在新版本的情况。该结果与 2.1 的 `system.init` 接口里面返回的升级信息一致。

> 请求参数

| 参数   |      名称      | 必须 |  类型  | 说明                            |
| :----- | :------------: | :--: | :----: | :------------------------------ |
| method |    接口名称    |  是  | String | 接口值 -> app.upgrade           |
| app_v  |   APP 版本号   |  是  | String | 此参数属于接口固定参数          |
| token  | 用户会话 token |  是  | String | 有 token 就传，没有就传空字符串 |

> 返回参数

|          参数           |    名称     |   类型    |                 说明                 |
| :-------------------: | :-------: | :-----: | :--------------------------------: |
|      upgrade_way      |   升级模式    | String  | 0 - 不升级、1 - 建议升级、2 - 强制升级、3 - 应用关闭 |
|         app_v         | 目标 APP 版本 | String  |     需要升级到此版本,如果已经是最新版本，此值为空字符串     |
|       app_title       |  升级提示的标题  | String  |     需要升级到此版本,如果已经是最新版本，此值为空字符串     |
|       app_desc        |  升级提示的描述  | String  |     需要升级到此版本,如果已经是最新版本，此值为空字符串     |
|        app_url        | APP 下载地址  | String  |     如果已经是最新版本或是IOS版本，则此值为空字符串      |
| upgrade.dialog_repeat | 升级弹窗重复提示  | Integer |            0 - 否，1 - 是。            |
|   upgrade.origin_v    |  当前APP版本  | String  |         原样返回请求过去的用户APP版本号          |

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "upgrade_way": 0,
        "app_v": "",
        "app_title": "",
        "app_desc": "",
        "app_url": "",
        "dialog_repeat": 0,
        "origin_v": "1.0.0"
    }
}
```



#### 2.3 获取防重复提交令牌[system.request.token]

> 该接口用于整个系统中每一处需要提交数据的位置。比如，注册/留言/购买 等操作。避免，重复提前导致的各种未知错误或恶意用户非法操作。
>
> 该接口会用于特殊的接口。当特殊接口有要求时，会注明。

> 请求参数

| 参数   | 名称           | 必须 |  类型   | 说明                           |
| ------ | -------------- | :--: | :-----: | ------------------------------ |
| method | 接口名称       |  是  | String  | 接口值 -> system.request.token |
| token  | 用户会话 TOKEN |  是  | String  | 未登录传空字符串               |
| number | 令牌数量       |  是  | Integer | 最小值必须为1，最大值为5。     |

> 返回参数

| 参数  | 名称           | 类型   | 说明                             |
| ----- | -------------- | ------ | -------------------------------- |
| token | 防重复请求令牌 | String | 任何有数据提交的位置都需要调用。 |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "token": "b5a6ff4719eae157f0af837f94f915ca"
    }
}
```



#### 2.4 文件上传接口[system.upload]

> 请求参数

| 参数   | 名称         | 必须 |  类型  | 说明                    |
| ------ | ------------ | :--: | :----: | ----------------------- |
| method | 接口名称     |  是  | String | 接口值 -> system.upload |
| token  | 会话 TOKEN   |  是  | String | 用户登录会话 TOKEN      |
| image  | 文件上传标识 |  是  | String | **不参与签名。**        |

> 注：image  参数是用表单上传时的 name 名称。一定不要参与签名哟~~~

> 返回参数

| 参数               | 名称         | 类型    | 说明                       |
| ------------------ | ------------ | ------- | -------------------------- |
| file_id            | 文件 ID      | Integer | 主要用于客户端做唯一标识用 |
| image_url          | 图片绝对路径 | String  |                            |
| relative_image_url | 图片相对路径 | String  |                            |

> 返回示例

```json
{
    "code": 200,
    "msg": "上传成功",
    "data": {
        "file_id": "25",
        "image_url": "http://files.yourname.com/images/images/20180717/5b4dd766b9507.jpg",
        "relative_image_url": "/images/images/20180717/5b4dd766b9507.jpg"
    }
}
```



#### 2.5 登录接口[user.login] ####

该接口用于用户登录 `APP` 使用。用户登录成功之后，服务器会生成一个与当前用户相关的 `Token` 令牌。这个令牌有一个失效时间（暂定 30 天）。30 天之后，如果这个用户一直没有启动过 `APP`，则此 `Token` 会失效。用户就需要重新登录。服务器会返回一个具体的错误码来代表登录超时、未登录等情况。

如果用户中途启动过 `APP`，则失效时间会从启动时的时间往后推 30 天。

还会存在另外一种情况：用户账号在其他手机上登录。因为当前我们会生成一个 `Token` 令牌。此 `Token` 令牌是与用户一对一关联起来的。下次登录会把上次登录产生的 `Token` 令牌覆盖。就实现了上次登录的 `APP` 账户被挤下线的功能。

> 请求参数

| 参数       | 名称       | 必须 |  类型   | 说明                                   |
| :--------- | :--------- | :--: | :-----: | :------------------------------------- |
| method     | 接口名称   |  是  | String  | 接口值 -> user.login                   |
| mobile     | 手机账号   |  是  | String  | 手机号。                               |
| code       | 短信验证码 |  是  | String  | 登录时需要先调用发送短信验证码的接口。 |
| login_type | 登录类型   |  是  | Integer | 1- 验证码登录、2 - 密码登录。          |

> 返回参数

| 参数     | 名称         |  类型  | 说明                         |
| -------- | ------------ | :----: | ---------------------------- |
| token    | 会话 TOKEN   | String |                              |
| open_id  | 用户开放标识 | String | 唯一标识。避免隐私信息泄漏。 |
| mobile   | 手机号码     | String |                              |
| headimg  | 头像地址     | String | 返回的是绝对地址。           |
| nickname | 昵称         | String |                              |
| intro    | 个人简介     | String |                              |
| reg_time | 注册时间     | String |                              |

> 返回示例

```json
{
    "code": 200,
    "msg": "登录成功",
    "data": {
        "token": "",
        "open_id": "677ca20c1fbbb9dc6ebd10c59ebeb196",
        "mobile": "18575202691",
        "headimg": "",
        "nickname": "148****1001",
        "intro": "",
        "reg_time": "2019-04-01 16:17:27"
    }
}
```



#### 2.6 注册接口[user.register]

> 请求参数

| 参数     | 名称       | 必须 |  类型  | 说明                              |
| -------- | ---------- | :--: | :----: | --------------------------------- |
| method   | 接口名称   |  是  | String | 接口值 -> user.register           |
| mobile   | 手机号     |  是  | String |                                   |
| password | 密码       |  是  | String | 6-20 位数字字母下划线破折号组成。 |
| code     | 短信验证码 |  是  | String |                                   |

>返回参数

| 参数     | 名称         |  类型  | 说明                                     |
| -------- | ------------ | :----: | ---------------------------------------- |
| token    | 会话 TOKEN   | String | 注册成功则自动登录。                     |
| open_id  | 用户开放标识 | String | 用户唯一标识。用于防止用户隐私信息泄漏。 |
| mobile   | 登录手机号   | String |                                          |
| headimg  | 头像         | String | 为空，则显示默认头像。                   |
| nickname | 昵称         | String |                                          |
| reg_time | 注册时间     | String | 格式：Y-m-d H:i:s                        |
| intro    | 个人简介     | String |                                          |
| openid   | 开放 ID      | String | 用于分享等需要识别用户的标识 ID。        |

```json
{
    "code": 200,
    "msg": "注册成功",
    "data": {
        "token": "",
        "open_id": "677ca20c1fbbb9dc6ebd10c59ebeb196",
        "mobile": "18575202691",
        "headimg": "",
        "nickname": "148****1001",
        "intro": "",
        "reg_time": "2019-04-01 16:17:27"
    }
}
```



#### 2.7 用户退出接口[user.logout] ####

用户退出会清理与之相关的缓存数据。比如，关联的 token 令牌。以及绑定的推送设备 ID 等信息。

> 请求参数

| 参数   | 名称            | 必须 |  类型  | 说明                          |
| :----- | :-------------- | :--: | :----: | ----------------------------- |
| method | 接口名称        |  是  | String | 接口值 -> user.logout         |
| token  | 用户 Token 令牌 |  是  | String | 登录时分配给用户的 Token 令牌 |

> 返回数据

**该接口只返回基础的参数**

```json
{
    "code": 200,
    "msg": "退出成功"
}
```

 

#### 2.8 短信发送[sms.send]

> 请求参数

| 参数   | 名称         | 必须 |  类型  | 说明                                           |
| ------ | ------------ | :--: | :----: | ---------------------------------------------- |
| method | 接口名称     |  是  | String | 接口值 -> sms.send                             |
| mobile | 手机号码     |  是  | String |                                                |
| key    | 短信模板标识 |  是  | String | 注册-USER_REGISTER_CODE、登录-USER_LOGIN_CODE  |
| token  | 会话 TOKEN   |  是  | String | 登录时分配的会话 TOKEN，有则传，无则传空字符串 |

> 返回示例

```json
// 成功返回
{
    "code": 200,
    "msg": "发送成功"
}
// 失败返回
{
    "code": 503,
    "msg": "两次发送间隔小于60秒"
}
```



####  2.9 短信验证[sms.verify]

> 请求参数

| 参数   | 名称         | 必须 |  类型  | 说明                                          |
| ------ | ------------ | :--: | :----: | --------------------------------------------- |
| method | 接口名称     |  是  | String | 接口值 -> sms.verify                          |
| mobile | 手机号码     |  是  | String |                                               |
| key    | 短信模板标识 |  是  | String | 注册-USER_REGISTER_CODE、登录-USER_LOGIN_CODE |
| code   | 验证码       |  是  | String |                                               |

> 返回参数

```json
// [1]
{
    "code": 503,
    "msg": "验证码已失效"
}

// [2]
{
    "code": 503,
    "msg": "您的验证码不正确"
}

// [3]
{
    "code": 200,
    "msg": "验证码正确"
}
```



#### 2.10 用户密码修改[user.pwd.edit]

> 请求参数

| 参数    | 名称         | 必须 |  类型  | 说明                         |
| ------- | ------------ | :--: | :----: | ---------------------------- |
| method  | API 接口名称 |  是  | String | 接口值 -> user.pwd.edit      |
| token   | 会话 TOKEN   |  是  | String | 登录则传，未登录传空字符串。 |
| old_pwd | 旧密码       |  是  | String |                              |
| new_pwd | 新密码       |  是  | String |                              |

> 返回示例

```json
// [1]
{
    "code": 200,
    "msg": "密码修改成功"
}
// [2]
{
    "code": 503,
    "msg": "密码修改失败"
}
```



#### 2.11 用户密码找回[user.pwd.find]

> 请求参数

| 参数     | 名称         | 必须 |  类型  | 说明                           |
| -------- | ------------ | :--: | :----: | ------------------------------ |
| method   | API 接口名称 |  是  | String | 接口值 -> user.pwd.find        |
| mobile   | 手机账号     |  是  | String | 用户注册的手机账号。           |
| code     | 短信验证码   |  是  | String | 通过 sms.send 接口发送验证码。 |
| password | 新密码       |  是  | String | 用户新设置的登录密码。         |

> 返回示例

```json
// [1]
{
    "code": 200,
    "msg": "密码找回成功"
}
// [2]
{
    "code": 503,
    "msg": "密码找回失败"
}
```



#### 2.12 用户详情接口[user.detail]

> 请求参数

| 参数   | 名称         | 必须 | 类型   | 说明                         |
| ------ | ------------ | :--: | ------ | ---------------------------- |
| method | API 接口名称 |  是  | String | 接口值 -> user.detail        |
| token  | 会话 TOKEN   |  是  | String | 登录就传，未登录传空字符串。 |

> 返回参数

| 参数     | 名称     |  类型  | 说明                               |
| -------- | -------- | :----: | ---------------------------------- |
| mobile   | 手机账号 | String |                                    |
| open_id  | Openid   | String | 用于对外分享时使用的标识           |
| nickname | 昵称     | String |                                    |
| headimg  | 头像     | String |                                    |
| intro    | 个人简介 | String | 如：心存高远，意守平常，终成千里。 |
| c_time   | 注册时间 | String |                                    |

> 返回示例

```json
{
    "code": 200,
    "msg": "信息获取成功",
    "data": {
        "mobile": "18575202692",
        "open_id": "960a3d82f110f8b54ea4a88f8bc9f615",
        "nickname": "185****2692",
        "headimg": "http://xx.com/files/5b9a1b9e34193.png",
        "intro": "",
        "c_time": "2018-06-29 18:56:48"
    }
}
```

#### 2.13 系统广告接口[system.ads]

> 请求参数

| 参数   | 名称       | 必须 | 类型   | 说明                       |
| ------ | ---------- | :--: | ------ | -------------------------- |
| method | API 接口值 |  是  | String | 接口值 -> system.ads       |
| token  | 会话 TOKEN |  是  | String | 有值就传，无值传空字符串   |
| code   | 广告位编码 |  是  | String | 每个位置编码视业务系统而定 |

> 返回参数

| 参数         | 名称         | 类型    | 说明                                 |
| ------------ | ------------ | ------- | ------------------------------------ |
| ad_id        | 广告 ID      | Integer |                                      |
| ad_name      | 广告名称     | String  | 轮播广告的文字，如果不需要可不使用。 |
| ad_image_url | 广告图片地址 | String  |                                      |
| ad_url       | 广告跳转地址 | String  | 跳转地址分为内链与外链。             |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "ad_id": 6,
                "ad_name": "世界杯广告",
                "ad_image_url": "http://xx.com/files/5b9a1b9e34193.png",
                "ad_url": "http://www.baidu.com"
            }
        ]
    }
}
```

#### 2.14 友情链接接口[system.link]

> 请求参数

| 参数   | 名称         | 必须 | 类型   | 说明                  |
| ------ | ------------ | :--: | ------ | --------------------- |
| method | API 接口名称 |  是  | String | 接口值 -> system.link |
| token  | 会话 TOKEN   |  是  | String | 有则传，无传字符串    |

> 返回参数

| 参数            | 名称         | 类型    | 说明 |
| --------------- | ------------ | ------- | ---- |
| cat_id          | 分类 ID      | Integer |      |
| cat_name        | 分类名称     | String  |      |
| links           | 友情链接列表 | Object  |      |
| links.link_name | 友链名称     | String  |      |
| links.link_url  | 友链 URL     | String  |      |
| links.image_url | 友链图片     | String  |      |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "cat_id": 7,
                "cat_name": "搜索引擎",
                "links": [
                    {
                        "link_name": "Google",
                        "link_url": "https://www.google.com",
                        "image_url": ""
                    }
                ]
            }
        ]
    }
}
```

#### 2.15 系统分类接口[system.category.list]

> 请求参数

| 参数     | 名称         | 必须 | 类型    | 说明                               |
| -------- | ------------ | :--: | ------- | ---------------------------------- |
| method   | API 接口名称 |  是  | String  | 接口值 -> system.category.list     |
| token    | 会话 TOKEN   |  是  | String  | 有则传，无传字符串                 |
| parentid | 父分类 ID    |  是  | Integer | 默认传 0                           |
| cat_type | 分类类型     |  是  | Integer | 1-文章分类、2-友情链接、3-商品分类 |

> 返回参数

| 参数         | 名称       | 类型    | 说明       |
| ------------ | ---------- | ------- | ---------- |
| cat_id       | 分类 ID    | Integer |            |
| cat_name     | 分类名称   | String  |            |
| parentid     | 父分类 ID  | Integer |            |
| cat_code     | 分类编码   | String  | 具有唯一性 |
| sub          | 子分类列表 | Object  |            |
| sub.cat_id   | 子分类  ID | Integer |            |
| sub.cat_name | 子分类名称 | String  |            |
| sub.parentid | 父分类 ID  | Integer |            |
| sub.cat_code | 子分类编码 | String  | 具有唯一性 |

> 返回示例

```json
{
    "code": 200,
    "msg": "Success",
    "data": {
        "0_1": {
            "cat_id": 1,
            "cat_name": "理财资讯",
            "parentid": 0,
            "cat_code": "100000000000000000000000000000",
            "sub": {
                "0_2": {
                    "cat_id": 2,
                    "cat_name": "基金",
                    "parentid": 1,
                    "cat_code": "100100000000000000000000000000",
                    "sub": []
                },
                "0_3": {
                    "cat_id": 3,
                    "cat_name": "股票",
                    "parentid": 1,
                    "cat_code": "100101000000000000000000000000",
                    "sub": []
                }
            }
        },
        "0_4": {
            "cat_id": 4,
            "cat_name": "体育新闻",
            "parentid": 0,
            "cat_code": "101000000000000000000000000000",
            "sub": {
                "0_5": {
                    "cat_id": 5,
                    "cat_name": "足球",
                    "parentid": 4,
                    "cat_code": "101100000000000000000000000000",
                    "sub": []
                },
                "0_6": {
                    "cat_id": 6,
                    "cat_name": "篮球",
                    "parentid": 4,
                    "cat_code": "101101000000000000000000000000",
                    "sub": []
                }
            }
        }
    }
}
```

#### 2.16 系统首页接口[system.home]

> 请求参数

| 参数   | 名称         | 必须 | 类型   | 说明                  |
| ------ | ------------ | :--: | ------ | --------------------- |
| method | API 接口名称 |  是  | String | 接口值 -> system.home |
| token  | 会话 TOKEN   |  是  | String | 有则传，无传字符串    |

> 返回参数

| 参数             | 名称         | 类型    | 说明               |
| ---------------- | ------------ | ------- | ------------------ |
| ads              | 广告         | Object  |                    |
| ads.ad_id        | 广告 ID      | Integer |                    |
| ads.ad_name      | 广告名称     | String  |                    |
| ads.ad_image_url | 广告图片 URL | String  |                    |
| ads.ad_url       | 广告跳转 URL | String  | 地址区分内链与外链 |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "ads": [
            {
                "ad_id": 6,
                "ad_name": "世界杯广告",
                "ad_image_url": "http://xxx.com/files/5b9a1b9e34193.png",
                "ad_url": "http://www.baidu.com"
            }
        ]
    }
}
```

#### 2.17 系统公告列表[notice.list]

> 请求参数

| 参数   | 名称         | 必须 | 类型    | 说明                  |
| ------ | ------------ | ---- | ------- | --------------------- |
| method | API 接口名称 | 是   | String  | 接口值 -> notice.list |
| token  | 会话令牌     | 是   | String  | 有传，无传空字符串    |
| page   | 页码         | 是   | Integer | 当前页码，默认 1 。   |

> 返回参数

| 参数          | 名称         | 类型    | 说明                     |
| ------------- | ------------ | ------- | ------------------------ |
| total         | 总记录条数   | Integer |                          |
| page          | 当前页码     | Integer |                          |
| count         | 当前分页条数 | Integer | 服务端按照多少条进行分页 |
| isnext        | 是否有下一页 | Boolean |                          |
| list          | 列表对象     | Object  |                          |
| list.noticeid | 公告 ID      | Integer |                          |
| list.title    | 公告标题     | String  |                          |
| list.summary  | 公告摘要     | String  |                          |
| list.c_time   | 公告发布时间 | String  |                          |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "noticeid": 2,
                "title": "清明节假期公告",
                "summary": "清明节将至，平台放假通知。",
                "c_time": "2019-04-19 16:18:04"
            },
            {
                "noticeid": 1,
                "title": "五一放假安排",
                "summary": "尊敬的各位用户，大家好。关于 5.1 节，平台放假时间如下。",
                "c_time": "2019-04-19 10:22:08"
            }
        ],
        "total": 2,
        "page": 1,
        "count": 20,
        "isnext": false
    }
}
```

#### 2.18 公告详情[notice.detail]

> 请求参数

| 参数     | 名称           | 必须 | 类型    | 说明                    |
| -------- | -------------- | ---- | ------- | ----------------------- |
| method   | API 接口名称   | 是   | String  | 接口值 -> notice.detail |
| token    | 会话令牌 TOKEN | 是   | String  | 有传，无传空字符串      |
| noticeid | 公告 ID        | 是   | Integer |                         |

> 返回参数

| 参数     | 名称         | 类型    | 说明 |
| :------- | ------------ | ------- | ---- |
| noticeid | 公告 ID      | Integer |      |
| title    | 公告标题     | String  |      |
| summary  | 公告摘要     | String  |      |
| body     | 公告内容     | String  |      |
| c_time   | 公告发布时间 | String  |      |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "noticeid": 1,
        "title": "五一放假安排",
        "summary": "尊敬的各位用户，大家好。关于 5.1 节，平台放假时间如下。",
        "body": "尊敬的各位用户，大家好。关于 5.1 节，平台放假时间如下。",
        "c_time": "2019-04-19 10:22:08"
    }
}
```

#### 2.19 用户公告未读数量接口[notice.unread.count]

> 请求参数

| 参数   | 名称           | 必须 | 类型   | 说明                          |
| ------ | -------------- | ---- | ------ | ----------------------------- |
| method | API 接口名称   | 是   | String | 接口值 -> notice.unread.count |
| token  | 会话令牌 TOKEN | 是   | String | 有传，无传空字符串            |

> 返回参数

| 参数  | 名称         | 类型    | 说明 |
| ----- | ------------ | ------- | ---- |
| count | 未读公告数量 | Integer |      |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "count": 0
    }
}
```

#### 2.20 系统消息列表接口[message.list]

> 请求参数

| 参数   | 名称           | 必须 | 类型    | 说明                   |
| ------ | -------------- | ---- | ------- | ---------------------- |
| method | API 接口名称   | 是   | String  | 接口值 -> message.list |
| token  | 会话令牌 TOKEN | 是   | String  |                        |
| page   | 页码           | 是   | Integer |                        |

> 返回参数

| 参数             | 名称            | 类型    | 说明                    |
| ---------------- | --------------- | ------- | ----------------------- |
| total            | 总记录条数      | Integer |                         |
| page             | 当前页码        | Integer |                         |
| count            | 每页分页条数    | Integer | 服务端按照此条数分页    |
| isnext           | 是否有下一页    | Boolean |                         |
| list             | 列表对象        | Object  |                         |
| list.msgid       | 消息 ID         | Integer |                         |
| list.msg_type    | 消息类型        | Integer | 1-系统、2-福利          |
| list.type_ref_id | 消息类型关联 ID | Integer | 如福利消息，代表福利 ID |
| list.is_read     | 是否已读        | Integer | 0-未读、1-未读          |
| list.title       | 消息标题        | String  |                         |
| list.content     | 消息内容        | String  |                         |
| list.url         | 消息跳转 URL    | String  | 详情见 url 内外链文档   |
| list.c_time      | 消息发布时间    | String  |                         |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "msgid": 2,
                "msg_type": 1,
                "type_ref_id": 0,
                "is_read": 0,
                "title": "恭喜您被选中为2019年锦鲤",
                "content": "恭喜您被选中2019年锦鲤，我们会在3个工作日内与您联系~",
                "url": "https://github.com/fingerQin",
                "c_time": "2019-04-18 15:12:04"
            },
            {
                "msgid": 1,
                "msg_type": 1,
                "type_ref_id": 0,
                "is_read": 1,
                "title": "五一劳动节福利",
                "content": "五一劳动节福利内容",
                "url": "https://www.exxx.com",
                "c_time": "2019-04-18 11:32:03"
            }
        ],
        "total": 2,
        "page": 1,
        "count": 20,
        "isnext": false
    }
}
```

#### 2.21 系统消息已读状态设置接口[message.read.status]

> 请求参数

| 参数   | 名称           | 必须 | 类型    | 说明                          |
| ------ | -------------- | ---- | ------- | ----------------------------- |
| method | API 接口名称   | 是   | String  | 接口值 -> message.read.status |
| token  | 会话令牌 TOKEN | 是   | String  | 有传，无传空字符串            |
| msgid  | 系统消息 ID    | 是   | Integer |                               |

> 返回示例

```json
{
    "code": 200,
    "msg": "设置成功"
}
```

#### 2.22 文章列表接口[news.list]

> 请求参数

| 参数   | 名称         | 必须 | 类型    | 说明                      |
| ------ | ------------ | ---- | ------- | ------------------------- |
| method | API 接口名称 | 是   | String  | 接口值 -> news.list       |
| cat_id | 分类 ID      | 是   | Integer | 如果没有指定分类，请传 -1 |
| page   | 页码         | 是   | Integer | 默认值 1                  |

> 返回参数

| 参数           | 名称           | 类型    | 说明                         |
| -------------- | -------------- | ------- | ---------------------------- |
| total          | 文章总数       | Integer |                              |
| page           | 当前页码       | Integer |                              |
| count          | 每页显示条数   | Integer | 该值并不是当前返回的结果条数 |
| isnext         | 是否存在下一页 | boolean | ture - 是、false - 否。      |
| list           | 文章列表       | Object  | 文章列表对象。               |
| list.news_id   | 文章 ID        | Integer |                              |
| list.title     | 文章标题       | String  |                              |
| list.intro     | 文章简介       | String  |                              |
| list.image_url | 文章主图       | String  |                              |
| list.source    | 文章来源       | String  |                              |
| list.c_time    | 发布时间       | String  |                              |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "news_id": 8,
                "title": "警方通报检察长妻子岳母殴打公交司机：两打人者被拘留",
                "intro": "经查，30路公交车女司机娄某珍在行驶过程中，与乘客杜某因小孩乘车问题发生言语纠纷。娄某珍先后于车上、车下用随车携带的水杯里的水、水桶里的污水泼洒杜某，杜某在车下用随身携带水杯里的水回泼娄某珍，后该公交车继续行驶。",
                "image_url": "",
                "source": "腾讯新闻",
                "c_time": "2019-08-21 20:04:07"
            },
            {
                "news_id": 7,
                "title": "全国“秋老虎”出没地图出炉！今年的秋老虎咬人吗？",
                "intro": "全国“秋老虎”出没地图出炉！今年的秋老虎咬人吗？",
                "image_url": "",
                "source": "腾讯新闻",
                "c_time": "2019-08-21 20:03:31"
            }
        ],
        "total": 8,
        "page": 1,
        "count": 20,
        "isnext": false
    }
}
```

#### 2.23 文章详情接口[news.detail]

> 请求参数

| 参数    | 名称         | 必须 | 类型    | 说明                  |
| ------- | ------------ | ---- | ------- | --------------------- |
| method  | API 接口名称 | 是   | String  | 接口值 -> news.detail |
| news_id | 文章 ID      | 是   | Integer |                       |

> 返回参数

| 参数      | 名称     | 类型    | 说明 |
| --------- | -------- | ------- | ---- |
| news_id   | 文章 ID  | Integer |      |
| title     | 文章标题 | String  |      |
| intro     | 文章简介 | String  |      |
| image_url | 文章主图 | String  |      |
| source    | 文章来源 | String  |      |
| c_time    | 发布时间 | String  |      |
| content   | 文章内容 | String  |      |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "news_id": 1,
        "title": "为什么对老鼠的研究并不总能在人类身上重现",
        "intro": "十多年前，当人们开始为老鼠和人类绘制基因图谱时，一个国际研究团队就开始研究并比较二者之间的“任务控制中心”功能了。期待已久的报告发布在11月20日出版的《自然》杂志上。",
        "image_url": "",
        "source": "中国科学院",
        "c_time": "2019-08-21 19:56:14",
        "content": "<p>\r\n\t十多年前，当人们开始为老鼠和人类绘制基因图谱时，一个国际研究团队就开始研究并比较二者之间的“任务控制中心”功能了。期待已久的报告发布在11月20日出版的《自然》杂志上。</p>"
    }
}
```

#### 2.24 积分商城商品列表接口[goods.list]

> 请求参数

| 参数        | 名称               | 必须 | 类型    | 说明                       |
| ----------- | ------------------ | ---- | ------- | -------------------------- |
| method      | API 接口名称       | 是   | String  | 接口值 -> goods.list       |
| cat_id      | 商品分类 ID        | 是   | Integer | 未选择时，默认值传 -1      |
| keywords    | 商品名称搜索关键词 | 是   | String  | 搜索时使用，默认值空字符串 |
| start_price | 最小查询价格       | 是   | Float   | 未填写时传 -1              |
| end_price   | 最大查询价格       | 是   | Float   | 未填写时传 -1              |
| page        | 当前页码           | 是   | Integer | 默认值 1                   |
| token       | 会话 token 令牌    | 是   | String  | 未登录时传空字符串         |

> 返回参数

| 参数                 | 名称             | 类型    | 说明                               |
| -------------------- | ---------------- | ------- | ---------------------------------- |
| total                | 文章总数         | Integer |                                    |
| page                 | 当前页码         | Integer |                                    |
| count                | 每页显示条数     | Integer | 该值并非当前页返回的结果数         |
| isnext               | 是否有下一页     | Boolean | true - 是、false - 否。            |
| list                 | 商品列表         | Object  | 商品列表对象。                     |
| list.goodsid         | 商品 ID          | Integer |                                    |
| list.goods_name      | 商品名称         | String  |                                    |
| list.min_price       | 商品最小价格     | Integer | 金币价格。                         |
| list.max_price       | 商品最大价格     | Integer | 金币价格。多规格价格不相同时出现。 |
| list.goods_img       | 商品主图         | String  |                                    |
| list.buy_count       | 购买/兑换次数    | Integer |                                    |
| list.month_buy_count | 最近一月购买次数 | Integer |                                    |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "goodsid": 21,
                "goods_name": "ChinaJoy 服装",
                "min_price": 9000,
                "max_price": 9000,
                "goods_img": "",
                "buy_count": 0,
                "month_buy_count": 0
            },
            {
                "goodsid": 19,
                "goods_name": "明基绝地求生吃鸡鼠标",
                "min_price": 160,
                "max_price": 160,
                "goods_img": "",
                "buy_count": 0,
                "month_buy_count": 0
            }
        ],
        "total": 16,
        "page": 1,
        "count": 20,
        "isnext": false
    }
}
```

#### 2.25 积分商城商品详情接口[goods.detail]

> 请求参数

| 参数     | 名称            | 必须 | 类型    | 说明                       |
| -------- | --------------- | ---- | ------- | -------------------------- |
| method   | API 接口名称    | 是   | String  | API 接口值 -> goods.detail |
| goods_id | 商品 ID         | 是   | Integer |                            |
| token    | 会话 TOKEN 令牌 | 是   | String  |                            |

> 返回参数

| 参数                  | 名称         | 类型    | 说明                                             |
| --------------------- | ------------ | ------- | ------------------------------------------------ |
| goodsid               | 商品 ID      | Integer |                                                  |
| goods_name            | 商品名称     | String  |                                                  |
| min_market_price      | 市场最小价格 | Integer |                                                  |
| max_market_price      | 市场最大价格 | Integer |                                                  |
| min_price             | 最小售价     | Integer | 购买时，最低价格                                 |
| max_price             | 最大售价     | Integer | 购买时，最高价格。多规格商品时存在。             |
| goods_img             | 商品主图     | String  | 可根据实际需求使用。通常详情页显示商品相册图片。 |
| buy_count             | 累计兑换数   | Integer |                                                  |
| month_buy_count       | 近30天兑换数 | Integer |                                                  |
| limit_count           | 每人限兑数量 | Integer | 0 代表不限制。                                   |
| description           | 商品描述     | String  | 积分商城通常由多张图片组成。                     |
| goods_image           | 商品相册     | List    | 由最多 5 张图片组成的商品相册。列表类型。        |
| spec_val              | 规格属性     | Object  | 单规格时为空列表。                               |
| products              | 货品属性     | Object  | 货品属性对象。                                   |
| products.productid    | 货品 ID      | Integer |                                                  |
| products.market_price | 市场价       | Integer |                                                  |
| products.sales_price  | 销售价       | Integer |                                                  |
| products.stock        | 库存         | Integer |                                                  |
| spec_val              | 规格         | String  | 单规格是为空字符串。                             |
| skuid                 | 货品 SKU     | String  | 每一个货品都有一个属于自己的 SKU。               |
| arr_spec_val          | 货品规格属性 | object  | 可以根据实际需求使用                             |

> 返回示例

```json
// 单规格属性返回结果。主要不同的地方在于货品 products 与 规格 spec_val 的不同。
{
    "code": 200,
    "msg": "success",
    "data": {
        "goodsid": 1,
        "goods_name": "ikbc C104 樱桃轴机械键盘",
        "min_market_price": 399,
        "max_market_price": 399,
        "min_price": 399,
        "max_price": 399,
        "goods_img": "http://files.xxx.com/images/voucher/20180822/5b7cbe51ea911.jpg",
        "buy_count": 0,
        "month_buy_count": 0,
        "limit_count": 0,
        "description": "",
        "products": {
            "single_product": {
                "productid": 33,
                "market_price": 399,
                "sales_price": 399,
                "stock": 993,
                "spec_val": "",
                "skuid": "ikbc00000001",
                "arr_spec_val": []
            }
        },
        "spec_val": [],
        "goods_image": [
            "http://files.xxx.com/images/voucher/20180822/5b7cbe51ea911.jpg",
            "http://files.xxx.com/images/voucher/20180822/5b7cbe53eb153.jpg",
            "http://files.xxx.com/images/voucher/20180822/5b7cbe558dc44.jpg",
            "http://files.xxx.com/images/voucher/20180822/5b7cbe59214c9.jpg",
            "http://files.xxx.com/images/voucher/20180822/5b7cbe5b1d2fc.jpg"
        ]
    }
}

// 多规格属性返回示例

{
    "code": 200,
    "msg": "success",
    "data": {
        "goodsid": 19,
        "goods_name": "明基绝地求生吃鸡鼠标",
        "min_market_price": 199,
        "max_market_price": 199,
        "min_price": 160,
        "max_price": 160,
        "goods_img": "http://files.xxx.com/images/voucher/20180828/5b84a62196ac6.jpg",
        "buy_count": 0,
        "month_buy_count": 0,
        "limit_count": 0,
        "description": "",
        "products": {
            "颜色:::红色": {
                "productid": 38,
                "market_price": 199,
                "sales_price": 160,
                "stock": 999,
                "spec_val": "颜色:::红色",
                "skuid": "ben001",
                "arr_spec_val": {
                    "颜色": "红色"
                }
            },
            "颜色:::黑色": {
                "productid": 39,
                "market_price": 199,
                "sales_price": 160,
                "stock": 999,
                "spec_val": "颜色:::黑色",
                "skuid": "ben002",
                "arr_spec_val": {
                    "颜色": "黑色"
                }
            }
        },
        "spec_val": {
            "颜色": [
                "红色",
                "黑色"
            ]
        },
        "goods_image": [
            "http://files.xxx.com/images/voucher/20180822/5b7cbc17cb04f.jpg",
            "http://files.xxx.com/images/voucher/20180822/5b7cbc19bb25c.jpg",
            "http://files.xxx.com/images/voucher/20180828/5b84a5c54f47a.jpg",
            "http://files.xxx.com/images/voucher/20180828/5b84a5c7c2e03.jpg",
            "http://files.xxx.com/images/voucher/20180828/5b84a62196ac6.jpg"
        ]
    }
}
```

#### 2.26 订单列表接口[order.list]

> 请求参数

| 参数         | 名称            | 必须 | 类型    | 说明                 |
| ------------ | --------------- | ---- | ------- | -------------------- |
| method       | API 接口名称    | 是   | String  | 接口值 -> order.list |
| token        | 会话 TOKEN 令牌 | 是   | String  | 未登录传空字符串     |
| order_sn     | 订单号          | 是   | String  | 默认传空字符串       |
| order_status | 订单状态        | 是   | Integer | 默认传 -1            |
| start_time   | 起始下单时间    | 是   | String  | 默认传空字符串       |
| end_time     | 截止下单时间    | 是   | String  | 默认传空字符串       |
| page         | 页码            | 是   | Integer | 默认传 1             |

> 返回参数

| 参数                          | 名称             | 类型    | 说明                           |
| ----------------------------- | ---------------- | ------- | ------------------------------ |
| total                         | 总订单数         | Integer |                                |
| page                          | 当前页码         | Integer |                                |
| count                         | 每页显示条数     | Integer | 该值并不是当前页返回的记录数。 |
| isnext                        | 是否有下一页     | Boolean | true - 是、false - 否。        |
| list                          | 订单列表对象     | Object  |                                |
| list.orderid                  | 订单 ID          | Integer |                                |
| list.order_sn                 | 订单号           | String  |                                |
| list.total_price              | 订单总额         | Integer |                                |
| list.pay_time                 | 支付时间         | String  |                                |
| list.order_status             | 订单状态         | Integer |                                |
| list.order_status_label       | 订单状态标签     | String  | 状态对应的中文释义。           |
| list.shipping_time            | 发货时间         | String  |                                |
| list.done_time                | 交易成功时间     | String  |                                |
| list.closed_time              | 订单关闭时间     | String  |                                |
| list.receiver_name            | 收件人姓名       | String  |                                |
| list.receiver_province        | 收件省份         | String  |                                |
| list.receiver_city            | 收件城市         | String  |                                |
| list.receiver_district        | 收件城市区县     | String  |                                |
| list.receiver_street          | 收件街道         | String  |                                |
| list.receiver_address         | 门牌号等详细地址 | String  |                                |
| list.receiver_mobile          | 联系号码         | String  |                                |
| list.c_time                   | 下单时间         | String  |                                |
| list.goods_list               | 购买商品列表     | Object  |                                |
| list.goods_list.goodsid       | 商品 ID          | Integer |                                |
| list.goods_list.goods_name    | 商品名称         | String  |                                |
| list.goods_list.goods_image   | 商品主图         | String  |                                |
| list.goods_list.productid     | 货品 ID          | Integer |                                |
| list.goods_list.spec_val      | 货品规格属性     | String  |                                |
| list.goods_list.quantity      | 购买数量         | Integer |                                |
| list.goods_list.sales_price   | 购买单价         | Integer |                                |
| list.goods_list.payment_price | 实际支付价格     | Integer | 扣除优惠之类的总价             |
| list.goods_list.total_price   | 商品总价         | Integer | 未优惠的总价                   |
| list.goods_list.market_price  | 商品市场价       | Integer |                                |

> 返回示例

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "orderid": 49,
                "order_sn": "SN202003140000000001",
                "total_price": 399,
                "pay_time": "2020-03-14 23:45:06",
                "order_status": 1,
                "shipping_time": null,
                "done_time": null,
                "closed_time": null,
                "receiver_name": "张木沐",
                "receiver_province": "广东省",
                "receiver_city": "深圳市",
                "receiver_district": "南山区",
                "receiver_street": "",
                "receiver_address": "粤海街道东方科技大厦601",
                "receiver_mobile": "14812345678",
                "c_time": "2020-03-14 23:45:06",
                "order_status_label": "待发货",
                "goods_list": [
                    {
                        "goodsid": 1,
                        "goods_name": "ikbc C104 樱桃轴机械键盘",
                        "goods_image": "http://xxx/5b7cbe51ea911.jpg",
                        "productid": 33,
                        "spec_val": "",
                        "market_price": 399,
                        "sales_price": 399,
                        "quantity": 1,
                        "payment_price": 399,
                        "total_price": 399
                    }
                ]
            }
        ],
        "total": 1,
        "page": 1,
        "count": 20,
        "isnext": false
    }
}
```

