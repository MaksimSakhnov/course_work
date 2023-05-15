<?php
$link = mysqli_connect("localhost", "root", "", "students_db");
$sql_select = "SELECT * FROM `000000465` ORDER BY ball_summ DESC"; // Выбираем таблицу из которой читать данные
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
    $num = 1;
    while ($student = mysqli_fetch_assoc($result)) {

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



                <?php

                if ($student['other_dir1']) {
                    $sql = "SELECT prof, orig, priorr FROM `{$student['other_dir1']}` where fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            ?>
                <div class="accordion_block">
                <button class="accordion"><?php echo "<div class='accordion_title'>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['priorr'], "</div>";?></button>

                <div class="panel">
                    <?php

                            break;
                        }
                    }

                }
                if ($student['other_dir2']) {
                    $sql = "SELECT prof, orig, priorr FROM `{$student['other_dir2']}` WHERE fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['priorr'], "</p>";
                            break;
                        }
                    }

                }
                if ($student['other_dir3']) {
                    $sql = "SELECT prof, orig, priorr FROM `{$student['other_dir3']}` where fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['priorr'], "</p>";
                            break;
                        }
                    }

                }
                if ($student['other_dir4']) {
                    $sql = "SELECT prof, orig, priorr FROM `{$student['other_dir4']}` where fio = '{$student['fio']}'";
                    $other_directions = mysqli_query($link, $sql);
                    if ($other_directions->num_rows > 0) {
                        while ($dir = mysqli_fetch_assoc($other_directions)) {
                            echo "<p>" .$dir['prof'], " / " . $dir['orig'], " / " . $dir['priorr'], "</p>";
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
        $num++;
    }

    ?>


</table>
<script src="script.js"></script>

</body>
</html>