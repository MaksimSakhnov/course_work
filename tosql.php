<?php

$link = mysqli_connect("localhost", "root", "", "students_db");

if ($link == false){
    print("Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
}
else {
    print("Соединение установлено успешно");
}

mysqli_set_charset($link, "utf8");
