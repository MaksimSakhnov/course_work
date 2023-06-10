<?php
$link = mysqli_connect("localhost", "root", "", "students_db");

$chosen_spec = 1;

$original = false;

if ($original){
    $sql_select = "SELECT * FROM ssu_abit_spisok_datamart WHERE id_grp = $chosen_spec, original = 1";
}
else{
    $sql_select = "SELECT * FROM ssu_abit_spisok_datamart WHERE id_grp = $chosen_spec";
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
        <th>СНИЛС</th>
        <th>Сумма баллов</th>
        <th>Испытание 1</th>
        <th>Испытание 2</th>
        <th>Испытание 3</th>
        <th>Индивидуальные достижения</th>
        <th>Оригинал</th>
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
                <?php echo $student['points_sum']; ?>
            </td>
            <td>
                <?php echo $student['exam1']; ?>
            </td>
            <td>
                <?php echo $student['exam2']; ?>
            </td>
            <td>
                <?php echo $student['exam3']; ?>
            </td>
            <td>
                <?php echo $student['achievements']; ?>
            </td>
            <td>
                <?php echo $student['original']; ?>
            </td>
            <td>
                <div class="accordion_block">
                <button class="accordion"><?php echo "<div class='accordion_title'> Название направления </div>";?></button>
                <div class="panel">
                <?php

                if ($student['other_dir1']) {
                    echo "<p>" . explode(';', $student['other_dir1'])[0] . "</p>";
                }
                if ($student['other_dir2']) {
                    echo "<p>" . explode(';', $student['other_dir2'])[0] . "</p>";
                }
                if ($student['other_dir3']) {
                    echo "<p>" . explode(';', $student['other_dir3'])[0] . "</p>";
                }
                if ($student['other_dir4']) {
                    echo "<p>" . explode(';', $student['other_dir4'])[0] . "</p>";
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