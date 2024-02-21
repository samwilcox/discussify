<?php

$str = 'filter=newest&forum=all';

\parse_str($str, $out);

var_dump($str, $out['forum']);