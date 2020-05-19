# 接口概述

文档更新日期：{docsify-updated}

## API URL

开发环境：https://xxx.xxx.com

测试环境：https://xxx.xxx.com

生产环境：https://xxx.xxx.com

## 全局 HTTP 请求头

!> 请把以下参数放入 HTTP 请求头中

| 名字 | 必填 | 类型 | 详细描述 |
| ---- | ---- | ---- | :------- |
| X-APP-ID | 是 | INT | 应用ID <br />小程序: 1<br /> Android: 2<br /> iOS: 3 |
| X-APP-VER | 否 | STRING | 客户端版本号，例如 1.2.3 （约定采用三段版本号） |
| Authorization | 是 | STRING | 示例 `Bearer {token}` <br /> 其中 `{token}` 为调用 `/auth/login` 接口后服务端返回的令牌）|
| Content-Type | 是 | STRING | 必须为 `application/json` |

## 全局响应格式

| 名字 | 类型 | 详细描述 |
| ---- | ---- | -------- |
| code | INT | 响应码 |
| message | STRING | 响应文本 |
| data | JSON | 响应内容 |

## 响应码定义

| code | 详细描述 |
| --------- | -------- |
| 0 | 成功 |
| -1 | 普通异常，详见 `message` 字段描述，客户端可以 `toast` 形式显示异常文本 |
| -1000 | 无效的登录凭证，请前往登录 |
