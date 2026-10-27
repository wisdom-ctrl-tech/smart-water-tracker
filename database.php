<?php
// ============================================================
// database.php
// The database connection and the main settings of the project.
// Every page includes this file, so the settings only live here.
// ============================================================

// ---- MySQL connection settings -------------------------------------------
// These are the standard XAMPP values. If your MySQL username or password
// is different, only change the values below (see the README, Step 4).
define('DB_SERVER', 'localhost');           // MySQL server (usually "localhost")
define('DB_USERNAME', 'root');              // XAMPP default MySQL username
define('DB_PASSWORD', '');                  // XAMPP default MySQL password is empty
define('DB_NAME', 'smart_water_tracker');   // The database created by database.sql
define('DB_CHARSET', 'utf8mb4');            // Character set used by the tables
// --------------------------------------------------------------------------

// Your local time zone. This decides which water entries count as "today".
// Examples: 'Europe/London', 'America/New_York', 'Asia/Kolkata', 'Australia/Sydney'
define('APP_TIMEZONE', 'Asia/Kolkata');

// Limits used to validate the numbers people type in.
define('MAX_ENTRY_ML', 5000);   // A single water entry may not be bigger than this
define('MIN_GOAL_ML', 500);     // Smallest daily goal a user may choose
define('MAX_GOAL_ML', 10000);   // Largest daily goal a user may choose

date_default_timezone_set(APP_TIMEZONE);

/**
 * Returns the one shared MySQL connection.
 * The connection is created the first time db() is called.
 */
function db(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        // Keep MySQL errors for ourselves instead of printing them on the page.
        mysqli_report(MYSQLI_REPORT_OFF);

        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($conn->connect_error) {
            // Show a simple message. Never show raw database errors to users.
            http_response_code(500);
            exit('Sorry, the website cannot connect to the database. '
                . 'Please make sure Apache and MySQL are running in XAMPP and that '
                . 'the settings in database.php are correct.');
        }

        $conn->set_charset(DB_CHARSET);
    }

    return $conn;
}
