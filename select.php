<?php

require('tosql.php');

$sql = 'SELECT * FROM students WHERE department = "Механико-математический факультет"';


$result = mysqli_query($link, $sql);
if($result->num_rows > 0){

    echo $result->num_rows;

    echo "<table>";
    echo "<tr>
            <th>ФИО</th>
            <th>Сумма баллов</th>
            <th>Русский язык</th>
            <th>Экз1</th>
            <th>Экз2</th>
            <th>Экз3</th>
            <th>Инд. достиж</th>
            <th>Оригинал</th>
            <th>Приоритет</th>
            <th>Другие направления</th>
            </tr>";
    while($student = $result->fetch_assoc()){

        echo "<tr>
            <td>".$student["fio"],"</td>";
        $sql = "SELECT * FROM exams WHERE code = '{$student['code']}'";

        $sum = 0;
        $rus_ball = 0;
        $ex1_ball = 0;
        $ex2_ball = 0;
        $ex3_ball = 0;
        $individual_ball = 0;
        $exam_result = mysqli_query($link, $sql);
        if($exam_result->num_rows > 0){
            while($exam = $exam_result->fetch_assoc()){
                $sum += (int)$exam['spec'];
                if($exam['subject'] = "Русский язык"){
                    $rus_ball = intval($exam['spec']);
                }
                else if($exam['subject'] = "Индивидуальные достижения"){
                    $individual_ball = (int)$exam['spec'];
                }
                else{
                    if($ex1_ball === 0){
                        $ex1_ball = (int)$exam['spec'];
                    }
                    else if($ex2_ball === 0){
                        $ex2_ball = (int)$exam['spec'];
                    }
                    else{
                        $ex3_ball =(int) $exam['spec'];
                    }
                }
            }
        }
        echo "<td>".$sum, "</td>",
        "<td>".$rus_ball, "</td>",
        "<td>".$ex1_ball, "</td>",
        "<td>".$ex2_ball, "</td>",
        "<td>".$ex3_ball, "</td>",
        "<td>".$individual_ball, "</td>";

        echo "<td>".$student["orig"],"</td>",
        "<td>".$student["priorr"],"</td>",
        "<td> </td>";

        echo "</tr>";
    }
    echo "</table>";
}