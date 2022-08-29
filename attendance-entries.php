<?php

/**
 * Attendance Entries
 *
 * @package     AttendanceEntries
 * @author      Henri Susanto
 * @copyright   2022 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Attendance Entries
 * Plugin URI:  https://github.com/susantohenri
 * Description: This plugin generate code under post
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri
 * Text Domain: joss-code
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
add_shortcode('attendance_counter', function ($attributes) {
    $attributes = shortcode_atts(['id' => '', 'answer' => 'Maybe'], $attributes);
    $entries_args = ['form_id' => absint($attributes['id'])];
    $entries = json_decode(json_encode(wpforms()->entry->get_entries($entries_args)), true);
    $entries = array_map(function ($entry) {
        return json_decode($entry['fields']);
    }, $entries);
    $count = 0;
    foreach ($entries as $entry) {
        $answer = '';
        $guest = 0;
        foreach ($entry as $field) {
            if ('Attendance' === $field->name) $answer = $field->value;
            if ('Guest' === $field->name) $guest = $field->value;
        }
        if ($attributes['answer'] === $answer) $count += $guest;
    }
    return $count;
});

add_shortcode('attendance_entries', function ($atts) {
    $columns = array('Name', 'Attendance', 'Guest', 'Greeting');
    $atts = shortcode_atts(['id' => ''], $atts);
    $entries_args = ['form_id' => absint($atts['id'])];
    $entries = json_decode(json_encode(wpforms()->entry->get_entries($entries_args)), true);
    $entries = array_map(function ($entry) {
        return json_decode($entry['fields']);
    }, $entries);
    $thead = '<thead><tr><th>' . implode('</th><th>', $columns) . '</th></tr></thead>';
    $table = "
        <table id='attendance_entries'>
            {$thead}
            <tbody>
    ";
    foreach ($entries as $entry) {
        $table .= '<tr>';
        foreach ($entry as $field) if (in_array($field->name, $columns)) $table .= "<td>{$field->value}</td>";
        $table .= '</tr>';
    }
    $table .= "
            <tbody>
        </table>
    ";
    return "
        <style type='text/css'>
          #attendance_entries {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
          }
          
          #attendance_entries td, #attendance_entries th {
            border: 1px solid #ddd;
            padding: 8px;
          }
          
          #attendance_entries tr:nth-child(odd){background-color: #f7f7f7;}
          
          #attendance_entries tr:hover {background-color: #ddd;}
          
          #attendance_entries th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #a0a0a0;
            color: white;
          }
        </style>
        ${table}
    ";
});
