<?php

date_default_timezone_set('Europe/Moscow');

$currentHour = date('H');

if ($currentHour >= 8 && $currentHour <= 22) {
    $chat_id = 99999999;
    $api_key = 11111111;

    $cannelsList = file('channels');
    $postsList = file('posts');

    foreach ($cannelsList as $cannel) {
        //Парсинг с помощью file_get_contents
        $page = file_get_contents($cannel);

        if ($page === false) {
            $err = "Ошибка: парсинга.";
            file_put_contents('logs', $err, FILE_APPEND);
            continue;
        }

        //Парсинг с помощью библиотеки cURL
        /*
        $curl = curl_init();
        $options = [
            CURLOPT_URL => str_replace(array("\r\n", "\n", "\r"), "", $cannel),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
        ];
        curl_setopt_array($curl, $options);

        $page = curl_exec($curl);

        if ($page === false) {
            $curlErr = "Ошибка cURL: " . curl_error($curl);
            file_put_contents('logs', $curlErr, FILE_APPEND);
            continue;
        }
        */

        preg_match_all(
            '#"videoId":"(?<videoId>[^"]+)","watchEndpointSupportedOnesieConfig"#su',
            $page,
            $videoId,
            PREG_PATTERN_ORDER
        );

        preg_match_all(
            '`"label":"(?<title>[^"]+\/*[^"]+)\sАвтор:\s`sU',
            $page,
            $title,
            PREG_PATTERN_ORDER
        );

        if ((empty($videoId) || empty($title)) || (count($videoId) != count($title))) {
            continue;
        }

        $records = array_combine(
            array_unique($videoId['videoId'], SORT_STRING),
            array_unique($title['title'], SORT_STRING)
        );

        /*
        echo $cannel . '<br>';
        echo 'videoId: ' . count(array_unique($videoId['videoId'], SORT_STRING)) . '<br>';
        echo 'title: ' . count(array_unique($title['title'], SORT_STRING)) . '<br>';

        foreach ($records as $videoId => $title) {
            echo '<a href="https://www.youtube.com/watch?v=' . $videoId . '">' . $title . '</a><br>';
        }
        */

        foreach ($records as $videoId => $title) {
            $title = $title;
            $link = $videoId;
            $record = $title . '|' . $link . PHP_EOL;

            if (array_search($record, $postsList) === false) {
                file_put_contents('posts', $record, FILE_APPEND);
                $data = array('chat_id' => $chat_id, 'text' => $title . PHP_EOL . $link);
                //file_get_contents('https://api.telegram.org/bot'. $api_key. '/sendMessage?'. http_build_query($data));
            }
        }
    }
}
