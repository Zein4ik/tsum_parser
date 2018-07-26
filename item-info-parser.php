<?php

set_time_limit(0);
require_once __DIR__ . '/vendor/autoload.php';

$resource = __DIR__ . '/items.txt';
if (!file_exists($resource)) {
    exit('Файл items.txt не найден');
}


$client = new \Guzzle\Http\Client(['timeout' => 5, 'connect_timeout' => 5]);
foreach (file($resource) as $itemLink) {
    $request = $client->createRequest('GET', trim($itemLink));
    try {
        $response = $request->send();

    } catch (Exception $e) {
        echo 'П ри загрузке ' . $i . ' проблема: ' . $e->getMessage() . PHP_EOL;
        continue;
    }

    if ($response->isSuccessful()) {
        $itemInfo = [];
        $content = $response->getBody(true);

        preg_match('#<span class="item__description">(.+?)</span>#s', $content, $info);
        preg_match('#price price_type_retail.+?<span>(.+?)</span>#s', $content, $price);
        preg_match_all(
            '#class="slider-item__image js-slider-preview-image".+?src="(.+?)">#s',
            $content,
            $photos
        );

        $itemInfo['name'] = $info[1];
        $itemInfo['price'] = str_replace('&nbsp;', ' ', $price[1]);

        // Скачивание изображений
        $itemDir = __DIR__ . '/items/' . preg_replace('##', '', $itemInfo['name']);
        if (!is_dir($itemDir)) {
            mkdir($itemDir);
        }

        foreach ($photos[1] as $photo) {
            $fileInfo = pathinfo($photo);
            $ext = substr($fileInfo['extension'], 0, 3);
            $fileName = $itemDir . '/' . $fileInfo['filename'] . '.' . $ext;
            copy($fileInfo['dirname'].'/'.$fileInfo['filename'] . '.' . $ext, $fileName);
        }

        $itemInfoTxt = '';
        $itemInfoTxt .= 'Название: ' . $itemInfo['name'] . PHP_EOL;
        $itemInfoTxt .= 'Цена: ' . $itemInfo['price'];
        file_put_contents($itemDir . '/info.txt', $itemInfoTxt);
        exit();
    } else {
        echo 'Не удалось получить данные со страницы ' . $i . PHP_EOL;
    }
}





