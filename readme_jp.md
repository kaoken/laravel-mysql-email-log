# laravel-mysql-email-log
Laravelで扱うログをMysqlに保存し、指定レベル以上の場合メールを送信する。

[![TeamCity (simple build status)](https://img.shields.io/teamcity/http/teamcity.jetbrains.com/s/bt345.svg)]()
[![composer version](https://img.shields.io/badge/version-0.0.0-blue.svg)](https://github.com/kaoken/laravel-confirmation-email)
[![licence](https://img.shields.io/badge/licence-MIT-blue.svg)](https://github.com/kaoken/laravel-confirmation-email)
[![laravel version](https://img.shields.io/badge/Laravel%20version-≧5.5-red.svg)](https://github.com/kaoken/laravel-confirmation-email)


__コンテンツの一覧__

- [インストール](#インストール)
- [設定](#設定)
- [イベント](#イベント)
- [ライセンス](#ライセンス)

## インストール

**composer**:

```bash
composer install kaoken/laravel-confirmation-email
```

または、`composer.json`へ追加

```json 
  "require": {
    ...
    "kaoken/laravel-confirmation-email":"^1.0"
  }
```

## 設定

### **`config\app.php`** に以下のように追加：

```php
    'providers' => [
        ...
        // 追加
        Kaoken\LaravelMysqlEmailLog\ConfirmationServiceProvider::class
    ],
```

### **`config\app.php`**へ追加する例

- `connection`は、データーばべーすドライバ名。`config\database.php`を参照。
- `model`は、ログモデル。
- `email`は、`true`の場合、`email_send_level`に応じて、メールを送信する。`false`の場合、一切送信しない。
- `email_send_level`は、ログレベルを指定して、指定したログレベル以上から送信する。優先順位は低いものから、`DEBUG`、`INFO`、`NOTICE`、`WARNING`、
`ERROR`、`CRITICAL`、`ALERT`、`EMERGENCY` である。大文字小文字は区別しない。
- `email_log`は、[Mailable](https://readouble.com/laravel/5.5/ja/mail)で派生したクラスを必要に応じて変更すること。
ログメールを送る。  
- `email_send_limit`は、[Mailable](https://readouble.com/laravel/5.5/ja/mail)で派生したクラスを必要に応じて変更すること。
メールの送信制限`max_email_send_count`を超えた時に送る。  
- `max_email_send_count`は、1日に送れるログメール。送信数を超えると簡単な警告メールが送られてくる。`email_send_level`参照。
- `to`は、メールの送信先。


```php
    
    
    // add
    'mysql_log' => [
        'connection' => 'mysql'
        'model' => Kaoken\LaravelMysqlEmailLog\Model\Log::class,
        'email' => true,
        'email_send_level' => 'ERROR',
        'email_log' => Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class,
        'email_send_limit' => Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class,
        'max_email_send_count' => 64,
        'to' => 'hoge@hoge.com'
    ],
```

### コマンドの実行
```bash
php artisan vendor:publish --tag=mysql_email_log
```
実行後、以下のディレクトリやファイルが追加される。   

* **`database`**
  * **`migrations`**
    * `2017_09_17_000001_create_logs_table.php`
* **`resources`**
  * **`views`**
    * **`vendor`**
      * **`mysql_email_log`**
        * `log.blade.php`
        * `over_limit.blade.php`
     
### マイグレーション
マイグレーションファイル`2017_09_17_000001_create_logs_table.php`は、必要に応じて
追加修正すること。

```bash
php artisan migrate
```

### メール
上記設定のコンフィグ`config\app.php`の場合、
`email_log`の`Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class`は、
対象レベル以上のログメールとして使用する。テンプレートは、`views\vendor\mysql_email_log\log.blade.php`
を使用している。アプリの仕様に合わせて変更すること。
  
`email_send_limit`の`Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class`は、
対象レベル以上のログが送信制限に達したときに使用する。テンプレートは、`views\vendor\mysql_email_log\over_limit.blade.php`
を使用している。アプリの仕様に合わせて変更すること。




## イベント
`vendor\laravel-mysql-email-log\src\Events`ディレクトリ内を参照!  

#### `LogMailToAdmin`
ログを書き込む前に呼び出される。  


#### `SendLimitMailToAdmin`
対象レベル以上のログが、送信制限に達したときに呼び出される。  





## ライセンス

[MIT](https://github.com/kaoken/laravel-confirmation-email/blob/master/LICENSE.txt)