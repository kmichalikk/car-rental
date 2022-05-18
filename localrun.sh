#!/bin/bash
which docker &> /dev/null || { echo "skrypt potrzebuje dockera do działania" ; exit 1 ; }
which npm &> /dev/null || { echo "skrypt potrzebuje polecenia npm do działania" ; exit 1 ; }

function stop {
	docker ps -a | grep km-car-rental &> /dev/null
	if [ "$?" -eq 0 ]; then
		echo "$1"
		docker stop km-car-rental &> /dev/null
		docker stop km-car-rental-db &> /dev/null
		docker rm -f km-car-rental &> /dev/null
		docker rm -f km-car-rental-db &> /dev/null
	fi
}

function build_client {
	echo "=> Buduję część front-endową"
	pushd client
	npm i && npm run build
	[ "$?" -ne 0 ] && { echo "Nie udało się zbudować części front-endowej, przerywam działanie" ; popd ; exit 1 ; }
	popd
}

function prep_docker {
	echo "=> Buduję obraz dockera"
	docker build -t km-car-rental-image .
	[ "$?" -ne 0 ] && { echo "Nie udało się zbudować obrazu, przerywam działanie" ; exit 1 ; }

	echo "=> Tworzę sieć dockerową dla projektu"
	docker network inspect km-car-rental-net &> /dev/null || docker network create km-car-rental-net
	[ "$?" -ne 0 ] && { echo "Nie udało się utworzyć dockerowej sieci dla projektu, przerywam działanie" ; exit 1 ; }
}

function start_docker {
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
}

case "$1" in
"stop")
	stop "=> Wyłączam kontenery"
;;
"start")
	stop "=> Wyłączam wcześniej działające kontenery"
	build_client
	prep_docker
	start_docker
;;
"quickstart")
	stop "=> Wyłączam wcześniej działające kontenery"
	! [ "$(ls -A client/public/build)" ] && { echo "Klient nie jest zbudowany, przerywam działanie" ; exit 1 ; }
	docker images | grep "km-car-rental-image" &> /dev/null
	[ "$?" -ne 0 ] && { echo "Obraz dockera nie jest zbudowany, przerywam działanie" ; exit 1 ; }
	start_docker
;;
*)
	echo "Skrypt wymaga użycia jednego z poniższych poleceń:"
	echo
	echo "  stop          zatrzymuje i usuwa kontenery"
	echo "  start         buduje projekt i uruchamia go"
	echo "  quickstart    uruchamia ponownie zakładając, że wszystko jest już zbudowane"
;;
esac