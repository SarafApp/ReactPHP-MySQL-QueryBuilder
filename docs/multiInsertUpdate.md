# MultiInsertUpdate

**MultiInsertUpdate** class has three properties named :

- `alias`<small>(string)</small>
- `rows`<small>(array)</small>
- `updates`<small>(array)</small>

The <b>MultiInsertUpdate</b> class contains five methods such as :

- `setColumns`
- `setInsertAlias`
- `addUpdate`
- `addUpdates`
- `compile`

Also, There are some other `Capabilities` to use with the `multiInsertUpdate` class:

1. [Into]()
2. [AddRow]()


### setColumns

This method takes two inputs :

- `columns`<small>(array)</small>
- `escapeKey`<small>(bool)</small>, by default it's true.

This method first ensure that rows are set, otherwise it throws `QueryBuilderException(Instance Have Some rows so column cant change)`.

**Returns: `MultiInsertUpdate`**

### setInsertAlias

This method takes two inputs :

- `aliasName`<small>(string)</small>
- `escape`<small>(bool)</small>, by default it's true.



### addUpdate

This method takes four inputs :

- `key`<small>(string)</small>
- `value`<small>(mixed)</small>
- `escapeValue`<small>(bool)</small>, by default it's true.
- `escapeKey`<small>(bool)</small>, by default it's true.

This method checks two condition :

- ensure that `columns` are set, otherwise it throws `QueryBuilderException(Columns not set)`.
- if `rows` are not set, it throws `QueryBuilderException(Rows not set)`.

**Returns: `MultiInsertUpdate`**

### addUpdates

This method takes three inputs :

- `updates`<small>(array)</small>
- `escapeValue`<small>(bool)</small>, by default it's true.
- `escapeKey`<small>(bool)</small>, by default it's true.

It executes `addUpdate` method with a **foreach** loop.

**Returns: `MultiInsertUpdate`**

### compile

If `intoTable` didn't fill with value, it throws `QueryBuilderException("Table Required")`.

If `alias` didn't fill with value, it throws `QueryBuilderException("Alias Required")`.

If you didn't set `columns`, it throws `QueryBuilderException("Columns Required")`.

If you didn't set `row`, it throws `QueryBuilderException("Rows Required")`.

If you didn't set `updates`, it throws `QueryBuilderException("Updates Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.