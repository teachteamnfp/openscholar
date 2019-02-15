#!/usr/bin/env bash

echo "Running pre-commit hook"
composer code-standard

# $? stores exit value of the last command
if [[ $? -ne 0 ]]; then
 echo "Fix the code standard issues."
 exit 1
fi
