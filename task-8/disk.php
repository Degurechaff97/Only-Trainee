<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Яндекс.Диск");
?>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Client\Exception\UnauthorizedException;

$token = 'y0_AgAAAAAHkO5pAAx7aQAAAAER2lOTAACMUythNvpKGqx5x4tqXPB-yR07uA';

function isTxtFile($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'txt';
}

try {
    $disk = new Disk($token);

    $diskInfo = $disk->toArray();
    echo "<p>Доступно места на диске: " . round(($diskInfo['total_space'] - $diskInfo['used_space']) / 1024 / 1024, 2) . " МБ</p>";

    if (isset($_GET['view'])) {
        $filename = $_GET['view'];
        if (isTxtFile($filename)) {
            $resource = $disk->getResource($filename);
            $stream = fopen('php://temp', 'r+');
            $resource->download($stream);
            rewind($stream);
            $content = stream_get_contents($stream);
            fclose($stream);
            
            echo "<h2>Просмотр файла: " . htmlspecialchars($filename) . "</h2>";
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
        } else {
            echo "<p>Просмотр доступен только для .txt файлов.</p>";
        }
        echo "<a href='?'>Вернуться к списку файлов</a>";
    } elseif (isset($_GET['delete'])) {
        $filename = $_GET['delete'];
        $resource = $disk->getResource($filename);
        $resource->delete();
        echo "<p>Файл " . htmlspecialchars($filename) . " успешно удален.</p>";
        echo "<a href='?'>Вернуться к списку файлов</a>";
    } elseif (isset($_GET['edit'])) {
        $filename = $_GET['edit'];
        if (isTxtFile($filename)) {
            $resource = $disk->getResource($filename);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
                $resource->delete();
                $newResource = $disk->getResource($filename);
                $stream = fopen('php://temp', 'r+');
                fwrite($stream, $_POST['content']);
                rewind($stream);
                $newResource->upload($stream);
                fclose($stream);
                echo "<p>Файл успешно обновлен!</p>";
            } else {
                $stream = fopen('php://temp', 'r+');
                $resource->download($stream);
                rewind($stream);
                $content = stream_get_contents($stream);
                fclose($stream);
                
                echo "<h2>Редактирование файла: " . htmlspecialchars($filename) . "</h2>";
                echo "<form method='POST'>";
                echo "<textarea name='content' rows='20' cols='80'>" . htmlspecialchars($content) . "</textarea><br>";
                echo "<input type='submit' value='Сохранить изменения'>";
                echo "</form>";
            }
        } else {
            echo "<p>Редактирование доступно только для .txt файлов.</p>";
        }
        echo "<a href='?'>Вернуться к списку файлов</a>";
    } elseif (isset($_GET['rename'])) {
        $oldName = $_GET['rename'];
        $resource = $disk->getResource($oldName);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newName'])) {
            $newName = $_POST['newName'];
            $resource->move($newName);
            echo "<p>Файл успешно переименован из " . htmlspecialchars($oldName) . " в " . htmlspecialchars($newName) . ".</p>";
        } else {
            echo "<h2>Переименование файла: " . htmlspecialchars($oldName) . "</h2>";
            echo "<form method='POST'>";
            echo "<input type='text' name='newName' value='" . htmlspecialchars($oldName) . "'><br>";
            echo "<input type='submit' value='Переименовать'>";
            echo "</form>";
        }
        echo "<a href='?'>Вернуться к списку файлов</a>";
    } else {
        $resources = $disk->getResources()->setLimit(1000);

        echo '<h2>Файлы на Яндекс.Диске:</h2>';
        echo '<ul>';
        foreach ($resources as $resource) {
            try {
                $name = $resource->get('name');
                $type = $resource->get('type');
                echo '<li>' . htmlspecialchars($name) . ' (' . htmlspecialchars($type) . ') ';
                if ($type === 'file') {
                    if (isTxtFile($name)) {
                        echo "<a href='?edit=" . urlencode($name) . "'>Редактировать</a> | ";
                        echo "<a href='?view=" . urlencode($name) . "'>Просмотреть</a> | ";
                    }
                    echo "<a href='?rename=" . urlencode($name) . "'>Переименовать</a> | ";
                    echo "<a href='?delete=" . urlencode($name) . "' onclick='return confirm(\"Вы уверены, что хотите удалить этот файл?\");'>Удалить</a>";
                }
                echo '</li>';
            } catch (\Exception $e) {
                echo '<li>Ошибка при получении информации о файле: ' . $e->getMessage() . '</li>';
            }
        }
        echo '</ul>';

        echo '<h2>Загрузить файл:</h2>';
        echo '<form method="POST" enctype="multipart/form-data">';
        echo '<input type="file" name="file">';
        echo '<input type="submit" value="Загрузить">';
        echo '</form>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $resource = $disk->getResource($file['name']);
            $resource->upload($file['tmp_name']);
            echo '<p>Файл успешно загружен!</p>';
        }
    }
} catch (UnauthorizedException $e) {
    echo '<p>Ошибка авторизации: ' . $e->getMessage() . '</p>';
    echo '<p>Возможно, токен устарел или недействителен. Попробуйте получить новый токен.</p>';
} catch (\Exception $e) {
    echo '<p>Произошла ошибка: ' . $e->getMessage() . '</p>';
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>