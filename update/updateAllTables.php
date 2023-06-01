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
            'examCode' => $exam['subject'],
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

$sql = "SELECT * FROM directions";
$directions_result = mysqli_query($link, $sql);
$sortedDirections = array();
if ($directions_result->num_rows > 0){
    while ($dir = $directions_result->fetch_assoc()){
        $sortedDirections[$dir['specID']] = array(
            'exam1' => $dir['exam1'],
            'exam2' => $dir['exam2'],
            'examAlt' => $dir['examAlt'],
            'spo1' => $dir['spo1'],
            'spo1Alt' => $dir['spo1Alt'],
            'spo2' => $dir['spo2'],
            'spo2Alt' => $dir['spo2Alt'],
            'altIsMain' => (BOOLEAN)$dir['altExIsMain'],
        );
    }
}

foreach ($profCodes_form_file as $tmp => $chosen_spec){
    // заполнение таблицы
    $sql = "DROP TABLE IF EXISTS `$chosen_spec`";
    $drop = mysqli_query($link, $sql);
    $sql = "CREATE TABLE IF NOT EXISTS `$chosen_spec`( 
            rateNum SMALLINT NOT NULL,
            prof VARCHAR(50) NOT NULL,
            fio VARCHAR(50) NOT NULL,
            ball_summ  SMALLINT NOT NULL,
            rus SMALLINT NOT NULL,
            exam1 SMALLINT Not NULL,
            exam2 SMALLINT Not NULL,
            examAlt SMALLINT Not NULL,
            achievements SMALLINT Not NULL,
            orig BOOLEAN NOT NULL,
            priorr  SMALLINT NOT NULL,
            other_dir1 VARCHAR(50),
            other_dir2 VARCHAR(50),
            other_dir3 VARCHAR(50),
            other_dir4 VARCHAR(50)
        )";
    $create = mysqli_query($link, $sql);

    $chosenSpecList = array();

    $sql = "SELECT prof, fio, code, orig, priorr, profCode FROM students WHERE profCode = '$chosen_spec'";
    $students_result = mysqli_query($link, $sql);

    if ($students_result->num_rows > 0) {
        while ($student = $students_result->fetch_assoc()) {
            // считаем баллы
            $rus_ball = 0;
            $ex1_ball = 0;
            $ex2_ball = 0;
            $exAlt_ball = 0;
            $exAlt2_ball = 0; // на случай четырех СПО
            $individual_ball = 0;
            $spo = false;
            
            if (isset($sortedExamResults[$student['code']])){
                foreach ($sortedExamResults[$student['code']] as $inidex => $subject) {
                    $ball = $subject['rating'];
                    if ($subject['examCode'] === "001") {
                        $rus_ball = $ball;
                    } else if ($subject['examCode'] === "040") {
                        $individual_ball = $ball;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['exam1']){
                        $ex1_ball = $ball;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['exam2']){
                        $ex2_ball = $ball;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['examAlt']){
                        $exAlt_ball = $ball;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['spo1']){
                        $ex1_ball = $ball;
                        $spo = true;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['spo2']){
                        $ex2_ball = $ball;
                        $spo = true;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['spo1Alt']){
                        $exAlt_ball = $ball;
                        $spo = true;
                    } else if ($subject['examCode'] === $sortedDirections[$chosen_spec]['spo2Alt']){
                        $exAlt2_ball = $ball;
                        $spo = true;
                    }
                }
            }

            $sum = $rus_ball + $individual_ball;
            if (!$spo){
                if ($sortedDirections[$chosen_spec]['altIsMain']){
                    $sum += max($ex1_ball, $exAlt_ball) + $ex2_ball;
                }
                else {
                    $sum += max($ex2_ball, $exAlt_ball) + $ex1_ball;
                }
            }
            else {
                $sum += max($ex1_ball, $exAlt_ball) + max($ex2_ball, $exAlt2_ball);
            }

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

            $chosenSpecList[] = array(
                'prof' => $student['prof'],
                'fio' => $student['fio'],
                'ball_summ' => $sum,
                'rus' => $rus_ball,
                'exam1' => $ex1_ball,
                'exam2' => $ex2_ball,
                'examAlt' => $exAlt_ball,
                'achievements' => $individual_ball,
                'orig' => $student['orig'],
                'priorr' => $student['priorr'],
                'other_dir1' =>$other_dir1,
                'other_dir2' =>$other_dir2,
                'other_dir3' =>$other_dir3,
                'other_dir4' =>$other_dir4,
                'altIsMain' => $sortedDirections[$chosen_spec]['altIsMain'],
            );
        }

        usort($chosenSpecList, function ($a, $b){

            if ($a['ball_summ'] !== $b['ball_summ']){
                return ($a['ball_summ'] < $b['ball_summ']) ? 1 : -1;
            }

            if ($a['exam1'] !== $b['exam1']){
                return ($a['exam1'] < $b['exam1']) ? 1 : -1;
            }

            if ($a['altIsMain']){
                if ($a['examAlt'] !== $b['examAlt']){
                    return ($a['examAlt'] < $b['examAlt']) ? 1 : -1;
                }

                if ($a['exam2'] !== $b['exam2']){
                    return ($a['exam2'] < $b['exam2']) ? 1 : -1;
                }
            }
            else {
                if ($a['exam2'] !== $b['exam2']){
                    return ($a['exam2'] < $b['exam2']) ? 1 : -1;
                }

                if ($a['examAlt'] !== $b['examAlt']){
                    return ($a['examAlt'] < $b['examAlt']) ? 1 : -1;
                }
            }

            if ($a['rus'] !== $b['rus']){
                return ($a['rus'] < $b['rus']) ? 1 : -1;
            }

            if ($a['fio'] !== $b['fio']){
                $compare = strcmp($a['fio'], $b['fio']);
                if ($compare !== 0){
                    return ($compare < 0) ? 1 : -1;
                }
            }

            return 0;
        });
    }

    foreach ($chosenSpecList as $index => $student){
        $index++;
        $sql = "INSERT INTO `$chosen_spec` VALUES(
                    '{$index}',
                    '{$student['prof']}', 
                    '{$student['fio']}',
                    '{$student['ball_summ']}',
                    '{$student['rus']}',
                    '{$student['exam1']}',
                    '{$student['exam2']}',
                    '{$student['examAlt']}',
                    '{$student['achievements']}',
                    '{$student['orig']}',
                    '{$student['priorr']}',
                    '{$student['other_dir1']}',
                    '{$student['other_dir2']}',
                    '{$student['other_dir3']}',
                    '{$student['other_dir4']}'
                    ) ";
        mysqli_query($link, $sql);
    }
}