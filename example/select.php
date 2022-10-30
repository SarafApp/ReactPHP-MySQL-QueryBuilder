<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;

include "vendor/autoload.php";

// Loop
$loop = Loop::get();

// Environments
$env = Dotenv::createImmutable(__DIR__ . "/../");
$env->load();

// Env Loader
$DB_NAME = $_ENV['DB_NAME'];
$DB_USER = $_ENV['DB_USER'];
$DB_PASS = $_ENV['DB_PASS'];
$DB_HOST = $_ENV['DB_HOST'];
$DB_PORT_READ = $_ENV['DB_PORT_READ'];
$DB_PORT_WRITE = $_ENV['DB_PORT_WRITE'];


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

//  SELECT * FROM Customers
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

//  SELECT CustomerName, City, Country FROM Customers
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addColumns(['CustomerName', 'City', 'Country'])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT CustomerName, City, Country FROM Customers" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

//  SELECT DISTINCT Country FROM Customers
$dbFactory->getQueryBuilder()
    ->select()
    ->setDistinct(true)
    ->from("Customers")
    ->addColumn("Country")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT DISTINCT Country FROM Customers" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT * FROM Customers WHERE Country = 'Germany' OR (City = 'Tehran' AND Country = 'Iran')
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addAllColumns()
    ->where("Country", "Germany")
    ->or()
    ->where("Country", "Iran")
    ->where("City", "Tehran")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Customers WHERE Country = 'Germany' OR (City = 'Tehran' AND Country = 'Iran')" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT * FROM Customers ORDER BY Country, CustomerName ASC
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addAllColumns()
    ->addOrder("Country")
    ->addOrder("CustomerName", OrderDirection::Ascending)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Customers ORDER BY Country, CustomerName ASC" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT CustomerName, ContactName, Address FROM Customers WHERE Address IS NULL
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addColumns(["CustomerName", "ContactName", "Address"])
    ->whereIsNull("Address")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT CustomerName, ContactName, Address FROM Customers WHERE Address IS NULL" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT * FROM Customers WHERE Country='Germany' LIMIT 3;
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addAllColumns()
    ->where("Country", "Germany")
    ->setLimit(3)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Customers WHERE Country='Germany' LIMIT 3" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT * FROM Customers WHERE CustomerName LIKE 'a__%';
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addAllColumns()
    ->whereLike("CustomerName", "a__", false)
    ->setLimit(3)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Customers WHERE CustomerName LIKE 'a__%'" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT * FROM Products WHERE Price BETWEEN 10 AND 20 AND CategoryID NOT IN (1,2,3);
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Products")
    ->addAllColumns()
    ->whereBetween("Price", 10, 200)
    ->whereNotIn("CategoryID", [1, 2, 3])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT * FROM Products WHERE Price BETWEEN 10 AND 20 AND CategoryID NOT IN (1,2,3)" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT CustomerName AS Customer, ContactName AS "Contact Person" FROM Customers;
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addColumnAsAlias("CustomerName", 'Customer')
    ->addColumnAsAlias("ContactName", 'Contact Person')
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT CustomerName AS Customer, ContactName AS \"Contact Person\" FROM Customers" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT Customers.CustomerName, Orders.OrderID FROM Customers LEFT JOIN Orders ON Customers.CustomerID = Orders.CustomerID ORDER BY Customers.CustomerName;
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addColumns(["Customers.CustomerName", "Orders.OrderID"])
    ->leftJoin("Orders", "Customers.CustomerID", "Orders.CustomerID")
    ->addOrder("Customers.CustomerName")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT Customers.CustomerName, Orders.OrderID FROM Customers LEFT JOIN Orders ON Customers.CustomerID = Orders.CustomerID ORDER BY Customers.CustomerName" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT COUNT(CustomerID), Country FROM Customers GROUP BY Country ORDER BY COUNT(CustomerID) DESC;
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Customers")
    ->addColumnCount("CustomerID")
    ->addColumn("Country")
    ->groupBy("Country")
    ->addOrder("COUNT(CustomerID)", OrderDirection::Descending, false)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT COUNT(CustomerID), Country FROM Customers GROUP BY Country ORDER BY COUNT(CustomerID) DESC" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// SELECT ProductName, UnitPrice * (UnitsInStock + COALESCE(UnitsOnOrder, 0)) FROM Products;
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Products")
    ->addColumn("ProductName")
    ->addColumn("UnitPrice * (UnitsInStock + COALESCE(UnitsOnOrder, 0))", false)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: SELECT ProductName, UnitPrice * (UnitsInStock + COALESCE(UnitsOnOrder, 0)) FROM Products" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

$loop->run();
