<?php

$channelsList = file('cannels');
$api_key = 'AIzaSyD9Maa_h5AJL0RYXqtAzvhEG6au4enlERE';

//Получить имя пользователя из URL канала
function getUserNameFromUrl(string $url): string
{
    $pattern = '#https:\/\/www.youtube.com\/.*?\/(?<userName>.+?)\/videos#';
    $pregMatch = preg_match($pattern, $url, $matches);

    if ($pregMatch) {
        return $matches['userName'];
    }
}

foreach ($channelsList as $channel) {
    echo getUserNameFromUrl($channel) . '<br>';
}

//Получить ID канала по имени пользователя
function getChannelIdByUserName(string $userName, string $apiKey)
{
    $apiUrl= "https://youtube.googleapis.com/youtube/v3/channels?part=snippet%2CcontentDetails%2Cstatistics%2Cid&forUsername={$userName}&key={$apiKey}";

    return json_decode(file_get_contents($apiUrl));
}

//Получить ID плейлиста канала по ID канала
function getPlayListIdByChannelId()
{

}

//Получить данные на все видео по ID плейлиста
function getVideosDataByPlayListId()
{

}
