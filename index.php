<?php
require 'vendor/autoload.php';

$api = new \Yandex\Geo\Api();

if (!empty($_GET['address'])) {
    $api->setQuery($_GET['address']);
}

// Настройка фильтров
@$api
    ->setLimit(INF) // кол-во результатов
    ->setLang(\Yandex\Geo\Api::LANG_RU) // локаль ответа
    ->load();

$response = $api->getResponse();
$response->getFoundCount(); // кол-во найденных адресов
$response->getQuery(); // исходный запрос
$response->getLatitude(); // широта для исходного запроса
$response->getLongitude(); // долгота для исходного запроса

// Список найденных точек
$collection = $response->getList();

// Создаем переменные для js
if (!empty($_GET['only_address'])) {
    $latitude = $_GET['latitude'];
    $longitude = $_GET['longitude'];
    $address = $_GET['only_address'];
    $only_address = $_GET['only_address'];
} elseif (!empty($collection)) {
    $latitude = $collection[0]->getLatitude();
    $longitude = $collection[0]->getLongitude();
    $address = $collection[0]->getAddress();
}

if (!empty($_GET['address']) && $response->getFoundCount() == 0) {
    echo "По Вашему запросу ничего не найдено";
    exit();
}

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск по координатам</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
</head>
<body>
<div class="container">
    <?php
        if (empty($only_address)) {
    ?>
            <h1>Сервис по определению широты и долготы по указанному адресу.</h1>
                <form action="">
                    <div class="form-group">
                         <input type="text" placeholder="Введите адрес, например: Новосибирск, улица Добролюбова, 12" name="address" id="lg" class="form-control">
                    </div>
                    <button type="submit" id="btn-login" class="btn btn-custom btn-lg btn-block">Найти</button>
                </form>
    <?php
        }

        if (!empty($address) && empty($only_address)) {
    ?>
        <h2>По Вашему запросу найдены следующие совпадения:</h2>
    <?php
        $i = 1;
            foreach ($collection as $item) {
    ?>
        <div class="form-group">
            <a href="index.php?latitude=<?= $item->getLatitude(); ?>&longitude=<?= $item->getLongitude(); ?>&only_address=<?= $item->getAddress(); ?>"><?= $item->getAddress(); ?></a>
            <p>Широта: <?= $item->getLatitude(); ?></p>
            <p>Долгота: <?= $item->getLongitude(); ?></p>
        </div>
    <?php
            $i++;
        }
    ?>
            <h1>Посмотреть на карте</h1>
    <?php
        }
            if (!empty($only_address)) {
    ?>
        <p>"<?= $only_address ?>" - широта: "<?= $latitude ?>", долгота "<?= $longitude ?>".</p>
    <?php
        }
        if (!empty($latitude) && !empty($longitude)) {
    ?>
        <div class="form-group" id="map" style="width: 900px; height: 600px"></div>
    <?php
        }
    ?>
</div>
<script type="text/javascript">
    ymaps.ready(init);
    var myMap,
        myPlacemark;

    function init(){
        myMap = new ymaps.Map("map", {
            center: [<?= $latitude; ?>, <?= $longitude; ?>],
            zoom: 10
        });

        myPlacemark = new ymaps.Placemark([<?= $latitude; ?>, <?= $longitude; ?>], {
            hintContent: '<?= $address; ?>',
            balloonContent: '<?= $address; ?>'
        });

        myMap.geoObjects.add(myPlacemark);
    }
</script>

</body>
</html>