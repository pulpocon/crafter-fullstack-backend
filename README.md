# Ticket Platform

```
$ cd docker
$ docker-compose up
$ docker-compose exec php-fpm bash
```

```
craftersvigo$ composer install
craftersvigo$ php bin/console doctrine:migrations:migrate
```

http://localhost:8086
http://localhost:8086/admin (test@pulpocon.es pulpoCon23)

## Cronjob to remove old pending tickets

```
*/5 * * * * root /usr/local/bin/php /application/bin/console app:remove-tickets-not-finished
```

