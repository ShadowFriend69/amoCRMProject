# amoCRMProject

Этот проект включает в себя два сервиса: `backend`, написанный на PHP, и `checker`, написанный на Python. Проект использует Docker для контейнеризации сервисов.


## Требования

- Docker
- Docker Compose

## Установка

1. Клонируйте репозиторий:

```bash
git clone  https://github.com/ShadowFriend69/amoCRMProject.git
```

2. Для сборки и запуска контейнеров выполните следующую команду в терминале:
```bash
docker-compose up --build
```

## Проверка работоспособности

После запуска контейнеров, откройте браузер и перейдите по адресу:
```bash
http://localhost:8080
```

Для проверки API выполните запрос:
```bash
curl "http://localhost:8080/check?url=http://example.com"
```

## Описание сервисов

### backend
Сервис `backend` написан на PHP и использует Slim Framework для обработки HTTP-запросов.

Маршрут `/check`

- **Метод**: GET
- **Параметры**: url (URL сайта для проверки)
- **Ответ**: JSON с результатами проверки
Пример запроса:
```bash
http://localhost:8080/check?url=http://example.com
```

### checker
Сервис `checker` написан на Python и выполняет проверку сайтов.

## Логи
Для просмотра логов контейнера `backend`, выполните команду:
```bash
docker logs <backend_container_id>
```

Для получения ID контейнера используйте команду:
```bash
docker ps
```

