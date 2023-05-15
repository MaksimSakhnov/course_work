<?php

require('tosql.php');
$sql = 'SELECT DISTINCT profCode FROM students order by profCode';

$result = mysqli_query($link, $sql);

if ($result->num_rows > 0) {

    while ($code = $result->fetch_assoc()) {
        // создание таблиц по кодам направлений
        $sql = "DROP TABLE IF EXISTS `{$code['profCode']}`";
        $drop = mysqli_query($link, $sql);
        $sql = "CREATE TABLE IF NOT EXISTS `{$code['profCode']}`( 
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

        // заполнение таблицы
        $sql = "SELECT prof, fio,code, orig, priorr, profCode FROM students WHERE profCode = '{$code['profCode']}'";
        $students_result = mysqli_query($link, $sql);
        if ($students_result->num_rows > 0) {
            while ($student = $students_result->fetch_assoc()) {
                // считаем баллы
                $sql = "SELECT * FROM exams WHERE code = '{$student['code']}'";
                $rus_ball = 0;
                $ex1_ball = 0;
                $ex2_ball = 0;
                $ex3_ball = 0;
                $individual_ball = 0;
                $exam_result = mysqli_query($link, $sql);
                if ($exam_result->num_rows > 0) {
                    while ($exam = $exam_result->fetch_assoc()) {
                        $ball = (int)$exam['spec'];
                        if ($exam['subject'] === "Русский язык") {
                            $rus_ball = $ball;
                        } else if ($exam['subject'] === "Индивидуальные достижения") {
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
                $sql = "SELECT DISTINCT profCode from students WHERE code = '{$student['code']}'";
                $profcodes_result = mysqli_query($link, $sql);
                if ($profcodes_result->num_rows > 0) {
                    while ($profCode = $profcodes_result->fetch_assoc()) {
                        if ($profCode['profCode'] !== $student['profCode']) {
                            if ($other_dir1 === null) {
                                $other_dir1 = $profCode['profCode'];
                            } else if ($other_dir2 === null) {
                                $other_dir2 = $profCode['profCode'];
                            }else if ($other_dir3 === null) {
                                $other_dir3 = $profCode['profCode'];
                            }else if ($other_dir4 === null) {
                                $other_dir4 = $profCode['profCode'];
                            }
                        }
                    }
                }

                $sql = "INSERT INTO `{$code['profCode']}` VALUES(
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
}