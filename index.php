<?php

date_default_timezone_set('Europe/Moscow');

$currentHour = date('H');

if ($currentHour <= 8 && $currentHour >= 22) {
    exit();
}

date_default_timezone_set('UTC');

//Настройки конфигурации
$apiKey = ''; //API Key для YouTube Data API v3
$chatId = -0000000; //id телеграм-чата
$telegramBotId = ''; //Токен телеграм-бота
$channels = 'channels'; //Путь к файлу с URL youtube-каналов
$posts = 'posts'; //Путь к файлу со списком залитых на телеграмм постов
$maxResults = 5; //Максимальное количество видео в выборке для каждого плейлиста канала
$period = 3600; //Период времени в секундах от текущего назад, в течении которого видео считается новым
//Конец настроек конфигурации

$channelUrlsList = file($channels);
$postsList = file($posts);
$channelIds = [];
$playListIds = [];

foreach ($channelUrlsList as $channelUrl) {
    $channelId = getChannelIdFromUrl($channelUrl);
    $playListIds = getPlayListIdsByChannelId($channelId, $apiKey);

    foreach ($playListIds as $playListId) {
        $playList = getVideosDataByPlayListId($playListId, $apiKey, $maxResults);

        foreach ($playList as $videoId => $item) {
            if ((time() - $period) <= strtotime($item['pubDate'])) {
                $recordElems = [
                    $item['pubDate'],
                    $item['channelId'],
                    $item['title'],
                    $videoId,
                ];
                $record = implode('|', $recordElems) . PHP_EOL;

                if (array_search($record, $postsList) === false) {
                    $link = "https://www.youtube.com/watch?v={$videoId}";
                    $data = array('chat_id' => $chatId, 'text' => $item['title'] . PHP_EOL . PHP_EOL . $link);

                    $sendMsg = file_get_contents('https://api.telegram.org/bot'. $telegramBotId . '/sendMessage?'. http_build_query($data));

                    if ($sendMsg) {
                        file_put_contents($posts, $record, FILE_APPEND);
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
    $apiUrl = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={$playListId}&maxResults={$maxResults}&key={$apiKey}";
    $videos = json_decode(file_get_contents($apiUrl), true);
    $videosData = [];

    foreach ($videos['items'] as $video) {
        $videosData[$video['snippet']['resourceId']['videoId']]['title'] = $video['snippet']['title'];
        $videosData[$video['snippet']['resourceId']['videoId']]['pubDate'] = $video['snippet']['publishedAt'];
        $videosData[$video['snippet']['resourceId']['videoId']]['channelId'] = $video['snippet']['channelId'];
    }

    return $videosData;
}
