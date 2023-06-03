# Introduction

**This project is an easy-to-use query builder and MySQL connection manager for your projects.**

## What is this  project built on top of?

**this project built on top of react/mysql**

## Requirements

- php version >= 8.0
- evenement/evenement: ^3.0 || ^2.1 || ^1.1
- react/event-loop: ^1.2
- react/promise: ^3 || ^2.7
- react/promise-stream: ^1.4
- react/promise-timer: ^1.9
- react/socket: ^1.12

## Installation

before using `Saraf/QB` make sure you have composer installed on your machine.

download the Saraf/QB installer using Composer:

````
composer require saraf/qb
````

## Connection

you should make an instance of the **DBFactory** class. it needs some mandatory arguments such as
`loop`, `host`, `database name`,`username`and `password`
with extra optional arguments such as:

- `writePort`
- `readPort`
- `writeInstanceCount`(count of connections for writePort)
- `readConnection`(count of connections for readPort)
- `timeout`
- `idle`(The duration of the connection).

<small><b>Note</b>:writePort = 6446, readPort =6447, these values are for InnoDB cluster if you don't use the InnoDB
cluster,
make sure you set the right port on both arguments. Default `mysql` port is 3306!</small>

````
try {
    $dbFactory = new DBFactory(
        $loop,
        $DB_HOST,
        $DB_NAME,
        $DB_USER,
        $DB_PASS,
        $DB_PORT_WRITE,
        $DB_PORT_READ,
        5,
        5,
        2,
        2
    );
} catch (DBFactoryException $e) {
    echo $e->getMessage();
    exit(1);
}
````

## How Query Builder Works

Query builder helps you construct a simple or advance query. 
It returns desired class for any `sql` clauses.

## Basic Examples

### Select

The most basic example for a `SELECT` statement is selecting all (*) columns from any table.
It takes the table name in the `from()` method:

````
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addAllColumns()
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Customers" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````

### Delete

Delete a row from any table, and it needs the table name in `from()`
method and the `where` clause it needs two arguments:

1. `$key` <small>(column name)</samll>
2. `$value`

````
$dbFactory->getQueryBuilder()
    ->delete()
    ->from("Customers")
    ->where("CustomerName", "Alfreds Futterkiste")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: DELETE FROM Customers WHERE CustomerName= 'Alfreds Futterkiste'" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````

### Insert

Insert a new row in a table. Needs the table name in the `into()` method,
and columns names as an array with `setColumns()` method and their value as an array with `addRow()`
method.

````
$dbFactory->getQueryBuilder()
    ->insert()
    ->into("Customers")
    ->setColumns(["CustomerName", "ContactName", "Address"])
    ->addRow(['Cardinal', 'Tom B. Erichsen', 'Skagen 21'])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Customers (CustomerName, ContactName, Address) VALUES ('Cardinal', 'Tom B. Erichsen', 'Skagen 21')" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````

### Update

Updates an existing row from a table. it requires the table name in the `table()` method and
`setUpdate()` requires a key-value array to match columns with new values.

````
$dbFactory->getQueryBuilder()
    ->update()
    ->table("Customers")
    ->setUpdates([
        'ContactName' => 'Alfred Schmidt',
        'City' => 'Frankfurt',
    ])
    ->where("CustomerID", 1)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: UPDATE Customers SET ContactName = 'Alfred Schmidt', City = 'Frankfurt' WHERE CustomerID = 1" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````

### InsertUpdate

This method behaves exactly just like the insert method but the difference is: if the query founded any duplicate id or values it
replaces it with provided update array key values.

````
$dbFactory->getQueryBuilder()
    ->insertUpdate()
    ->into("Users")
    ->setColumns(["id", "name", "age"])
    ->setRow([10, 'Ray', 34])
    ->setUpdates([
        'name' => "Ray",
        'age' => 34
    ])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Users (id, name, age) VALUES (10, \"Roy\", 34) ON DUPLICATE KEY UPDATE name=\"Roy\", age=34" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````

### MultiInsertUpdate

This method behaves exactly just like `InsertUpdate`
but it can handle multiple rows by using `setInsertAlias()`.

````
$dbFactory->getQueryBuilder()
    ->multiInsertUpdate()
    ->into("Users")
    ->setColumns(["id", "name", "age"])
    ->addRows([
        [10, 'Ray', 34],
        [11, 'Ali', 30],
        [12, 'Mahdi', 20]
    ])
    ->setInsertAlias("alias")
    ->addUpdate("name", "alias.name", false)
    ->addUpdate("age", "alias.age", false)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Users (id, name, age) VALUES (10, \"Roy\", 34),(11, \"Ali\", 30),(12,\"Mahdi\",20) as alias ON DUPLICATE KEY UPDATE name=alias.name, age=alias.age" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
````
