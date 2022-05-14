#!/bin/bash
which docker &> /dev/null || { echo "skrypt potrzebuje dockera do działania" ; exit 1 ; }
which npm &> /dev/null || { echo "skrypt potrzebuje polecenia npm do działania" ; exit 1 ; }

docker ps | grep km-car-rental &> /dev/null
if [ "$?" -eq 0 ]; then
	echo "Wyłączam wcześniej działające kontenery"
	docker stop km-car-rental
	docker rm -f km-car-rental	
fi

pushd client
npm i && npm run build
[ "$?" -ne 0 ] && { echo "Nie udało się zbudować części front-endowej, przerywam działanie" ; popd ; exit 1 ; }

popd

docker build -t km-car-rental-image .
[ "$?" -ne 0 ] && { echo "Nie udało się zbudować obrazu dockera, przerywam działanie" ; exit 1 ; }

docker network inspect km-car-rental-net &> /dev/null || docker network create km-car-rental-net
[ "$?" -ne 0 ] && { echo "Nie udało się utworzyć dockerowej sieci dla projektu, przerywam działanie" ; exit 1 ; }

docker run -p "8080:80" --net km-car-rental-net --name km-car-rental -d km-car-rental-image