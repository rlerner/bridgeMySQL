# bridgeMySQL
Enables Traditional mysql_ functions in PHP7, on systems where MySQLi is available.

## About
With the release of PHP7, mysql_query, mysql_connect, and other mysql_* functions are removed. Several orginazations may desire to leverage PHP7 enhancmenets or patches while still supporting legacy code.

This allows you to do that.

## Caution
Not all functions are completely implemented, and those that are may have bugs. Please test this code in your environment before using in production. Usage is at your own risk.

## Installation
Please see the most current installation information here: http://www.bridgemysql.com/installation/

## Ew
Yes, it uses globals. This is a hack made to get you through the process of modernizing your code.

## Website
I have a lot more information on the website below, however please report bugs here on GitHub.
http://www.bridgemysql.com/

## Help Out
Want to write tests, improve documentation, etc? Let me know! I'm rolling out this code mostly implemented, because I find that it'll be super helpful, and most of the functions that do not operate have very low occurances on a GitHub search anyway.
