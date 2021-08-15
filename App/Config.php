<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'web_app_finance_mvc';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = false;

    const SECRET_KEY = 'RaHAruH7YLdiQVmoHLuRAvosFCCGd2mp';

    const SENDGRID_API_KEY = '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';
}
