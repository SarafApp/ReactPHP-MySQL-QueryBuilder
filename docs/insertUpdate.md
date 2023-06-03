# InsertUpdate

**InsertUpdate** class has three properties as an array named:

1. `columns`
2. `row`
3. `updates`

The <b>InsertUpdate</b> class contains several methods such as :

- `setColumns`
- `setRow`
- `setUpdate`
- `setUpdates`
- `compile`

Also, There is another `Capability` to use with `InsertUpdate` class:

1. [Into]()

### setColumns

This method takes two inputs :

1. `columns`<small>(array)</small>.
2. `escapeKey`<small>(bool)</small>, by default it's true.

This method first checks two conditions:

- ensure that `columns` are not set, otherwise it throws `QueryBuilderException(Columns already set)`.
- unsure that `row` are set, otherwise it throws `QueryBuilderException(Instance Have Some rows so column cant change)`.

**Returns: `InsertَUpdate`**

### setRow

This method takes two inputs :

1. `row`<small>(array)</small>.
2. `$escapeValue`=true<small>(bool)</small>, by default it's true.

This method checks three conditions:

- if `columns` are not set, it throws `QueryBuilderException(Columns not set)`.
- if `row` is set, it throws `QueryBuilderException(Row Already Set)`.
- unsure that `columns` and `rows` have same count, otherwise it
  throws `QueryBuilderException(Columns and Rows Must Have same counts)`.

**Returns: `InsertَUpdate`**

### setUpdate

This method takes four inputs :

- `columns`<small>(array)</small>.
- `value`<small>(mixed)</small>.
- `escapeValue`<small>(bool)</small>, by default it's true.
- `escapeKey`<small>(bool)</small>, by default it's true.
addCol
At first checks `columns` are set, otherwise it throws `QueryBuilderException(Columns not set)`,
and ensure that `row` is set, otherwise it throws `QueryBuilderException(Row not set)`.

**Returns: `InsertَUpdate`**

### setUpdates

this method takes three inputs :

- `updates`<small>(array)</small>.
- `escapeValues`<small>(bool)</small>, by default it's true.
- `escapeKey`<small>(bool)</small>, by default it's true.

It executes `setUpdate` method with a **foreach** loop.

**Returns: `InsertَUpdate`**

### compile

If you didn't set `intoTable` value, it throws `QueryBuilderException("Table Required")`.

If you didn't set `columns`, it throws `QueryBuilderException("Columns Required")`.

If you didn't set `row`, it throws `QueryBuilderException("Rows Required")`.

If you didn't set `updates`, it throws `QueryBuilderException("Updates Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.







