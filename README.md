# youtube_scannerНа php нужно написать скрипт, который раз в час мониторит появились ли новые ролики на ютуб-каналах из списка и если появились, то выкладывает пост в Телеграм. Список каналов должен быть в файле. Список может изменяться. Взаимодействие с Телегам - через API Телеграма.

Список каналов:

https://www.youtube.com/c/%D0%94%D0%B5%D0%BD%D1%8C%D0%B3%D0%B8%D0%BD%D0%B5%D1%81%D0%BF%D1%8F%D1%82/videos
https://www.youtube.com/c/InvestFutureRu/videos
https://www.youtube.com/user/uptrader/videos
https://www.youtube.com/c/BitkoganEvgeny/videos
https://www.youtube.com/c/ThewallstreetPro/videos
https://www.youtube.com/channel/UCZUaRiKZ6BZtB_yD9f7XlMQ/videos
https://www.youtube.com/channel/UCNY3HHVKy6LdpgUcDquXgTw/videos

Постинг в канал нужно осуществлять только в промежутке с 8.00 по 22.00 МСК. 

Пост формируется как название ролика, потом пустая строчка и ссылка на ролик.

БД не используем. Информацию о том, какой последний ролик из какого ютуб-канала уже запостили, храним в файле возле скрипта. Один и тот же ролик не должен быть щапощен в канал более одного раза.
