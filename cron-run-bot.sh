#!/bin/bash

# Preventing duplicate cron job executions via pid file
PIDFILE=./getUpdatesCli.pid
SLEEP=3

if [ -f $PIDFILE ]
then
  PID=$(cat $PIDFILE)
  ps -p $PID > /dev/null 2>&1
  if [ $? -eq 0 ]
  then
    echo "Process already running"
    exit 1
  else
    ## Process not found assume not running
    echo $$ > $PIDFILE
    if [ $? -ne 0 ]
    then
      echo "Could not create PID file"
      exit 1
    fi
  fi
else
  echo $$ > $PIDFILE
  if [ $? -ne 0 ]
  then
    echo "Could not create PID file"
    exit 1
  fi
fi

# get telegram bot updates every $SLEEP seconds
while true 
do 
  php ./getUpdatesCLI.php
  sleep $SLEEP
done

rm $PIDFILE
