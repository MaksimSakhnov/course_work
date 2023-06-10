<?php

require('tosql.php');

$sql = "SELECT * FROM ssu_abit_spisok_header_exams";
$dirInfoSelect = mysqli_query($link, $sql);
$dirInfo = array();
if ($dirInfoSelect->num_rows > 0){
    while ($dir = $dirInfoSelect->fetch_assoc()){
        $dirInfo[$dir['id_grp']] = array(
            'dep_title' => $dir['dep_title'],
            'dir_code' => $dir['dir_code'],
            'dir_title' => $dir['dir_title'],
            'dir_level' => $dir['dir_level'],
            'title' => $dir['title'],
            'form' => $dir['form'],
            'nabor' => $dir['nabor'],
            'podano' => $dir['podano'],
            'all_exams' => $dir['all_exams'],
        );
    }
}
unset($dirInfoSelect);

$sql = "SELECT * FROM ssu_abit_spisok_with_specs";
$studentsGrpsSelect = mysqli_query($link, $sql);
$studentsGrps = array();
if ($studentsGrpsSelect->num_rows > 0){
    while ($stGrps = $studentsGrpsSelect->fetch_assoc()){
        $studentsGrps[$stGrps['id_pers']] = array_map(function($x) {return (int)$x;}, explode(';', $stGrps['all_groups']));
    }
}
unset($studentsGrpsSelect);

$sql = "SELECT * FROM ssu_abit_spisok_with_points";
$studentsInfo = mysqli_query($link, $sql);

$sql = "DROP TABLE IF EXISTS ssu_abit_spisok_datamart";
$drop = mysqli_query($link, $sql);
$sql = "CREATE TABLE IF NOT EXISTS ssu_abit_spisok_datamart( 
        id_grp SMALLINT NOT NULL,
        dir_title VARCHAR(150) NOT NULL,
        fio VARCHAR(50) NOT NULL,
        points_sum SMALLINT NOT NULL,
        exam1 SMALLINT NOT NULL,
        exam2 SMALLINT Not NULL,
        exam3 SMALLINT Not NULL,
        achievements SMALLINT Not NULL,
        original BOOLEAN NOT NULL,
        other_dir1 VARCHAR(150),
        other_dir2 VARCHAR(150),
        other_dir3 VARCHAR(150),
        other_dir4 VARCHAR(150)
    )";
$create = mysqli_query($link, $sql);

if ($studentsInfo->num_rows > 0){
    while ($student = $studentsInfo->fetch_assoc()){

        // отдельно баллы по каждому экзу
        $exams = array_map(function($x) {return (int)$x;}, explode('.', $student['points_doted']));
        while (count($exams) < 4){
            $exams[] = 0;
        } 
        $otherDirs = array(NULL, NULL, NULL, NULL);
        $cnt = 0;
        foreach($studentsGrps[$student['id_pers']] as $index => $Grp){
            if ($Grp !== (int)$student['id_grp']){
                $otherDirs[$cnt] = $dirInfo[$Grp]['title'] . ';' . (string)$Grp;
                $cnt++;
            }
        }

        $sql = "INSERT INTO ssu_abit_spisok_datamart VALUES(
                    '{$student['id_grp']}', 
                    '{$dirInfo[$student['id_grp']]['dir_title']}',
                    '{$student['fio']}',
                    '{$student['points_sum']}',
                    '{$exams[0]}',
                    '{$exams[1]}',
                    '{$exams[2]}',
                    '{$exams[3]}',
                    '{$student['original']}',
                    '{$otherDirs[0]}',
                    '{$otherDirs[1]}',
                    '{$otherDirs[2]}',
                    '{$otherDirs[3]}'
                    ) ";
        mysqli_query($link, $sql);
        unset($exams);
        unset($otherDirs);
    }
}