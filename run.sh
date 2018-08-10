#!/usr/bin/env bash

i=0
FIRST_RUN=1
RET=-1
echo "Quit with CTRL+C"

while [ 1 ] && [ $RET -ne 1 ]; do
    [ $FIRST_RUN -eq 1 ] || sleep 60
    system_profiler -xml SPBluetoothDataType | php read.php >> storage.csv
    RET=$?
    i=$((i+1))
    FIRST_RUN=0
    echo "Last Ran: " `date`
done

exit $RET