# Insert
This class has a property named `rows`.

The <b>Insert</b> class contains two methods such as :

- `setColumns`
- `compile`

Also, There are some other `Capabilites` to use with `Insert` class:

1. [Into]()
2. [AddRow]()

### setColumns

This method takes two inputs :

1. `columns`<small>(array)</small>.
2. `escapeKey`<small>(bool)</small>, by default it's true.

This method first ensures that `rows` are set, otherwise it throws `QueryBuilderException(Instance Have Some rows so column cant change)`

**Returns: `Insert`**

### Compile

If `intoTable` didn't fill with value, it throws `QueryBuilderException("Table Required")`.

If you didn't set `columns`, it throws `QueryBuilderException("Columns Required")`.

If you didn't set `row`, it throws `QueryBuilderException("Rows Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.
