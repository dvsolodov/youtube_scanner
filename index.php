<?php

date_default_timezone_set('Europa/Moscow');

$currentHour = intval(date('H'));

file_put_contents('logs', 'Запуск - ' . date('Y-m-d H:i:s', time()) . PHP_EOL,
    FILE_APPEND);// Логирование запуска скрипта

if ($currentHour < 8 || $currentHour > 22) {
    exit();
}

date_default_timezone_set('UTC');

file_put_contents('logs', 'Работа - ' . date('Y-m-d H:i:s', time()) . PHP_EOL,
    FILE_APPEND);// Логирование работы скрипта


//Настройки конфигурации
$apiKey = '00000000000';               //API Key для YouTube Data API v3
$chatId = -000000000000000;            //id телеграм-чата
$telegramBotId = '000000000000000000'; //Токен телеграм-бота
$channels = 'channels';         //Путь к файлу с URL youtube-каналов
$ignoreList = 'ignoreList.txt'; //Путь к файлу с ID игнорируемых плейлистов
$posts = 'posts'; //Путь к файлу со списком залитых на телеграмм постов
$maxResults = 10; //Максимальное количество видео в выборке для каждого плейлиста канала
$period = 86400;  //Период времени в секундах от текущего назад, в течении которого видео считается новым
//Конец настроек конфигурации

$channelUrlsList = file($channels);     // Список URL youtube-каналов
$channelIds = [];                       // Массив с ID каналов
$playListIds = [];                      // Массив с ID плейлистов каналов
$ignorePlayListIds = file($ignoreList); // ID игнорируемых плейлистов

foreach ($channelUrlsList as $channelUrl) {
    $channelId = getChannelIdFromUrl($channelUrl);
    $playListIds = getPlayListIdsByChannelId($channelId, $apiKey);

    foreach ($playListIds as $playListId) {

        if (array_search($playListId . PHP_EOL, $ignorePlayListIds) === false) {

            $playList = getVideosDataByPlayListId($playListId, $apiKey, $maxResults);

            foreach ($playList as $videoId => $item) {

                if ($item['title'] == "Private video") {
                    continue;
                }

                if ((time() - $period) <= strtotime($item['pubDate'])) {
                    $recordElems = [
                        $item['pubDate'],
                        $item['channelId'],
                        $item['title'],
                        $videoId,
                    ];
                    $record = implode('|', $recordElems) . PHP_EOL;
                    $postsList = file($posts);

                    foreach ($postsList as $postRecord) {
                        $elems = explode('|', $postRecord);

                        if (array_search($videoId . PHP_EOL, $elems) === true) {
                            continue 2;
                        }
                    }

                    $link = "https://www.youtube.com/watch?v={$videoId}";
                    $data = array('chat_id' => $chatId, 'text' => $item['title'] . PHP_EOL . PHP_EOL . $link);

                    $sendMsg =
                        file_get_contents('https://api.telegram.org/bot' . $telegramBotId . '/sendMessage?' .
                            http_build_query($data));

                    if ($sendMsg) {
                        file_put_contents($posts, $record, FILE_APPEND);
                        file_put_contents('logs', 'Размещение поста в телеграм-канале - ' . $record . PHP_EOL,
                            FILE_APPEND);// Логирование публикации поста в телеграм-канале
                    }
                }
            }
        }
    }
}

//Получить ID канала из URL канала
function getChannelIdFromUrl(string $channelUrl): ?string
{
    $pattern = '#^https:\/\/www.youtube.com\/channel\/(?<channelId>.+?)\/videos$#';
    $pregMatch = preg_match($pattern, $channelUrl, $matches);

    if ($pregMatch) {
        return $matches['channelId'];
    }

    return null;
}

//Получить ID плейлистов канала по ID канала
function getPlayListIdsByChannelId(string $channelId, string $apiKey): array
{
    $apiUrl = "https://www.googleapis.com/youtube/v3/playlists?channelId={$channelId}&maxResults=100&key={$apiKey}";
    $playLists = json_decode(file_get_contents($apiUrl), true);
    $playListIds = [];

    foreach ($playLists['items'] as $playList) {
        $playListIds[] = $playList['id'];
    }

    return $playListIds;
}

//Получить данные на все видео одного плейлиста по его ID
function getVideosDataByPlayListId(string $playListId, string $apiKey, int $maxResults): array
{
    $apiUrl =
        "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={$playListId}&maxResults={$maxResults}&key={$apiKey}";
    $videos = json_decode(file_get_contents($apiUrl), true);
    $videosData = [];

    foreach ($videos['items'] as $video) {
        $videosData[$video['snippet']['resourceId']['videoId']]['title'] = $video['snippet']['title'];
        $videosData[$video['snippet']['resourceId']['videoId']]['pubDate'] = $video['snippet']['publishedAt'];
        $videosData[$video['snippet']['resourceId']['videoId']]['channelId'] = $video['snippet']['channelId'];
    }

    return $videosData;
}
