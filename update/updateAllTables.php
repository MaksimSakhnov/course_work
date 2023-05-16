<?php

require('tosql.php');

$profCodes_form_file = array();

$file = fopen('specCodes.txt','r');
while (!feof($file))
{
    $profCodes_form_file[] = trim(fgets($file));
}
fclose($file);

$sql = "SELECT * FROM exams";
$exam_result = mysqli_query($link, $sql);
$sortedExamResults = array();
if ($exam_result->num_rows > 0){
    while ($exam = $exam_result->fetch_assoc()){
        $sortedExamResults[$exam['code']][] = array(
            'name' => $exam['subject'],
            'rating' => (int)$exam['spec'],
        );
    }
}

$sql = "SELECT code, profCode from students";
$profcodes_result = mysqli_query($link, $sql);
$sortedProfCodes = array();
if ($profcodes_result->num_rows > 0){
    while ($profCode = $profcodes_result->fetch_assoc()){
        $sortedProfCodes[$profCode['code']][] = $profCode['profCode'];
    }
}

foreach ($profCodes_form_file as $tmp => $chosen_spec){
    // заполнение таблицы
    $sql = "DROP TABLE IF EXISTS `$chosen_spec`";
    $drop = mysqli_query($link, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS `$chosen_spec`( 
            prof VARCHAR(50) NOT NULL,
            fio VARCHAR(50) NOT NULL,
            ball_summ  SMALLINT NOT NULL,
            rus SMALLINT NOT NULL,
            ekz1 SMALLINT Not NULL,
            ekz2 SMALLINT Not NULL,
            ekz3 SMALLINT Not NULL,
            achievements SMALLINT Not NULL,
            orig BOOLEAN NOT NULL,
            priorr  SMALLINT NOT NULL,
            other_dir1 VARCHAR(50),
            other_dir2 VARCHAR(50),
            other_dir3 VARCHAR(50),
            other_dir4 VARCHAR(50)
        )";
    $create = mysqli_query($link, $sql);

    $sql = "SELECT prof, fio, code, orig, priorr, profCode FROM students WHERE profCode = '$chosen_spec'";
    $students_result = mysqli_query($link, $sql);

    if ($students_result->num_rows > 0) {
        while ($student = $students_result->fetch_assoc()) {
            // считаем баллы
            $rus_ball = 0;
            $ex1_ball = 0;
            $ex2_ball = 0;
            $ex3_ball = 0;
            $individual_ball = 0;
            
            if (isset($sortedExamResults[$student['code']])){
                foreach ($sortedExamResults[$student['code']] as $inidex => $subject) {
                    $ball = $subject['rating'];
                    if ($subject['name'] === "Русский язык") {
                        $rus_ball = $ball;
                    } else if ($subject['name'] === "Индивидуальные достижения") {
                        $individual_ball = $ball;
                    } else {
                        if ($ex1_ball === 0) {
                            $ex1_ball = $ball;
                        } else if ($ex2_ball === 0) {
                            $ex2_ball = $ball;
                        } else {
                            $ex3_ball = $ball;
                        }
                    }
                }
            }

            $sum = $rus_ball + $ex1_ball + $individual_ball + max($ex2_ball, $ex3_ball);

            // записываем другие направления
            $other_dir1 = null;
            $other_dir2 = null;
            $other_dir3 = null;
            $other_dir4 = null;
            
            foreach ($sortedProfCodes[$student['code']] as $index => $profCode) {
                if ($profCode !== $student['profCode']) {
                    if ($other_dir1 === null) {
                        $other_dir1 = $profCode;
                    } else if ($other_dir2 === null) {
                        $other_dir2 = $profCode;
                    }else if ($other_dir3 === null) {
                        $other_dir3 = $profCode;
                    }else if ($other_dir4 === null) {
                        $other_dir4 = $profCode;
                    }
                }
            }

            $sql = "INSERT INTO `$chosen_spec` VALUES(
                        '{$student['prof']}', 
                        '{$student['fio']}',
                        '$sum',
                        '$rus_ball',
                        '$ex1_ball',
                        '$ex2_ball',
                        '$ex3_ball',
                        '$individual_ball',
                        '{$student['orig']}',
                        '{$student['priorr']}',
                        '$other_dir1',
                        '$other_dir2',
                        '$other_dir3',
                        '$other_dir4'
                        ) ";
            mysqli_query($link, $sql);
        }
    }
}