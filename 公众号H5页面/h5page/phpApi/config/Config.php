<?php
/**
 * 配置文件.
 * User: zyy
 * Date: 2017/11/25
 * Time: 12:21
 */

return [
  'dbConfig'=>[
      'database_type' => 'mysql',
      'database_name' => 'hunting',
      'server' => '39.108.190.88',
      'username' => 'programmer',
      'password' => 'hujiaqiLixianJian@123456',
      'charset' => 'utf8',
      'port' => 3306,
      'prefix' => '',
      'option' => [
          \PDO::ATTR_CASE => \PDO::CASE_NATURAL
      ]
  ],


];