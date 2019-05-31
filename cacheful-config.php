<?php

function isCachefulEnabled() {
    return defined('CACHEFUL_ENABLED');
}

function getCachefulProjectId() {
    return defined('CACHEFUL_PROJECT_ID')
        ? CACHEFUL_PROJECT_ID
        : null;
}

function getCachefulApiToken() {
    return defined('CACHEFUL_API_TOKEN')
        ? CACHEFUL_API_TOKEN
        : null;
}

function shouldSimulateSlow() {
    return defined('CACHEFUL_SIMULATE_SLOW');
}

function isConfigured()
{
    return isCachefulEnabled()
        && getCachefulProjectId()
        && getCachefulApiToken();
}
