#!/bin/sh

cat *.coffee | coffee --compile --bare --stdio > ../public/js/shifts.js

