<?php
chdir(__DIR__);
putenv('HOME=/tmp');
echo shell_exec('git config --global --add safe.directory /home/acion2/public_html/guidewaylms 2>&1');
echo "\n";
echo shell_exec('git pull 2>&1');
