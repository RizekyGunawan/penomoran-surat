<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN' => '',
        'hostname' => '192.168.10.145',
        'username' => 'sipd',
        'password' => '',
        'database' => 'penomoran_db',   // Database utama penomoran-surat
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    /**
     * Koneksi ke database referensi (kemenkopmk_db).
     * Digunakan untuk membaca tabel users dan unit_kerja
     * yang dipakai bersama dengan aplikasi lain.
     *
     * @var array<string, mixed>
     */
    public array $kemenkopmk = [
        'DSN' => '',
        'hostname' => '192.168.10.145',
        'username' => 'sipd',
        'password' => '',
        'database' => 'kemenkopmk_db',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN' => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8',
        'DBCollat' => '',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Override defaults with values from .env when available (read full config)
        // Helper: read scalar and cast booleans/ints when needed
        $val = env('database.default.DSN', $this->default['DSN']);
        $this->default['DSN'] = $val;

        $val = env('database.default.hostname', $this->default['hostname']);
        $this->default['hostname'] = $val;

        $val = env('database.default.username', $this->default['username']);
        $this->default['username'] = $val;

        $val = env('database.default.password', $this->default['password']);
        $this->default['password'] = $val;

        $val = env('database.default.database', $this->default['database']);
        $this->default['database'] = $val;

        $val = env('database.default.DBDriver', $this->default['DBDriver']);
        $this->default['DBDriver'] = $val;

        $val = env('database.default.DBPrefix', $this->default['DBPrefix']);
        $this->default['DBPrefix'] = $val;

        $val = env('database.default.pConnect', $this->default['pConnect']);
        $this->default['pConnect'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.DBDebug', $this->default['DBDebug']);
        $this->default['DBDebug'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.charset', $this->default['charset']);
        $this->default['charset'] = $val;

        $val = env('database.default.DBCollat', $this->default['DBCollat']);
        $this->default['DBCollat'] = $val;

        $val = env('database.default.swapPre', $this->default['swapPre']);
        $this->default['swapPre'] = $val;

        $val = env('database.default.encrypt', $this->default['encrypt']);
        $this->default['encrypt'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.compress', $this->default['compress']);
        $this->default['compress'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.strictOn', $this->default['strictOn']);
        $this->default['strictOn'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.numberNative', $this->default['numberNative']);
        $this->default['numberNative'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.foundRows', $this->default['foundRows']);
        $this->default['foundRows'] = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN) : (bool) $val;

        $val = env('database.default.port', $this->default['port']);
        $this->default['port'] = is_numeric($val) ? (int) $val : $this->default['port'];

        // failover and dateFormat can be provided as JSON in env
        $val = env('database.default.failover', null);
        if (!empty($val) && is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                $this->default['failover'] = $decoded;
            }
        }

        $val = env('database.default.dateFormat', null);
        if (!empty($val) && is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                $this->default['dateFormat'] = array_merge($this->default['dateFormat'], $decoded);
            }
        }

        // kemenkopmk connection
        $val = env('database.kemenkopmk.DSN', $this->kemenkopmk['DSN']);
        $this->kemenkopmk['DSN'] = $val;

        $val = env('database.kemenkopmk.hostname', $this->kemenkopmk['hostname']);
        $this->kemenkopmk['hostname'] = $val;

        $val = env('database.kemenkopmk.username', $this->kemenkopmk['username']);
        $this->kemenkopmk['username'] = $val;

        $val = env('database.kemenkopmk.password', $this->kemenkopmk['password']);
        $this->kemenkopmk['password'] = $val;

        $val = env('database.kemenkopmk.database', $this->kemenkopmk['database']);
        $this->kemenkopmk['database'] = $val;

        $val = env('database.kemenkopmk.DBDriver', $this->kemenkopmk['DBDriver']);
        $this->kemenkopmk['DBDriver'] = $val;

        $val = env('database.kemenkopmk.port', $this->kemenkopmk['port']);
        $this->kemenkopmk['port'] = is_numeric($val) ? (int) $val : $this->kemenkopmk['port'];

        $val = env('database.kemenkopmk.failover', null);
        if (!empty($val) && is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                $this->kemenkopmk['failover'] = $decoded;
            }
        }

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
