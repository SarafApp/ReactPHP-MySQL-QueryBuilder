# Delete

The `Delete` class has a method named :

- `compile`

Also, There are some other `Capabilities` to use with the `Select` class:

1. [From]()
2. [Where]()
3. [Limit]()

### compile

If `fromTable` isn't filled with value, it throws `QueryBuilderException("From is Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.