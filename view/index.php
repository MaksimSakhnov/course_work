<?php
$link = mysqli_connect("localhost", "root", "", "students_db");

$chosen_spec = '000000828';

$original = true;

if ($original){
    $sql_select = "SELECT * FROM `$chosen_spec` WHERE orig = 1"; // Выбираем таблицу из которой читать данные
}
else{
    $sql_select = "SELECT * FROM `$chosen_spec`";
}
$result = mysqli_query($link, $sql_select);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css" type="text/css">
    <script src="script.js"></script>
    <title>Списки абитуриентов</title>
</head>
<body>
<table>
    <tr>
        <th>№</th>
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
    </tr>
    <?php
    $num = 0;
    while ($student = mysqli_fetch_assoc($result)) {
        $num++;
        ?>
        <tr>
            <td>
                <?php echo $num; ?>
            </td>
            <td>
                <?php echo $student['fio']; ?>
            </td>
            <td>
                <?php echo $student['ball_summ']; ?>
            </td>
            <td>
                <?php echo $student['rus']; ?>
            </td>
            <td>
                <?php echo $student['ekz1']; ?>
            </td>
            <td>
                <?php echo $student['ekz2']; ?>
            </td>
            <td>
                <?php echo $student['ekz3']; ?>
            </td>
            <td>
                <?php echo $student['achievements']; ?>
            </td>
            <td>
                <?php echo $student['orig']; ?>
            </td>
            <td>
                <?php echo $student['priorr']; ?>
            </td>
            <td>
                <div class="accordion_block">
                <button class="accordion"><?php echo "<div class='accordion_title'> Направление / Подан оригинал / Место </div>";?></button>
                <div class="panel">
                <?php

                if ($student['other_dir1']) {
                    $sql = "SELECT prof, orig, rateNum FROM `{$student['other_dir1']}` WHERE fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['rateNum'], "</p>";
                            break;
                        }
                    }
                }
                if ($student['other_dir2']) {
                    $sql = "SELECT prof, orig, rateNum FROM `{$student['other_dir2']}` WHERE fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['rateNum'], "</p>";
                            break;
                        }
                    }
                }
                if ($student['other_dir3']) {
                    $sql = "SELECT prof, orig, rateNum FROM `{$student['other_dir3']}` where fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['rateNum'], "</p>";
                            break;
                        }
                    }
                }
                if ($student['other_dir4']) {
                    $sql = "SELECT prof, orig, rateNum FROM `{$student['other_dir4']}` where fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['rateNum'], "</p>";
                            break;
                        }
                    }
                }
                ?>
                </div>
                </div>
            </td>
        </tr>
        <?php
    }

    ?>


</table>
<script src="script.js"></script>

</body>
</html>