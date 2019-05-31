<?php

function cacheful_simulate_slow () {
    $s = 0;
    while ($s < 2) {
        sleep(1);
        $s++;
    }
}
