# Select

**Select** class has a property named `statements` which is a simple string array
to store the columns name that you want to select.

The `Select` class has these method calls:

1. `addColumn`
2. `addColumns`
3. `addAllColumns`
4. `addColumnSum`
5. `addColumnCount`
6. `addColumnAverage`
7. `addColumnMax`
8. `addColumnMin`
9. `addColumnAsAlias`
10. `setDistinct`
11. `compile`

Also, There are some other `Capabilities` to use with the `Select` class:

1. [Where]()
2. [From]()
3. [Limit]()
4. [Join]()
5. [Group]()
6. [Order]()

### addAllColumns

This method ensures that you only want to select all columns from any table! If statements filled before calling this
method it returns `QueryBuilderException("Some Columns Already Set")`

**Returns: `Select`**

### addColumn

This method takes two inputs :

1. `column`<small>(string)</small>-> column name.
2. `escape`<small>(bool)</small>, by default it's true.

If statement array filled with star **(*)** it returns `QueryBuilderException(All Columns Selected)` exception;
and if column is empty, then it throws `QueryBuilderException(Column cant set empty)` exception.

**Returns: `Select`**

### addColumns

This method takes two inputs :

1. `column`<small>(string array)</small>-> column name.
2. `escape`<small>(bool)</small>, by default its true.

It executes the `addColumn` method with a **foreach** loop.

```php
addColumns([
    'name',
    'family'
])
```

**Returns: `Select`**


### addColumnAsAlias

Aliases are often used to make column names more readable.

This method takes four Inputs :

1. `column`<small>(string)</small> -> its a column name.
2. `alias`<small>(string)</small> -> its a temporary name.
3. `escapeKey`<small>(bool)</small>, by default it's true.
4. `escapeAlias`<small>(bool)</small>, by default it's true.

If the column name is empty, it throws `QueryBuilderException(Column cant set empty)`.

**Returns: `Select`**

### addColumnSum

This method takes four inputs :

1. `column`<small>(string)</small>
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `SUM` ability.

First ensure that `column` isn't empty, otherwise it throws `QueryBuilderException(Column cant set empty)`.

**Returns: `Select`**

### addColumnCount

This method four inputs :

1. `column`<small>(string)</small>, by default its value equal to `*`
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `COUNT` ability, it returns the number of rows that matches a specified criterion.

**Returns: `Select`**

### addColumnAverage

This method takes four inputs :

1. `column`<small>(string)</small>
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `AVERAGE` ability, it returns the average value of a numeric column.

First ensure that `column` isn't empty, otherwise it throws `QueryBuilderException(Column cant set empty)`.

**Returns: `Select`**

### addColumnMax

This method takes four inputs :

1. `column`<small>(string)</small>
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `MAX` ability, returns the largest value of the selected column.

First ensure that `column` isn't empty, otherwise it throws `QueryBuilderException(Column cant set empty)`.

**Returns: `Select`**

### addColumnMin

This method takes four inputs :

1. `column`<small>(string)</small>
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `MIN` ability, it returns the smallest value of the selected column.

First ensure that `column` isn't empty, otherwise it throws `QueryBuilderException(Column cant set empty)`.

**Returns: `Select`**

### addColumnAsAlias

This method takes four inputs :

1. `column`<small>(string)</small>
2. `alias`<small>(string)</small>, by default it's `null`.
3. `escapeKey`<small>(string)</small>, by default it's true.
4. `escapeAlias`<small>(String)</small>, by default it's true.

This method is for `SUM` ability, it returns the total sum of a numeric column.

First ensure that `column` isn't empty, otherwise it throws `QueryBuilderException(Column cant set empty)`.

This method works alike `addcollumn` method but it has `alias` ability.

**Returns: `Select`**

### setDistinct

This method takes `enable`<small>(bool)</small> as input and by default it's true.

When you call this method, it enables `distinct` ability in your Query.

**Returns: `Select`**

### compile

If `fromTable` isn't filled with value, it throws `QueryBuilderException("From is Required")`.

If `factory` is null it returns `Query` instance, otherwise it returns `EQuery` instance.





