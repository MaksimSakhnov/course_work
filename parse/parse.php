<?php

require('../update/tosql.php');

$examCodes = array(
    'Творческое испытание' => "000",
    'Русский язык' => "001",
    'Химия' => "002",
    'Биология' => "003",
    'Обществознание' => "004",
    'География' => "005",
    'Математика' => "006",
    'Физика' => "007",
    'Информатика и ИКТ' => "008",
    'История' => "009",
    'Литература' => "010",
    'Профессиональное испытание' => "011",
    'Иностранный язык' => "012",
    'Английский язык' => "013",
    'Немецкий язык' => "014",
    'Индивидуальные достижения' => "040",
);

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
                            $examName = "";
                            if (strpos($subject['Предмет'], "Русский") === false && strpos($subject['Предмет'], "язык") !== false){
                                $examName = "Иностранный язык";
                            }
                            else if (strpos($subject['Предмет'], "Профессиональное испытание") !== false){
                                $examName = "Профессиональное испытание";
                            }
                            else if (strpos($subject['Предмет'], "Творческое испытание") !== false){
                                $examName = "Творческое испытание";
                            }
                            else {
                                $examName = $subject['Предмет'];
                            }
                            $examPoints[$chel['Абитуриент']['ВнутреннийКод']][$examCodes[$examName]] = (int)$subject['Балл'];
                        }
                    } else {
                        $sostav = (array)$sostav;
                        $examName = "";
                        if (strpos($sostav['Предмет'], "Русский") === false && strpos($subject['Предмет'], "язык") !== false){
                            $examName = "Иностранный язык";
                        }
                        else if (strpos($sostav['Предмет'], "Профессиональное испытание") !== false){
                            $examName = "Профессиональное испытание";
                        }
                        else if (strpos($sostav['Предмет'], "Творческое испытание") !== false){
                            $examName = "Творческое испытание";
                        }
                        else {
                            $examName = $sostav['Предмет'];
                        }
                        $examPoints[$chel['Абитуриент']['ВнутреннийКод']][$examCodes[$examName]] = (int)$sostav['Балл'];
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
        $sql = "INSERT INTO students VALUES ('$fio', '$code', '$spec', '$form', '$codeSpec', '$studyForm', '$payForm', '$prior', '$orig', '$profName', '$profCode', '$prof', '$osnovanie', '$profSubject', '$department', '$return')";
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