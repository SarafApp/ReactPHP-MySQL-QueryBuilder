# Transaction

**Transaction** class has three properties:

1. `queries`
2. `queryResultCollection`
3. `connection`

The <b>Transaction</b> class contains several methods such as :

- `addQuery`
- `resolveQueries`
- `compile`

### __constructor

when we create an instance of this class it reserves a database connection using `reserveConnection` method and stores it
in `connection` property, to use it
for all transaction queries.

### addQuery

This method takes three arguments :

1. `name`<small>(string)</small>.
2. `query`<small>(Select|Update|Delete|Insert)</small>.
3. `callback`<small>(Closure)</small> by default its null.

This method push arguments as an array to **queries** property

**Returns: `Transaction`**

### resolveQueries

this method doesn't take any argument.

This method flow:

- if `queries` is empty, it commits the transaction and return a promise with result of last query.
- if `queries` is not empty, it shifts the first item of this property and stores it in a variable.
- we get the query string of the selected query and send a query to database with this query and by using the reserved
  connection.
- if the result of query returned `false` we `rollback` the transaction and return a reject promise with
  `TransactionException` exception.
- otherwise if selected query has callback, we call and check the callback to be true (we inject two argument to this
  callback, first the current query result and next the collection of all transaction query results).
- it the callback is null, or it returns true we call the current method , and it starts from first flow.

**Returns: `Promise`**

### compile

This method doesn't take any argument.

This method flow:

- if `queries` is empty, it throws `TransactionException('There are no queries inside transaction.')`.
- it starts the transaction.
- if transaction started, it calls the `resolveQueries` method, otherwise it release the reserved connection.

**Returns: `Promise`**







