# laravel-mysql-email-log
Save logs handled by Laravel in Mysql, and send mail when it is over specified level.

[![Travis](https://img.shields.io/travis/rust-lang/rust.svg)]()
[![composer version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://github.com/kaoken/laravel-mysql-email-log)
[![licence](https://img.shields.io/badge/licence-MIT-blue.svg)](https://github.com/kaoken/laravel-mysql-email-log)
[![laravel version](https://img.shields.io/badge/Laravel%20version-≧5.5-red.svg)](https://github.com/kaoken/laravel-mysql-email-log)



__Table of content__

- [Install](#install)
- [Setting](#setting)
- [Event](#event)
- [License](#license)

## Install

**composer**:

```bash
composer install kaoken/laravel-mysql-email-log
```

or, add `composer.json`  

```json 
  "require": {
    ...
    "kaoken/laravel-mysql-email-log":"^1.0"
  }
```

## Setting

###  Add to **`config\app.php`** as follows:

```php
    'providers' => [
        ...
        // Add
        Kaoken\LaravelMysqlEmailLog\LaravelMysqlEmailLogServiceProvider::class
    ],
```
  
    
### Add to **`config\database.php`** as follows:

```php
    'connections' => [
        ...
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        // Add (Copy 'mysql' above)
        'mysql_log' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        ...
```
Copy the above `['connections']['mysql']` and set the driver name to `mysql_log`.
This is necessary to prevent the log from being lost due to rollback after writing 
the log when doing transaction processing (`DB :: transaction`,` DB :: beginTransaction` etc.) 
with the driver name `mysql`.

  
  

###  Add to **`config\app.php`** as follows:

- `connection` is the driver name for the data. See `config\database.php`.
- `model` is a log model.
- `email` sends a mail according to` email_send_level` if it is `true`.
In case of `false`, do not send anything.
- `email_send_level` specifies the log level and send from the specified log level or higher.
Since the priority is low, `DEBUG`、`INFO`、`NOTICE`、`WARNING`、
`ERROR`、`CRITICAL`、`ALERT`、`EMERGENCY`.Capital letters and lower case letters are not distinguished.
- `email_log` should modify the class derived from [Mailable](https://laravel.com/docs/5.5/mail) as necessary.
Send log mail. 
- `email_send_limit` should modify the class derived from [Mailable](https://laravel.com/docs/5.5/mail) as necessary.
Send when e-mail transmission limit `max_email_send_count` is exceeded. 
- `max_email_send_count`, the log e-mail that can be transmitted in one day.
 A simple warning mail is sent when the number exceeds the number of transmissions. See `email_send_level`.
- `to` is the destination of the mail.
  
It is good to add it under the `'log_level' => env('APP_LOG_LEVEL', 'debug'),` of the `config\app.php` file.


```php  
    'log_level' => env('APP_LOG_LEVEL', 'debug'),
    // Add
    'mysql_log' => [
        'connection' => 'mysql_log',
        'model' => Kaoken\LaravelMysqlEmailLog\Model\Log::class,
        'email' => true,
        'email_send_level' => 'ERROR',
        'email_log' => Kaoken\LaravelMysqlEmailLog\Mail\LogMailToAdmin::class,
        'email_send_limit' => Kaoken\LaravelMysqlEmailLog\Mail\SendLimitMailToAdmin::class,
        'max_email_send_count' => 64,
        'to' => 'hoge@hoge.com'
    ],
```

### Command
```bash
php artisan vendor:publish --tag=mysql-email-log
```
  
After execution, the following directories and files are added.  

* **`database`**
  * **`migrations`**
    * `2017_09_17_000001_create_logs_table.php`
* **`resources`**
  * **`views`**
    * **`vendor`**
      * **`mysql_email_log`**
        * `log.blade.php`
        * `over_limit.blade.php`
     
### Migration
Migration file `2017_09_17_000001_create_logs_table.php` should be modified as necessary.  

```bash
php artisan migrate
```

### E-Mail
In the case of config `config\app.php` of Setting above,

`email_log`の`Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class`は、
対象レベル以上のログメールとして使用する。
テンプレートは、`views\vendor\mysql_email_log\log.blade.php`
を使用している。アプリの仕様に合わせて変更すること。
  
`email_send_limit`の`Kaoken\LaravelMysqlEmailLog\Mail\ConfirmationMailToUser::class`は、
対象レベル以上のログが送信制限に達したときに使用する。
テンプレートは、`views\vendor\mysql_email_log\over_limit.blade.php`
を使用している。アプリの仕様に合わせて変更すること。




## Event
See inside the `vendor\laravel-mysql-email-log\src\Events` directory!

#### `BeforeWriteLogEvent`
Called before writing the log.




## License

[MIT](https://github.com/kaoken/laravel-confirmation-email/blob/master/LICENSE.txt)