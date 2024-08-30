<?php

namespace ProcessWire;

use DateTime;
use RockDaterangePicker\DateRange;

/**
 * @author Bernhard Baumrock, 23.07.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class FieldtypeRockDaterangePicker extends Fieldtype
{
  public function init(): void
  {
    parent::init();
    wire()->classLoader->addNamespace('RockDaterangePicker', __DIR__ . '/classes');
    if (wire()->modules->isInstalled('RockMigrations')) {
      rockmigrations()->saveCSS(
        __DIR__ . '/InputfieldRockDaterangePicker.less',
        minify: true,
      );
    }
  }

  /**
   * Format value for output
   *
   * @param Page $page
   * @param Field $field
   * @param string $value
   * @return string
   *
   */
  public function ___formatValue(Page $page, Field $field, $value)
  {
    return $value;
  }

  /**
   * Return blank value if no data is stored in the DB yet
   *
   * @param Page|NullPage $page
   * @param Field $field
   * @return RockDaterange
   *
   */
  public function getBlankValue(Page $page, Field $field)
  {
    return new DateRange();
  }

  /**
   * Return the database schema in specified format
   *
   * @param Field $field
   * @return array
   *
   */
  public function getDatabaseSchema(Field $field)
  {
    $schema = parent::getDatabaseSchema($field);

    $schema['data'] = 'timestamp NOT NULL'; // the from timestamp
    $schema['end'] = 'timestamp NOT NULL';
    $schema['hasRange'] = "int(1) NOT NULL";
    $schema['hasTime'] = "int(1) NOT NULL";
    $schema['isRecurring'] = "int(1) NOT NULL";
    $schema['every'] = "int(3)";
    $schema['everytype'] = "int(1)";
    $schema['recurend'] = "int(1)";
    $schema['recurenddate'] = "timestamp";
    $schema['recurendcount'] = "int(3)";

    // see FieldtypeComments how this works
    $schemaVersion = (int) $field->get('schemaVersion');
    $updateSchema = true;
    $table = $field->getTable();
    $database = wire()->database;

    if ($schemaVersion < 1 && $updateSchema) {
      try {
        if (!$database->columnExists($table, 'isRecurring')) {
          $database->query("ALTER TABLE `$table` ADD isRecurring " . $schema['isRecurring']);
        }
        if (!$database->columnExists($table, 'every')) {
          $database->query("ALTER TABLE `$table` ADD every " . $schema['every']);
        }
        if (!$database->columnExists($table, 'everytype')) {
          $database->query("ALTER TABLE `$table` ADD everytype " . $schema['everytype']);
        }
        if (!$database->columnExists($table, 'recurend')) {
          $database->query("ALTER TABLE `$table` ADD recurend " . $schema['recurend']);
        }
        if (!$database->columnExists($table, 'recurenddate')) {
          $database->query("ALTER TABLE `$table` ADD recurenddate " . $schema['recurenddate']);
        }
        if (!$database->columnExists($table, 'recurendcount')) {
          $database->query("ALTER TABLE `$table` ADD recurendcount " . $schema['recurendcount']);
        }
        $field->set('schemaVersion', 1);
        $field->save();
      } catch (\Throwable $th) {
        $this->error($th->getMessage());
        $updateSchema = false;
      }
    }

    return $schema;
  }

  /**
   * @param DatabaseQuerySelect $query
   * @param string $table
   * @param string $subfield
   * @param string $operator
   * @param int|string $value
   * @return DatabaseQuerySelect
   * @throws WireException if given invalid operator
   */
  public function getMatchQuery($query, $table, $subfield, $operator, $value)
  {
    $database = $this->wire('database');
    if (!$database->isOperator($operator))
      throw new WireException("Operator '{$operator}' is not implemented in {$this->className}");
    $table = $database->escapeTable($table);
    $subfield = $database->escapeCol($subfield);

    // use datetime + strtotime to sanitize $value
    $date = new DateTime();
    $date->setTimestamp(strtotime($value));

    switch ($subfield) {
      case 'start':
        $val = $date->format('Y-m-d H:i:s');
        $query->where("$table.data $operator '$val'");
        return $query;
      case 'end':
        $val = $date->format('Y-m-d H:i:s');
        $query->where("$table.end $operator '$val'");
        return $query;
      case 'year':
        $date->modify('first day of this year');
        $startDate = $date->format('Y-m-d 00:00:00');
        $date->modify('last day of this year');
        $endDate = $date->format('Y-m-d 23:59:59');
        break;
      case 'month':
        $date->modify('first day of this month');
        $startDate = $date->format('Y-m-d 00:00:00');
        $date->modify('last day of this month');
        $endDate = $date->format('Y-m-d 23:59:59');
        break;
      case 'day':
        $startDate = $date->format('Y-m-d 00:00:00');
        $endDate = $date->format('Y-m-d 23:59:59');
        break;
      case 'inRange':
        /**
         * Usage:
         * ->find('yourfield.inRange' => '2024-01-01 - 2024-01-31')
         * Or using timestamps:
         * ->find('yourfield.inRange' => '1704067200 - 1706659200')
         */
        $parts = explode(' - ', $value);
        $startTS = $parts[0];
        $endTS = $parts[1];
        // if startts and endts are not a unix timestamp use strtotime
        if (!is_numeric($startTS)) $startTS = strtotime($startTS);
        if (!is_numeric($endTS)) $endTS = strtotime($endTS);
        $startDate = new DateTime();
        $startDate->setTimestamp($startTS);
        $endDate = new DateTime();
        $endDate->setTimestamp($endTS);
        $startDate = $startDate->format('Y-m-d H:i:s');
        $endDate = $endDate->format('Y-m-d H:i:s');
    }

    $query->where("$table.data <= '$endDate' AND $table.end >= '$startDate'");
    return $query;
  }

  public function getInputfield(Page $page, Field $field)
  {
    $f = wire()->modules->get('InputfieldRockDaterangePicker');
    return $f;
  }

  /**
   * Sanitize value for storage
   *
   * @param Page $page
   * @param Field $field
   * @param string $value
   * @return string
   */
  public function sanitizeValue(Page $page, Field $field, $value)
  {
    return $value;
  }

  public function sleepValue($page, $field, $value)
  {
    bd($value, 'sleep');
    return [
      'data' => date('Y-m-d H:i:s', $value->start),
      'end' => date('Y-m-d H:i:s', $value->end),
      'hasTime' => $value->hasTime,
      'hasRange' => $value->hasRange,
      'isRecurring' => $value->isRecurring,
      'every' => $value->every,
      'everytype' => $value->everytype,
      'recurend' => $value->recurend,
      'recurenddate' => $value->recurenddate,
      'recurendcount' => $value->recurendcount,
    ];
  }

  /**
   * Get data from DB and convert it into a RockDaterange object
   * @return RockDaterange
   */
  public function wakeupValue($page, $field, $value)
  {
    $value['start'] = $value['data'];
    unset($value['data']);
    $range = new DateRange($value);
    return $range;
  }
}
