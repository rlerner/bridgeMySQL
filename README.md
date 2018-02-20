# bridgeMySQL
A workaround for the removal of legacy `mysql_*` functions in PHP7+.

## Requirements
This script routes legacy `mysql_*` functions to MySQLi functions, therefore the MySQLi extension is required.

On Windows, inside of PHP.INI:
```ini
;Remove semicolon on the following line:
;extension=php_mysqli.php
```

On Debian:
```bash
apt-get install php-mysql
```

## Word of Advise
This code has been used on many production systems without issue, however a database abstraction layer such as this is a critical piece of functionality. Please verify that the functions you require appear supported with this script and test accordingly. Obviously, this is to be used at your own risk.

## Installation
You can simply:

```php
require_once "bridgeMySQL.php";
```

Or even prepend the file inside of PHP.INI:
```ini
auto_prepend=/path/to/bridgeMySQL.php
```

## Ew
Yes, it uses globals. This is a hack made to get you through the process of modernizing your code.
