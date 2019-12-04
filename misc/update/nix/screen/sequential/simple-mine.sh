#!/bin/sh

if [ -e "nZEDbBase.php" ]
then
	export NZEDB_ROOT="$(pwd)"
else
	export NZEDB_ROOT="$(php ../../../../../nZEDbBase.php)"
fi

#export NZEDB_UNRAR="/var/www/nZEDb/resources/tmp/unrar/"
export NZEDB_PATH="${NZEDB_ROOT}/misc/update"
export CLI_PATH="${NZEDB_ROOT}/cli/data"
export RAGE_PATH="${NZEDB_ROOT}/misc/testing/PostProcess"
export TEST_PATH="${NZEDB_ROOT}/misc/testing"
export DEV_PATH="${NZEDB_ROOT}/misc/testing/Developers"
export DB_PATH="${NZEDB_ROOT}/misc/testing/DB"
export THREADED_PATH="${NZEDB_ROOT}/misc/update/nix/multiprocessing"
export NZEDB_SLEEP_TIME="90" # in seconds
LASTOPTIMIZE=`date +%s`
LASTOPTIMIZE1=`date +%s`
command -v php >/dev/null 2>&1 && export PHP=`command -v php` || { export PHP=`command -v php`; }

##delete stale tmpunrar folders
## we need to have this use the Db setting. No idea how yet, but this fails too often otherwise.
#export count=`find $NZEDB_UNRAR -type d -print| wc -l`
#if [ $count != 1 ]
#then
#	rm -r $NZEDB_UNRAR/*
#fi

while :

 do
CURRTIME=`date +%s`

cd ${NZEDB_PATH}
$PHP ${NZEDB_PATH}/update_binaries.php

$PHP ${THREADED_PATH}/releases.php  # Set thread count to 1 in site-admin for sequential processing

$PHP ${TEST_PATH}/nzb-import.php /home/torrents/nzbdump/ true true true 15
$PHP ${TEST_PATH}/nzb-import.php /home/torrents/dump/ true true false 15

$PHP ${NZEDB_PATH}/postprocess.php all true

$PHP ${NZEDB_PATH}/decrypt_hashes.php full show
$PHP ${NZEDB_PATH}/match_prefiles.php 150 show 150
#$PHP ${TEST_PATH}/Release/fixReleaseNames2.php

#$PHP ${NZEDB_PATH}/requestid.php full
#$PHP ${DB_PATH}/populate_nzb_guid.php true

cd ${TEST_PATH}
DIFF=$(($CURRTIME-$LASTOPTIMIZE))
if [ "$DIFF" -gt 1800 ] || [ "$DIFF" -lt 1 ]
then
	LASTOPTIMIZE=`date +%s`
	echo "Cleaning DB..."
	#$PHP ${DEV_PATH}/renametopre.php 4 show
	$PHP ${TEST_PATH}/Release/fixReleaseNames.php 1 true all no
	$PHP ${TEST_PATH}/Release/fixReleaseNames.php 3 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 5 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 7 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 1 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 3 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 5 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 7 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 9 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 9 true preid no
	#$PHP ${TEST_PATH}/Release/removeCrapReleases.php true full
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 blacklist
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 blfiles
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 codec
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 executable
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 gibberish
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 hashed
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 installbin
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 passworded
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 sample
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 scr
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 short
	$PHP ${TEST_PATH}/Release/removeCrapReleases.php true 24 size
	$PHP ${NZEDB_PATH}/decrypt_hashes.php full show
	$PHP ${NZEDB_PATH}/match_prefiles.php 500 show 500
fi

cd ${NZEDB_PATH}
DIFF=$(($CURRTIME-$LASTOPTIMIZE1))
if [ "$DIFF" -gt 172800 ] || [ "$DIFF" -lt 1 ]
then
	LASTOPTIMIZE1=`date +%s`
	echo "Updating some stuff .. "
	#$PHP ${NZEDB_PATH}/optimise_db.php space
	#$PHP ${NZEDB_PATH}/update_tvschedule.php
	#$PHP ${CLI_PATH}/populate_anidb.php true full
	$PHP ${CLI_PATH}/predb_import_daily_batch2.php progress local true
	$PHP ${CLI_PATH}/predb_import_daily_batch.php progress local true
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 2 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 4 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 6 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 2 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 6 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 8 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 6 true preid no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 10 true other no
	#$PHP ${TEST_PATH}/Release/fixReleaseNames.php 10 false preid no
	#$PHP ${TEST_PATH}/Release/removeCrapReleases.php true full
	$PHP ${NZEDB_PATH}/decrypt_hashes.php full show
	$PHP ${NZEDB_PATH}/match_prefiles.php 5000 show 5000
fi

echo "waiting ${NZEDB_SLEEP_TIME} seconds..."
sleep ${NZEDB_SLEEP_TIME}

done
