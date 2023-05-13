<?php

require('tosql.php');

$dep = "Механико-математический факультет";

$chosen_spec = "Прикладная математика и информатика (бакалавр)";

// подразумеваем, что ex2 и ex3 - альтернативные предметы по выбору (например, физика/информатика на мехмат)

$dep_exams = array( // наверное, это нужно грузить из бд с факультетами
    'Экз1' => "Математика",
    'Экз2' => "Физика",
    'Экз3' => "Информатика и ИКТ",
);

$sql = "SELECT * FROM students WHERE spec = '{$chosen_spec}'";


$result = mysqli_query($link, $sql);
if($result->num_rows > 0){

    echo $result->num_rows;

    echo "<table>";
    echo "<tr>
            <th>ФИО</th>
            <th>Сумма баллов</th>
            <th>Русский язык</th>
            <th>{$dep_exams['Экз1']}</th>
            <th>{$dep_exams['Экз2']}</th>
            <th>{$dep_exams['Экз3']}</th>
            <th>Инд. достиж</th>
            <th>Оригинал</th>
            <th>Приоритет</th>
            <th>Другие направления</th>
            </tr>";
    while ($student = $result->fetch_assoc()){

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
        if ($exam_result->num_rows > 0){
            while ($exam = $exam_result->fetch_assoc()){
                $ball = (int)$exam['spec'];
                switch($exam['subject']){
                    case "Русский язык":
                        $rus_ball = $ball;
                        $sum += $ball;
                        break;
                    case "Индивидуальные достижения":
                        $individual_ball = $ball;
                        $sum += $ball;
                        break;
                    case $dep_exams['Экз1']:
                        $ex1_ball = $ball;
                        $sum += $ball;
                        break;
                    case $dep_exams['Экз2']:
                        $ex2_ball = $ball;
                        $sum += $ball;
                        break;
                    case $dep_exams['Экз3']:
                        $ex3_ball = $ball;
                        $sum += $ball;
                        break;
                    default:
                        break;
                }
            }
        }

        $sum -= min($ex2_ball, $ex3_ball); // чтобы не было суммы 4 предметов

        echo "<td>".$sum, "</td>",
        "<td>".$rus_ball, "</td>",
        "<td>".$ex1_ball, "</td>",
        "<td>".$ex2_ball, "</td>",
        "<td>".$ex3_ball, "</td>",
        "<td>".$individual_ball, "</td>";

        echo "<td>".$student["orig"],"</td>",
        "<td>".$student["priorr"],"</td>";

        // специальности
        echo "<td>";
        $sql = "SELECT * FROM students WHERE code = '{$student['code']}'";
        $student_specs = mysqli_query($link, $sql);
        if ($student_specs->num_rows > 0){
            while ($student = $student_specs->fetch_assoc()){
                if ($student['spec'] === $chosen_spec) continue; // чтобы текущее не показывалось
                echo $student['department'] . " / " . $student['spec'] . "<br>";
            }
        }
        echo "</td>";

        echo "</tr>";
    }
    echo "</table>";
}