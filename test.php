<?php

$str = 'Dude, what the hell is ${[global][sam]} doing? You know what! ${[global][alvin]} is insane isn\'t he???';

preg_match_all('/\${\[([^]]+)\]\[([^]]+)\]}/', $str, $matches, PREG_SET_ORDER);

$newMatch = [];

foreach ($matches as $match) {
    $newMatch[] = [$match[1], $match[2]];
}

foreach ($newMatch as $m) {
    if ($m[0] == 'global' && $m[1] == 'sam') {
        $str = str_replace('${[' . $m[0] . '][' . $m[1] . ']}', 'Sam Wilcox', $str);
    }
}

echo '<pre>';
var_dump($str);
echo '</pre>';