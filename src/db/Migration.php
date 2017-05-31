<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
 */

namespace craft\db;

use craft\helpers\Db;
use yii\db\ColumnSchemaBuilder;

/**
 * @inheritdoc
 *
 * @property Connection $db the DB connection that this command is associated with
 * @method Connection getDb() returns the connection the DB connection that this command is associated with
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
abstract class Migration extends \yii\db\Migration
{
    // Public Methods
    // =========================================================================

    // Schema Builder Methods
    // -------------------------------------------------------------------------

    /**
     * Creates a tinytext column for MySQL, or text column for others.
     *
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function tinyText(): ColumnSchemaBuilder
    {
        if (Db::isTypeSupported('tinytext', $this->db)) {
            return $this->db->getSchema()->createColumnSchemaBuilder('tinytext');
        }

        return $this->text();
    }

    /**
     * Creates a mediumtext column for MySQL, or text column for others.
     *
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function mediumText(): ColumnSchemaBuilder
    {
        if (Db::isTypeSupported('mediumtext', $this->db)) {
            return $this->db->getSchema()->createColumnSchemaBuilder('mediumtext');
        }

        return $this->text();
    }

    /**
     * Creates a longtext column for MySQL, or text column for others.
     *
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function longText(): ColumnSchemaBuilder
    {
        if (Db::isTypeSupported('longtext', $this->db)) {
            return $this->db->getSchema()->createColumnSchemaBuilder('longtext');
        }

        return $this->text();
    }

    /**
     * Creates an enum column for MySQL and PostgreSQL, or a string column with a check constraint for others.
     *
     * @param string   $columnName The column name
     * @param string[] $values     The allowed column values
     *
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function enum(string $columnName, array $values): ColumnSchemaBuilder
    {
        if (Db::isTypeSupported('enum', $this->db)) {
            return $this->db->getSchema()->createColumnSchemaBuilder('enum', $values);
        }

        $check = "[[{$columnName}]] in (";
        foreach ($values as $i => $value) {
            if ($i != 0) {
                $check .= ',';
            }
            $check .= $this->db->quoteValue($value);
        }
        $check .= ')';

        return $this->string()->check($check);
    }

    /**
     * Shortcut for creating a uid column
     *
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function uid(): ColumnSchemaBuilder
    {
        return $this->char(36)->notNull()->defaultValue('0');
    }

    // CRUD Methods
    // -------------------------------------------------------------------------

    /**
     * Creates and executes an INSERT SQL statement.
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * @param string $table                The table that new rows will be inserted into.
     * @param array  $columns              The column data (name=>value) to be inserted into the table.
     * @param bool   $includeAuditColumns  Whether to include the data for the audit columns
     *                                     (dateCreated, dateUpdated, uid).
     */
    public function insert($table, $columns, $includeAuditColumns = true)
    {
        echo "    > insert into $table ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->insert($table, $columns, $includeAuditColumns)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    /**
     * Creates and executes an batch INSERT SQL statement.
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * @param string $table               The table that new rows will be inserted into.
     * @param array  $columns             The column names.
     * @param array  $rows                The rows to be batch inserted into the table.
     * @param bool   $includeAuditColumns Whether `dateCreated`, `dateUpdated`, and `uid` values should be added to $columns.
     */
    public function batchInsert($table, $columns, $rows, $includeAuditColumns = true)
    {
        echo "    > batch insert into $table ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->batchInsert($table, $columns, $rows, $includeAuditColumns)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    /**
     * Creates and executes a command that will insert some given data into a table, or update an existing row
     * in the event of a key constraint violation.
     *
     * @param string $table                The table that the row will be inserted into, or updated.
     * @param array  $keyColumns           The key-constrained column data (name => value) to be inserted into the table
     *                                     in the event that a new row is getting created
     * @param array  $updateColumns        The non-key-constrained column data (name => value) to be inserted into the table
     *                                     or updated in the existing row.
     * @param bool   $includeAuditColumns  Whether `dateCreated`, `dateUpdated`, and `uid` values should be added to $columns.
     */
    public function upsert(string $table, array $keyColumns, array $updateColumns, bool $includeAuditColumns = true)
    {
        echo "    > insert or update into $table ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->upsert($table, $keyColumns, $updateColumns, $includeAuditColumns)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    /**
     * Creates and executes an UPDATE SQL statement.
     * The method will properly escape the column names and bind the values to be updated.
     *
     * @param string       $table               The table to be updated.
     * @param array        $columns             The column data (name => value) to be updated.
     * @param string|array $condition           The condition that will be put in the WHERE part. Please
     *                                          refer to [[Query::where()]] on how to specify condition.
     * @param array        $params              The parameters to be bound to the command.
     * @param bool         $includeAuditColumns Whether the `dateUpdated` value should be added to $columns.
     */
    public function update($table, $columns, $condition = '', $params = [], $includeAuditColumns = true)
    {
        echo "    > update in $table ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->update($table, $columns, $condition, $params, $includeAuditColumns)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    /**
     * Creates and executes a SQL statement for replacing some text with other text in a given table column.
     *
     * @param string       $table     The table to be updated.
     * @param string       $column    The column to be searched.
     * @param string       $find      The text to be searched for.
     * @param string       $replace   The replacement text.
     * @param string|array $condition The condition that will be put in the WHERE part. Please
     *                                refer to [[Query::where()]] on how to specify condition.
     * @param array        $params    The parameters to be bound to the command.
     */
    public function replace(string $table, string $column, string $find, string $replace, $condition = '', array $params = [])
    {
        echo "    > replace \"$find\" with \"$replace\" in $table.$column ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->replace($table, $column, $find, $replace, $condition, $params)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    // Schema Manipulation Methods
    // -------------------------------------------------------------------------

    /**
     * Creates and executes a SQL statement for dropping a DB table, if it exists.
     *
     * @param string $table The table to be dropped. The name will be properly quoted by the method.
     */
    public function dropTableIfExists(string $table)
    {
        echo "    > dropping $table if it exists ...";
        $time = microtime(true);
        $this->db->createCommand()
            ->dropTableIfExists($table)
            ->execute();
        echo ' done (time: '.sprintf('%.3f', microtime(true) - $time)."s)\n";
    }

    /**
     * Builds and executes a SQL statement for creating a primary key.
     * The method will properly quote the table and column names.
     *
     * @param string|null $name the name of the primary key constraint. If null, a name will be automatically generated.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        if ($name === null) {
            $name = $this->db->getPrimaryKeyName($table, $columns);
        }

        return parent::addPrimaryKey($name, $table, $columns);
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     *
     * @param string|null $name the name of the foreign key constraint. If null, a name will be automatically generated.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas or use an array.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas or use an array.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if ($name === null) {
            $name = $this->db->getForeignKeyName($table, $columns);
        }

        return parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * Builds and executes a SQL statement for creating a new index.
     *
     * @param string|null $name the name of the index. The name will be properly quoted by the method. If null, a name will be automatically generated.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas or use an array. Each column name will be properly quoted by the method. Quoting will be skipped for column names that
     * include a left parenthesis "(".
     * @param bool $unique whether to add UNIQUE constraint on the created index.
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($name === null) {
            $name = $this->db->getIndexName($table, $columns, $unique);
        }

        return parent::createIndex($name, $table, $columns, $unique);
    }
}
