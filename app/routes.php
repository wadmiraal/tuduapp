<?php

/**
 * @file
 * Route definitions.
 */

$app->post('/inbox/{service}/{securityKey}', 'Tudu\Controller\Main::inboxAction');

