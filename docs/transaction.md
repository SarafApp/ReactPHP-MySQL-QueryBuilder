# Transaction

Group multiple writes (and reads) into a `BEGIN ... COMMIT` block with automatic `ROLLBACK` on failure.

A MySQL transaction is connection-scoped on the server, so every statement inside it travels down the same TCP connection. The QB library reserves one connection from the write pool for the duration of the transaction, routes every statement issued inside the closure through it, and releases it on `COMMIT` or `ROLLBACK`.

### transaction(callable $work)

The user's closure receives a `Transaction` handle. Inside the closure, build queries with the same fluent API as the normal builder (`$tx->select()`, `$tx->insert()`, `$tx->update()`, `$tx->delete()`, `$tx->insertUpdate()`, `$tx->multiInsertUpdate()`, plus `$tx->raw(string $sql)` as an escape hatch).

The closure must return a `PromiseInterface` (or any value, which will be coerced to one). When that promise resolves, `COMMIT` runs and the outer promise resolves with `['result' => true, 'data' => $closureReturnValue]`. If it rejects, throws, or resolves to an array with `result => false`, `ROLLBACK` runs and the outer promise resolves with `['result' => false, 'error' => ..., 'failedAt' => ...]`.

The outer promise **always resolves, never rejects** — matching the convention of `EQuery::commit()`.

### `failedAt` values

| value      | meaning                                                                          |
|------------|----------------------------------------------------------------------------------|
| `reserve`  | No write connection available (pool at `maxWriteConnections` cap and all reserved). Closure was never invoked. |
| `begin`    | `START TRANSACTION` itself failed. No further statements were sent.              |
| `closure`  | The closure threw synchronously. `ROLLBACK` was issued.                          |
| `inner`    | The closure resolved with `result=>false` or rejected. `ROLLBACK` was issued.    |
| `commit`   | The `COMMIT` statement failed. `ROLLBACK` was issued.                            |

### Pool growth & exhaustion

`DBFactory`'s `maxWriteConnections` constructor parameter (default `1_000_000_000`) is a hard cap on the number of write connections. The pool starts at `writeInstanceCount` and grows on demand when a `transaction()` needs a connection but all existing write workers are reserved or busy. Newly grown connections stay in the pool after release, so a steady-state burst ratchets the pool to its needed size and stabilizes.

If the cap is reached and every write connection is reserved, `transaction()` resolves immediately with `failedAt => 'reserve'` — the closure is never invoked.

### Routing inside a transaction

All statements built via the `Transaction` handle — including `SELECT`s — execute on the reserved write connection, not the read pool. This is required for read-your-writes consistency inside the transaction. Statements issued outside the closure (via the normal builder) continue to use the read/write pool routing as before.

`streamRaw()` is not supported inside a transaction: an open raw stream and a subsequent `COMMIT` racing on the same connection would corrupt ordering. Use `stream()` (the buffered variant) if needed, or `raw()` for any custom SQL.

### Example

```php
$dbFactory->getQueryBuilder()->transaction(function ($tx) {
    return $tx->insert()
        ->into("Orders")
        ->setColumns(["customerId", "total"])
        ->addRow([42, 199])
        ->compile()
        ->commit()
        ->then(function ($r) use ($tx) {
            if (!$r['result']) {
                // returning result=>false (or throwing) triggers ROLLBACK
                throw new \RuntimeException("insert failed: " . $r['error']);
            }
            return $tx->update()
                ->table("Stock")
                ->setUpdates(['reserved' => 'reserved + 1'])
                ->where("productId", 42)
                ->compile()
                ->commit()
                ->then(fn() => ['orderId' => $r['insertId']]);
        });
})->then(function ($outer) {
    if ($outer['result']) {
        echo "committed; orderId=" . $outer['data']['orderId'] . PHP_EOL;
    } else {
        echo "rolled back at " . $outer['failedAt'] . ": " . $outer['error'] . PHP_EOL;
    }
});
```
