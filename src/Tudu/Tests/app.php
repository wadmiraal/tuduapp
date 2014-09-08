<?php

/**
 * @file
 * Boostrap the application for the web test cases.
 *
 * The web tests actually create a running application. This file configures the
 * application in a similar way to the real thing.
 */

// Setup the configuration variable.
global $conf;
$conf['db.options'] = array(
    'driver'   => 'pdo_sqlite',
    'path'     => __DIR__ . '/../../../.tmp/' . uniqid() . '.db',
);

$conf['tudu.emails.create'] = 'new@tuduapp.com';
$conf['tudu.emails.update'] = 'please-reply@tuduapp.com';
$conf['tudu.emails.names'] = array(
    'Tudu name',
);

$conf['tudu.emails.signatures.html'] = array(
    'Signature HTML',
);
$conf['tudu.emails.signatures.plain'] = array(
    'Signature plain',
);

// Create the application
$app = new Silex\Application();

// Include the routes and application definition. We don't include the
// configuration file, as we setup the configuration above.
require_once __DIR__ . '/../../../app/app.php';
require_once __DIR__ . '/../../../app/routes.php';

// Return the application, ready for booting.
return $app;
