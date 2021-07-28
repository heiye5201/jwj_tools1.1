<?php
/**
 * Created by PhpStorm.
 * User: hyh
 * Date: 2020/3/10
 * Time: 16:33
 */

namespace jwj_tools\tools\Api;


class DbModel
{

    public function createDb($config,$dbname)
    {
        $servername = $config['hostname'];//默认端口是3306，这里我使用MySQL，端口为3308
        $username = $config['username'];//默认用户为root
        $password = $config['password'];//默认密码为空
        $conn = mysqli_connect($servername, $username, $password);//连接至数据库服务器
        //检测连接，若连接失败，则输出错误信息并退出脚本
        if (!$conn) {
            return array('status'=>false,'msg'=>"连接错误：" . mysqli_connect_error());die;
        }
        //创建数据库
        $sql = "CREATE DATABASE $dbname";//编写sql语句
        $sql .= " CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
        if (mysqli_query($conn, $sql)) {
            return array('status'=>true,'msg'=> "数据库创建成功！");
        } else {
            return array('status'=>false,'msg'=> "创建数据库错误：" . mysqli_error($conn));//若创建不成功，则显示产生的错误信息
        }
        mysqli_close($conn);//关闭数据库连接
    }



    public function  createTable($config,$dbname,$sql){
        $servername = $config['hostname'];//默认端口是3306，这里我使用MySQL，端口为3308
        $username = $config['username'];//默认用户为root
        $password = $config['password'];//默认密码为空
        $conn = mysqli_connect($servername, $username, $password,$dbname);//连接至数据库服务器
        mysqli_query($conn,'set names utf8');
        //检测连接，若连接失败，则输出错误信息并退出脚本
        if (!$conn) {
            return array('status'=>false,'msg'=>"连接错误：" . mysqli_connect_error());die;
        }
        //创建数据表
        if (mysqli_multi_query($conn, $sql)) {
            return array('status'=>true,'msg'=> "数据表创建成功！");
        } else {
            return array('status'=>false,'msg'=> "创建数据表错误：" . mysqli_error($conn));//若创建不成功，则显示产生的错误信息
        }
        mysqli_close($conn);//关闭数据库连接
    }



}