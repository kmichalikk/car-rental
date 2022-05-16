#!/bin/bash
which docker &> /dev/null || { echo "skrypt potrzebuje dockera do działania" ; exit 1 ; }
which npm &> /dev/null || { echo "skrypt potrzebuje polecenia npm do działania" ; exit 1 ; }

docker ps -a | grep km-car-rental &> /dev/null
if [ "$?" -eq 0 ]; then
	echo "=> Wyłączam wcześniej działające kontenery"
	docker stop km-car-rental &> /dev/null
	docker stop km-car-rental-db &> /dev/null
	docker rm -f km-car-rental &> /dev/null
	docker rm -f km-car-rental-db &> /dev/null
fi

if [[ "$1" != "-o" && "$1" != "--omit-client" ]]; then
echo "=> Buduję część front-endową"
pushd client
npm i && npm run build
[ "$?" -ne 0 ] && { echo "Nie udało się zbudować części front-endowej, przerywam działanie" ; popd ; exit 1 ; }
popd
fi

echo "=> Buduję obraz dockera"
docker build -t km-car-rental-image .
[ "$?" -ne 0 ] && { echo "Nie udało się zbudować obrazu, przerywam działanie" ; exit 1 ; }

echo "=> Tworzę sieć dockerową dla projektu"
docker network inspect km-car-rental-net &> /dev/null || docker network create km-car-rental-net
[ "$?" -ne 0 ] && { echo "Nie udało się utworzyć dockerowej sieci dla projektu, przerywam działanie" ; exit 1 ; }

echo "=> Uruchamiam kontener mariadb"
docker run --net km-car-rental-net \
--name km-car-rental-db \
--health-cmd="mysqladmin ping --silent" \
-e MARIADB_ROOT_PASSWORD="zaq1@WSX" \
-e MARIADB_DATABASE="car_rental" \
--mount type=bind,source="$(pwd)/database",target="/docker-entrypoint-initdb.d" \
-d mariadb:10.6
[ "$?" -ne 0 ] && { echo "Nie udało się uruchomić mariadb, przerywam działanie" ; exit 1 ; }

echo "=> Czekam na inicjalizację bazy danych"
status=$(docker inspect --format "{{.State.Health.Status}}" km-car-rental-db)
while [ "$status" != "healthy" ]; do
	sleep 2
	echo -n "."
	status=$(docker inspect --format "{{.State.Health.Status}}" km-car-rental-db)
done
echo ""

echo "=> Uruchamiam kontener z aplikacją"
docker run -p "8080:80" \
--net km-car-rental-net \
--name km-car-rental \
-d km-car-rental-image
[ "$?" -ne 0 ] && { echo "Nie udało się uruchomić aplikacji, wyłączam kontener mariadb i przerywam działanie" ; docker rm -f km-car-rental-db ; exit 1 ; }

echo "=> Czekam, aż wszystko będzie gotowe"
curl -isd "target=hello" http://localhost:8080/server/server.php | grep "200 OK" &> /dev/null
while [ "$?" -ne 0 ]; do
	sleep 2
	echo -n "."
	curl -isd "target=hello" http://localhost:8080/server/server.php | grep "200 OK" &> /dev/null
done
echo ""

echo "Aplikacja jest gotowa pod adresem http://localhost:8080"