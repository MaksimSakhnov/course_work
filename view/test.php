<?php
$link = mysqli_connect("localhost", "root", "", "students_db");
$sql_select = "SELECT * FROM `000000465` ORDER BY ball_summ DESC"; // Выбираем таблицу из которой читать данные
$result = mysqli_query($link, $sql_select);
while($student = mysqli_fetch_assoc($result)){
 echo $student['fio'];

}
