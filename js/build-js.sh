#!/bin/sh

cat *.coffee | coffee --compile --bare --no-header --stdio > ../public/js/shifts.js

