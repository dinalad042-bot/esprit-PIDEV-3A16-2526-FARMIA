<?php
$pythonVenvPath = dirname(__DIR__, 2) . '\\venv\\Scripts\\python.exe';
$pythonScriptPath = dirname(__DIR__, 2) . '\\python_api\\app.py';
$cmd = sprintf('start /B "" "%s" "%s"', $pythonVenvPath, $pythonScriptPath);
echo "Executing: $cmd\n";
pclose(popen($cmd, "r"));
echo "Done\n";
