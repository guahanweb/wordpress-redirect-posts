#!/bin/bash
echo "Building..."
res=$(zip -r gw-redirect-posts.zip . -x *.git* -x README.md -x build.sh)
echo "done - be sure to commit your changes!"
exit $?
