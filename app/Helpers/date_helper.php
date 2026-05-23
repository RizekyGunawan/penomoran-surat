<?php

/**
 * Date Helper
 *
 * Fungsi-fungsi pembantu untuk format tanggal dalam Bahasa Indonesia.
 * Digunakan di seluruh Views agar tidak ada duplikasi fungsi.
 */

if (! function_exists('format_date')) {
    /**
     * Format tanggal ke format dd-mm-yyyy
     *
     * @param string|null $date Tanggal dalam format apapun yang diterima strtotime()
     * @return string Tanggal terformat atau '-' jika kosong
     */
    function format_date(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }
        return date('d-m-Y', strtotime($date));
    }
}

if (! function_exists('format_date_with_time')) {
    /**
     * Format tanggal + waktu ke format "dd Mon yyyy, HH.mm" (Bahasa Indonesia)
     * Contoh: "10 Des 2025, 14.23"
     *
     * @param string|null $date Tanggal dalam format apapun yang diterima strtotime()
     * @return string Tanggal+waktu terformat atau '-' jika kosong
     */
    function format_date_with_time(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];

        $timestamp = strtotime($date);
        $day       = date('d', $timestamp);
        $month     = $months[date('m', $timestamp)];
        $year      = date('Y', $timestamp);
        $time      = date('H.i', $timestamp);

        return "{$day} {$month} {$year}, {$time}";
    }
}
