<?php

$serverName = 'localhost';
$userName = 'root';
$password = '';
$dbName = 'students_db';

$conn = new mysqli($serverName, $userName, $password,$dbName);

if($conn->connect_error){
    die('error' .$conn->connect_error);
}

$sql = 'DROP TABLE IF EXISTS students';
$conn->query($sql);

$sql = 'CREATE TABLE IF NOT EXISTS students(
    fio VARCHAR(50) NOT NULL,
    code VARCHAR(50) NOT NULL,
    spec VARCHAR(80) NOT NULL,
    form VARCHAR(50) NOT NULL,
    codeSpec VARCHAR(50) NOT NULL,
    studyForm VARCHAR(50) NOT NULL,
    payForm VARCHAR(50) NOT NULL,
    priorr  INT NOT NULL,
    orig BOOLEAN NOT NULL,
    profName VARCHAR(100) NOT NULL,
    profCode VARCHAR(50) NOT NULL,
    prof VARCHAR(80) NOT NULL,
    osnovanie VARCHAR(50) NOT NULL,
    profSubject VARCHAR(60) NOT NULL,
    department VARCHAR(80) NOT NULL,
    returnn BOOLEAN NOT NULL
)';

if($conn->query($sql) === TRUE){
    echo 'table success';
}
else{
    echo 'table error';
}

$sql = 'CREATE TABLE IF NOT EXISTS exams( 
    code VARCHAR(50) NOT NULL,
    subject  VARCHAR(50) NOT NULL,
    spec VARCHAR(50) NOT NULL
)';

if($conn->query($sql) === TRUE){
    echo 'table success';
}
else{
    echo 'table error';
}

$conn->close();

?>