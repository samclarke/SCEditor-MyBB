#!/bin/bash

rm -r zip/Upload/*

cp --parents inc/plugins/sceditor.php zip/Upload
cp --parents inc/languages/english/admin/config_sceditor.lang.php zip/Upload
cp --parents inc/languages/english/sceditor.lang.php zip/Upload
cp --parents -r jscripts/sceditor/* zip/Upload
