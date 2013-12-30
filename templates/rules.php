<button type="button" class="btn btn-primary" onclick="setModal('newRule');")>Создать</button>
<?php
    $rules = loadRule();
    if (count($rules[0])) {
        echo('<table class="table table-hover">
            <thead>
                <tr><th>ID</th><th>Provider</th><th width="60%">Rule</th><th width="65px"></th></tr>
            </thead>
            <tbody>');

            $i = 0;
            foreach($rules as $index => $val) {
                echo('<tr>
                    <td>'.$rules[$i]['ID'].'</td>
                    <td>'.$rules[$i]['Provider'].'</td>
                    <td>От "'.$rules[$i]['From'].'" с темой "'.$rules[$i]['Subject'].'".');

                    if ($rules[$i]['Text']) echo (' Текст письма "'.$rules[$i]['Text'].'".');

                    if ($rules[$i]['TextInFilename']) echo (' Слова в имени файла "'.$rules[$i]['TextInFilename'].'".');

                    echo (' Переименовать в "'.$rules[$i]['NewFilename'].'" и сохранить в "'.$rules[$i]['Path'].'".</td>
                    <td>
                    <div class="btn-group" style="display: inline-block;">
                        <button title="Редактировать" type="button" class="btn btn-default btn-xs" onClick="editRule('.$rules[$i]['ID'].');">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>
                        <button title="Удалить" type="button" class="btn btn-default btn-xs" onClick="preDelRule('.$rules[$i]['ID'].');">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </div>
                    </td>
                </tr>');

                $i++;
            }

            echo ('</tbody>
        </table>');
    } else {
        echo('<center><h1>Cписок правил пуст.</h1></center>');
    }
?>
