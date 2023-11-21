# Saraf Query Builder

This project is an easy-to-use query builder and mysql connection manager for saraf projects.

> **Warning**
> Since mysql 8+ the default authentication plugin is `caching_sha2_password`
> which is currently [not supported](https://github.com/friends-of-reactphp/mysql/issues/112)
> by `friends-of-reactphp/mysql` therefore in order to use mysql database, the
> auth plugin for user should be set to `mysql_native_password` like this: `ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'P@55w@rd';`

### Development

Before anything please ensure that you have `phpcs` `php-cs-fixer` installed on your machine. Then run the following
commands to set up project for yourself.

```console
sudo npm i -g @commitlint/config-conventional
pip install pre-commit
pre-commit install
pre-commit install --hook-type commit-msg
```
