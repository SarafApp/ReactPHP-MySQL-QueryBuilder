# update

This class has a property named `updates`.

The **Update** class contain two method such as :

- `setUpdates`
- `compile`

Also, There are some other `Capabilities` to use with the `Update` class:

1. [Table]()
2. [Where]()


### setUpdates

This method takes two inputs :

1. `columns`<small>(array)</small>
2. `escape`<small>(bool)</small>, by default it's true

This method checks one condition with a `foreach` loop, ensure that the column is string, otherwise it throws
`QueryBuilderException(Update Required a Key-Value Format)`.

**Returns: `َUpdate`**

### compile

If `updateTable` didn't fill with value, it throws `QueryBuilderException("Table Required")`.

If you didn't set `updates`, it throws `QueryBuilderException("Updates Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.




