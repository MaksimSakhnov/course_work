<?php

require('tosql.php');

if (file_exists('29072021.xml')) {
    $xml = simplexml_load_file('29072021.xml');
    $spisok = array();
    $examPoints = array();
    $cnt = 0;
    foreach ($xml as $index => $chel) {
        $chel = (array)$chel;
        foreach ($chel as $field => $value) {
            if ($field == 'Баллы') {
                $value = (array)$value;
                foreach ($value as $bespolezno => $sostav) {
                    if (is_array($sostav)) {
                        foreach ($sostav as $sostavIndex => $subject) {
                            $subject = (array)$subject;
                            $examPoints[$chel['Абитуриент']['ВнутреннийКод']][$subject['Предмет']] = (int)$subject['Балл'];
                        }
                    } else {
                        $sostav = (array)$sostav;
                        $examPoints[$chel['Абитуриент']['ВнутреннийКод']][$sostav['Предмет']] = (int)$sostav['Балл'];
                    }
                }
                unset($chel[$field]);
            } else if (is_object($value)) {
                $chel[$field] = (array)$value;
                foreach ($chel[$field] as $subfield => $subvalue) {
                    if (is_object($subvalue)) {
                        $chel[$field][$subfield] = (array)$subvalue;
                        foreach ($chel[$field][$subfield] as $subsubfield => $subsubvalue) {
                            if (is_object($subsubvalue)) {
                                $chel[$field][$subfield][$subsubfield] = (array)$subsubvalue;
                            }
                        }
                    }
                }
            }
        }
        $spisok[] = $chel;
        $cnt++;
        #if ($cnt == 20000) break;
    }

    $sql = "DELETE FROM students";
    mysqli_query($link, $sql);
    $sql = "DELETE FROM exams";
    mysqli_query($link, $sql);
    foreach ($spisok as $key => $chel) {
        $fio = $chel['Абитуриент']['ФИО'];
        $code = $chel['Абитуриент']['ВнутреннийКод'];
        $spec = $chel['Специальность']['Наименование'];
        $form = $chel['Специальность']['ФормаПодготовки'];
        $codeSpec = $chel['Специальность']['КодСпециальности'];
        $studyForm = $chel['ФормаОбучения'];
        $payForm = $chel['ФормаОплаты'];
        $prior = (int)$chel['ПриоритетЗачисления'];
        $orig = ($chel['ПоданОригинал'] == "true") ? true : false;
        $profName = $chel['Профиль']['Наименование'];
        $profCode = $chel['Профиль']['КодПрофиля'];
        $prof = $chel['Профиль']['ПредставлениеЭлемента'];
        $osnovanie = $chel['Профиль']['ОснованиеПоступления'];
        $profSubject = $chel['Профиль']['ПрофПредмет'];
        $department = $chel['Факультет'];
        $return = ($chel['Возврат'] == "true") ? true : false;
        $sql = "INSERT INTO students VALUES ('$fio', '$code', '$spec', '$codeSpec', '$form', '$studyForm', '$payForm', '$prior', '$orig', '$profName', '$profCode', '$prof', '$osnovanie', '$profSubject', '$department', '$return')";
        mysqli_query($link, $sql);

    }
    foreach ($examPoints as $code => $exam) {
        foreach ($exam as $subject => $rating) {
            $sql = "INSERT INTO exams VALUES ('$code', '$subject', '$rating')";
            mysqli_query($link, $sql);
        }
    }
} else {
    echo 'error';
}