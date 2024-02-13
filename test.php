<?php

$stuff = '<html><head><title>Sam</title><head><body>Ok <then class=""></then>${[global][sam]} and then he wentt here! And then there is more ${[global][stuff]} here as well.</body></html>';

preg_match_all('/\${\[([^]]+)\]\[([^]]+)\]}/', $stuff, $matches);

echo '<pre>';
var_dump($matches);
echo '</pre><br><br>';

for ($i = 0; $i < \count($matches); $i++) {
    echo $matches[2][$i] . ' ';
}

echo '<br><br>';

for ($i = 0; $i < \count($matches); $i++) {
    echo $matches[1][$i] . ' ';
}